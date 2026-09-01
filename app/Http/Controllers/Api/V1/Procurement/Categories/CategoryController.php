<?php

namespace App\Http\Controllers\Api\V1\Procurement\Categories;

use App\DataTransferObjects\Procurement\SubcategoryMoveResult;
use App\Http\Controllers\Controller;
use App\Http\Requests\Procurement\Categories\ReassignCategoryVendorLinkRequest;
use App\Http\Requests\Procurement\Categories\StoreCategoryRequest;
use App\Http\Requests\Procurement\Categories\UpdateCategoryRequest;
use App\Http\Resources\Api\V1\Procurement\Categories\CategoryResource;
use App\Http\Resources\Api\V1\Procurement\Categories\VendorCategoryLinkResource;
use App\Models\Procurement\Vendors\Category;
use App\Models\Procurement\Vendors\Subcategory;
use App\Models\Procurement\Vendors\VendorCategory;
use App\Services\Procurement\Categories\CategoryCatalogService;
use App\Services\Procurement\Categories\CategoryVendorLinkService;
use App\Services\Procurement\Categories\SubcategoryMoveService;
use App\Support\TableSort;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;

class CategoryController extends Controller
{
    public function __construct(
        protected CategoryCatalogService $catalogService,
        protected SubcategoryMoveService $moveService,
        protected CategoryVendorLinkService $vendorLinkService,
    ) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $perPage = max(1, min(100, (int) $request->query('per_page', 15)));
        $sort = TableSort::resolve($request, ['name_ar', 'name_en', 'created_at'], 'name_ar', 'asc');

        $query = Category::query()
            ->withCount('subcategories')
            ->withCount(['vendors as vendors_count' => function ($q) {
                $q->select(DB::raw('count(distinct vendors.id)'));
            }]);

