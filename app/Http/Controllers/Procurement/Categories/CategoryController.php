<?php

namespace App\Http\Controllers\Procurement\Categories;

use App\Exports\Procurement\CategoriesExport;
use App\Exports\Procurement\CategoriesTemplateExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\Procurement\Categories\ImportCategoriesRequest;
use App\Http\Requests\Procurement\Categories\ReassignCategoryVendorLinkRequest;
use App\Http\Requests\Procurement\Categories\StoreCategoryRequest;
use App\Http\Requests\Procurement\Categories\UpdateCategoryRequest;
use App\DataTransferObjects\Procurement\SubcategoryMoveResult;
use App\Imports\Procurement\CategoriesImport;
use App\Models\Procurement\Vendors\Category;
use App\Models\Procurement\Vendors\Subcategory;
use App\Models\Procurement\Vendors\VendorCategory;
use App\Services\Procurement\Categories\CategoryCatalogService;
use App\Services\Procurement\Categories\CategoryVendorLinkService;
use App\Services\Procurement\Categories\SubcategoryMoveService;
use App\Support\TableSort;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class CategoryController extends Controller
{
    public function __construct(
        protected CategoryCatalogService $catalogService,
        protected SubcategoryMoveService $moveService,
        protected CategoryVendorLinkService $vendorLinkService,
    ) {}

    public function index(Request $request): View
    {
        $perPage = max(1, min(100, (int) $request->query('per_page', 15)));

        $allowedSorts = ['name_ar', 'name_en', 'created_at'];
        $sort = TableSort::resolve($request, $allowedSorts, 'name_ar', 'asc');

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

        $categories = $query->paginate($perPage)->appends($request->query());

        return view('procurement.categories.index', [
            'categories' => $categories,
            'sortColumn' => $sort['column'],
            'sortDirection' => $sort['direction'],
        ]);
    }

    public function create(): View
    {
        return view('procurement.categories.create', [
            'category' => new Category([
                'status' => 'active',
            ]),
        ]);
    }

    public function store(StoreCategoryRequest $request): RedirectResponse
    {
        $data = $request->validated();

        DB::transaction(function () use ($data) {
            $category = Category::query()->create([
                'name_en' => $data['name_en'],
                'name_ar' => $data['name_ar'],
                'slug' => $data['slug'],
                'status' => $data['status'],
            ]);

            $this->catalogService->syncSubcategories($category, $data['subcategories'] ?? []);
        });

        return redirect()
            ->route('categories.index')
            ->with('success', 'Category created successfully.');
    }

    public function show(Category $category): View
    {
        $category->load(['subcategories' => fn ($q) => $q->orderBy('name_en')->withCount('vendors')]);

        $categoryOnlyVendorCount = VendorCategory::query()
            ->where('category_id', $category->id)
            ->whereNull('subcategory_id')
            ->distinct('vendor_id')
            ->count('vendor_id');

        return view('procurement.categories.show', [
            'category' => $category,
            'categoryOnlyVendorCount' => $categoryOnlyVendorCount,
        ]);
    }

    public function categoryVendorLinks(Category $category): View
    {
        return $this->vendorLinksView($category, null);
    }

    public function subcategoryVendorLinks(Category $category, Subcategory $subcategory): View
    {
        if ((int) $subcategory->category_id !== (int) $category->id) {
            abort(404);
        }

        return $this->vendorLinksView($category, $subcategory);
    }

    public function reassignVendorLink(
        ReassignCategoryVendorLinkRequest $request,
        VendorCategory $vendorCategory,
    ): RedirectResponse {
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

        $message = $this->vendorLinkResultMessage($result);

        return redirect()
            ->to($this->resolveVendorLinksReturnUrl($request, $vendorCategory))
            ->with('success', $message);
    }

    public function removeVendorLink(Request $request, VendorCategory $vendorCategory): RedirectResponse
    {
        $updateBrochures = filter_var($request->input('update_brochures'), FILTER_VALIDATE_BOOLEAN);
        $returnUrl = $this->resolveVendorLinksReturnUrl($request, $vendorCategory);

        $result = $this->vendorLinkService->removeLink($vendorCategory, $updateBrochures);

        return redirect()
            ->to($returnUrl)
            ->with('success', $this->vendorLinkResultMessage($result));
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

    private function vendorLinksView(Category $category, ?Subcategory $subcategory): View
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

        return view('procurement.categories.vendor-links', [
            'category' => $category,
            'subcategory' => $subcategory,
            'vendorLinks' => $vendorLinks,
            'catalogCategories' => $catalogCategories,
            'subcategoriesByCategory' => $subcategoriesByCategory,
        ]);
    }

    private function resolveVendorLinksReturnUrl(Request $request, VendorCategory $vendorCategory): string
    {
        $return = trim((string) $request->input('return_url', ''));
        if ($return !== '') {
            $returnPath = parse_url($return, PHP_URL_PATH) ?? '';
            if (preg_match('#^/categories/\d+/vendor-links$#', $returnPath) === 1
                || preg_match('#^/categories/\d+/subcategories/\d+/vendor-links$#', $returnPath) === 1) {
                return $return;
            }
        }

        if ($vendorCategory->subcategory_id !== null) {
            return route('categories.subcategories.vendor-links', [
                'category' => $vendorCategory->category_id,
                'subcategory' => $vendorCategory->subcategory_id,
            ]);
        }

        return route('categories.vendor-links', $vendorCategory->category_id);
    }

    public function edit(Category $category): View
    {
        $category->load(['subcategories' => fn ($q) => $q->orderBy('name_ar')->orderBy('name_en')->withCount('vendors')]);

        return view('procurement.categories.edit', [
            'category' => $category,
            'allCategories' => Category::query()
                ->orderBy('name_ar')
                ->orderBy('name_en')
                ->get(['id', 'name_en', 'name_ar']),
        ]);
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

    public function update(UpdateCategoryRequest $request, Category $category): RedirectResponse
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

        return redirect()
            ->route('categories.show', $category)
            ->with('success', $message);
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

    public function destroy(Category $category): RedirectResponse
    {
        $detachedVendorLinks = $this->catalogService->softDeleteCategoryCascade($category);

        $message = 'Category deleted successfully.';
        if ($detachedVendorLinks > 0) {
            $message .= ' '.$detachedVendorLinks.' vendor classification link(s) were removed; vendors were not deleted.';
        }

        return redirect()
            ->route('categories.index')
            ->with('success', $message);
    }

    public function export(): BinaryFileResponse
    {
        $filename = 'categories-export-'.now()->format('Y-m-d_His').'.xlsx';

        return Excel::download(new CategoriesExport, $filename);
    }

    public function importForm(): View
    {
        return view('procurement.categories.import');
    }

    public function import(ImportCategoriesRequest $request): RedirectResponse
    {
        /** @var CategoriesImport $import */
        $import = app(CategoriesImport::class);

        Excel::import($import, $request->file('file'));

        $result = $import->result;

        $message = $result->summaryLine();

        if ($result->failedRows > 0 || count($result->errors) > 0) {
            $detail = count($result->errors) > 10
                ? array_merge(array_slice($result->errors, 0, 10), ['Additional errors omitted.'])
                : $result->errors;

            return redirect()
                ->route('categories.import.form')
                ->with('error', $message)
                ->with('import_errors', $detail);
        }

        return redirect()
            ->route('categories.index')
            ->with('success', $message);
    }

    public function downloadTemplate(): BinaryFileResponse
    {
        return Excel::download(new CategoriesTemplateExport, 'categories-import-template.xlsx');
    }
}