        if ($request->filled('q')) {
            $term = '%'.$request->string('q').'%';
            $query->where(function ($q) use ($term) {
                $q->where('name_ar', 'like', $term)
                    ->orWhere('name_en', 'like', $term)
                    ->orWhere('slug', 'like', $term);
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }

        $query->orderBy($sort['column'], $sort['direction'])
            ->orderBy('id');

        return CategoryResource::collection($query->paginate($perPage)->withQueryString())->additional([
            'statuses' => ['active', 'inactive'],
        ]);
    }

    public function store(StoreCategoryRequest $request): JsonResponse
    {
        $data = $request->validated();

        $category = DB::transaction(function () use ($data) {
            $category = Category::query()->create([
                'name_en' => $data['name_en'],
                'name_ar' => $data['name_ar'],
                'slug' => $data['slug'],
                'status' => $data['status'],
            ]);

            $this->catalogService->syncSubcategories($category, $data['subcategories'] ?? []);

            return $category;
        });

        $this->loadShowRelations($category);

        return (new CategoryResource($category))
            ->additional(['message' => 'Category created successfully.'])
            ->response()
            ->setStatusCode(201);
    }

    public function show(Category $category): CategoryResource
    {
        $this->loadShowRelations($category);

        return (new CategoryResource($category))->additional([
            'all_categories' => $this->allCategoriesForForm(),
        ]);
    }

    public function update(UpdateCategoryRequest $request, Category $category): JsonResponse
    {
        $data = $request->validated();
        [$moveRows, $stayRows] = $this->partitionSubcategoryRows($data['subcategories'] ?? [], $category->id);

        /** @var list<SubcategoryMoveResult> $moveResults */
        $moveResults = [];

        DB::transaction(function () use ($data, $category, $moveRows, $stayRows, &$moveResults) {
            $category->update([
                'name_en' => $data['name_en'],
                'name_ar' => $data['name_ar'],
                'slug' => $data['slug'],
                'status' => $data['status'],
            ]);

            foreach ($moveRows as $row) {
                $subcategory = Subcategory::query()
                    ->whereKey((int) $row['id'])
                    ->where('category_id', $category->id)
                    ->firstOrFail();

                $subcategory->update([
                    'name_en' => $row['name_en'],
                    'name_ar' => $row['name_ar'],
                    'slug' => $row['slug'],
                    'status' => $row['status'],
                ]);

                $target = Category::query()->findOrFail((int) $row['target_category_id']);
                $moveResults[] = $this->moveService->move($subcategory->fresh(), $target);
            }

            $this->catalogService->syncSubcategories($category, $stayRows);
        });

        $message = 'Category updated successfully.';
        if ($moveResults !== []) {
            $message = 'Category updated. '.implode(' ', array_map(
                static fn (SubcategoryMoveResult $result) => $result->summaryLine(),
                $moveResults
            ));
        }

        $category->refresh();
        $this->loadShowRelations($category);

        return (new CategoryResource($category))
            ->additional([
                'message' => $message,
                'all_categories' => $this->allCategoriesForForm(),
            ])
            ->response();
    }

    public function destroy(Category $category): JsonResponse
    {
        $detachedVendorLinks = $this->catalogService->softDeleteCategoryCascade($category);

        $message = 'Category deleted successfully.';
        if ($detachedVendorLinks > 0) {
            $message .= ' '.$detachedVendorLinks.' vendor classification link(s) were removed; vendors were not deleted.';
        }

        return response()->json(['message' => $message]);
    }

    public function movePreview(Request $request, Subcategory $subcategory): JsonResponse
    {
        $validated = $request->validate([
            'target_category_id' => ['required', 'integer', 'exists:categories,id'],
        ]);

        $target = Category::query()->findOrFail((int) $validated['target_category_id']);
        $impact = $this->moveService->preview($subcategory, $target);

        return response()->json([
            'vendor_links' => $impact->vendorLinks,
            'brochures' => $impact->brochures,
            'procurement_requests' => $impact->procurementRequests,
            'has_name_conflict' => $impact->hasNameConflict,
            'has_slug_conflict' => $impact->hasSlugConflict,
        ]);
    }

    public function categoryVendorLinks(Category $category): JsonResponse
    {
        return $this->vendorLinksResponse($category, null);
    }

    public function subcategoryVendorLinks(Category $category, Subcategory $subcategory): JsonResponse
    {
        if ((int) $subcategory->category_id !== (int) $category->id) {
            abort(404);
        }

        return $this->vendorLinksResponse($category, $subcategory);
    }

    public function reassignVendorLink(
        ReassignCategoryVendorLinkRequest $request,
        VendorCategory $vendorCategory,
    ): JsonResponse {
        $validated = $request->validated();
        $updateBrochures = filter_var($validated['update_brochures'] ?? false, FILTER_VALIDATE_BOOLEAN);
        $targetSubcategoryId = isset($validated['target_subcategory_id']) && $validated['target_subcategory_id'] !== null
            ? (int) $validated['target_subcategory_id']
            : null;

        $result = $this->vendorLinkService->reassign(
            $vendorCategory,
            (int) $validated['target_category_id'],
            $targetSubcategoryId,
            $updateBrochures,
        );

        return response()->json(['message' => $this->vendorLinkResultMessage($result)]);
    }

    public function removeVendorLink(Request $request, VendorCategory $vendorCategory): JsonResponse
    {
        $updateBrochures = filter_var($request->input('update_brochures'), FILTER_VALIDATE_BOOLEAN);
        $result = $this->vendorLinkService->removeLink($vendorCategory, $updateBrochures);

        return response()->json(['message' => $this->vendorLinkResultMessage($result)]);
    }

    /**
     * @param  array{brochures_updated: int, merged: bool, removed: bool}  $result
     */
    private function vendorLinkResultMessage(array $result): string
    {
        if ($result['merged']) {
            $message = 'This vendor was already linked to the target classification. The duplicate link here was removed.';
        } elseif ($result['removed']) {
            $message = 'Vendor link removed from this classification.';
        } else {
            $message = 'Vendor reassigned successfully.';
        }

        if ($result['brochures_updated'] > 0) {
            $message .= ' '.$result['brochures_updated'].' brochure(s) updated.';
        }

        return $message.' Procurement requests were not changed.';
    }

    private function vendorLinksResponse(Category $category, ?Subcategory $subcategory): JsonResponse
    {
        $query = VendorCategory::query()
            ->with(['vendor'])
            ->where('category_id', $category->id)
            ->orderBy('id');

        if ($subcategory === null) {
            $query->whereNull('subcategory_id');
        } else {
            $query->where('subcategory_id', $subcategory->id);
        }

        $vendorLinks = $query->get()->map(function (VendorCategory $link) use ($category) {
            $link->setAttribute(
                'matching_brochures_count',
                $this->vendorLinkService->countMatchingBrochures($link)
            );
            $link->setAttribute(
                'other_links_in_category',
                VendorCategory::query()
                    ->where('vendor_id', $link->vendor_id)
                    ->where('category_id', $category->id)
                    ->whereKeyNot($link->id)
                    ->with('subcategory')
                    ->get()
            );

            return $link;
        });

        $catalogCategories = Category::query()
            ->with(['subcategories' => fn ($q) => $q->orderBy('name_en')])
            ->orderBy('name_ar')
            ->orderBy('name_en')
            ->get();

        $subcategoriesByCategory = $catalogCategories->mapWithKeys(fn (Category $cat) => [
            $cat->id => $cat->subcategories->map(fn (Subcategory $sub) => [
                'id' => $sub->id,
                'label' => trim($sub->name_ar.' — '.$sub->name_en, ' —'),
            ])->values(),
        ])->all();

        return response()->json([
            'category' => [
                'id' => $category->id,
                'name_en' => $category->name_en,
                'name_ar' => $category->name_ar,
            ],
            'subcategory' => $subcategory === null ? null : [
                'id' => $subcategory->id,
                'name_en' => $subcategory->name_en,
                'name_ar' => $subcategory->name_ar,
            ],
            'vendor_links' => VendorCategoryLinkResource::collection($vendorLinks)->resolve(),
            'catalog_categories' => $catalogCategories->map(fn (Category $cat) => [
                'id' => $cat->id,
                'name_en' => $cat->name_en,
                'name_ar' => $cat->name_ar,
            ])->values(),
            'subcategories_by_category' => $subcategoriesByCategory,
        ]);
    }

    private function loadShowRelations(Category $category): void
    {
        $category->load(['subcategories' => fn ($q) => $q->orderBy('name_en')->withCount('vendors')]);
        $category->loadCount('subcategories');
        $category->setAttribute(
            'category_only_vendor_count',
            VendorCategory::query()
                ->where('category_id', $category->id)
                ->whereNull('subcategory_id')
                ->distinct('vendor_id')
                ->count('vendor_id')
        );
    }

    /**
     * @return list<array{id: int, name_en: string|null, name_ar: string|null}>
     */
    private function allCategoriesForForm(): array
    {
        return Category::query()
            ->orderBy('name_ar')
            ->orderBy('name_en')
            ->get(['id', 'name_en', 'name_ar'])
            ->map(fn (Category $c) => [
                'id' => $c->id,
                'name_en' => $c->name_en,
                'name_ar' => $c->name_ar,
            ])
            ->values()
            ->all();
    }

    /**
     * @param  array<int, array<string, mixed>>  $rows
     * @return array{0: list<array<string, mixed>>, 1: list<array<string, mixed>>}
     */
    private function partitionSubcategoryRows(array $rows, int $currentCategoryId): array
    {
        $moveRows = [];
        $stayRows = [];

        foreach ($rows as $row) {
            $targetCategoryId = isset($row['target_category_id']) && $row['target_category_id'] !== ''
                ? (int) $row['target_category_id']
                : $currentCategoryId;

            if ($targetCategoryId !== $currentCategoryId && ! empty($row['id'])) {
                $row['target_category_id'] = $targetCategoryId;
                $moveRows[] = $row;

                continue;
            }

            $stayRows[] = $row;
        }

        return [$moveRows, $stayRows];
    }
}
