<?php

namespace App\Services\Procurement\Categories;

use App\DataTransferObjects\Procurement\CategoryRebuildApplyResult;
use App\Models\Procurement\ProcurementRequests\ProcurementRequestItem;
use App\Models\Procurement\QuickReceipts\QuickReceipt;
use App\Models\Procurement\Vendors\Category;
use App\Models\Procurement\Vendors\Subcategory;
use App\Models\Procurement\Vendors\VendorBrochure;
use App\Models\Procurement\Vendors\VendorCategory;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CategoryRebuildApplyService
{
    public function __construct(
        private CategoryCatalogService $catalogService,
    ) {}

    /**
     * @param  list<array{key: string, name_ar: string, name_en: string, slug: string, subcategories: list<array{key: string, name_ar: string, name_en: string, slug: string}>}>  $proposed
     * @param  array<int|string, string|null>  $categoryMap  old category id → proposed category key or blank
     * @param  array<int|string, string|null>  $subcategoryMap  old subcategory id → proposed subcategory key or blank
     */
    public function apply(
        array $proposed,
        array $categoryMap,
        array $subcategoryMap,
        bool $retireMapped,
    ): CategoryRebuildApplyResult {
        $this->assertMapsAreValid($proposed, $categoryMap, $subcategoryMap);

        $result = new CategoryRebuildApplyResult;

        DB::transaction(function () use ($proposed, $categoryMap, $subcategoryMap, $retireMapped, $result) {
            $resolved = $this->upsertProposedTree($proposed, $result);

            foreach ($subcategoryMap as $oldId => $proposedKey) {
                $proposedKey = trim((string) $proposedKey);
                if ($proposedKey === '' || $proposedKey === 'keep') {
                    continue;
                }

                $old = Subcategory::query()->find((int) $oldId);
                $target = $resolved[$proposedKey] ?? null;
                if ($old === null || $target === null || ($target['subcategory_id'] ?? null) === null) {
                    continue;
                }

                $newSubId = (int) $target['subcategory_id'];
                $newCatId = (int) $target['category_id'];
                if ($old->id === $newSubId) {
                    continue;
                }

                $this->reassignSubcategoryRecords($old, $newCatId, $newSubId, $result);
                $result->subcategoriesMapped++;

                if ($retireMapped) {
                    $this->catalogService->softDeleteSubcategory($old->fresh());
                    $result->oldSubcategoriesRetired++;
                }
            }

            foreach ($categoryMap as $oldId => $proposedKey) {
                $proposedKey = trim((string) $proposedKey);
                if ($proposedKey === '' || $proposedKey === 'keep') {
                    continue;
                }

                $old = Category::query()->find((int) $oldId);
                $newCatId = $resolved[$proposedKey]['category_id'] ?? null;
                if ($old === null || $newCatId === null) {
                    continue;
                }
                $newCatId = (int) $newCatId;
                if ($old->id === $newCatId) {
                    continue;
                }

                $this->reassignCategoryOnlyRecords($old, $newCatId, $result);
                $result->categoriesMapped++;

                if ($retireMapped) {
                    $remainingSubs = Subcategory::query()->where('category_id', $old->id)->count();
                    $remainingRefs = $this->categoryRemainingReferences($old->id);
                    if ($remainingSubs === 0 && $remainingRefs === 0) {
                        $this->catalogService->softDeleteCategoryCascade($old->fresh());
                        $result->oldCategoriesRetired++;
                    }
                }
            }
        });

        return $result;
    }

    /**
     * @param  list<array{key: string, name_ar: string, name_en: string, slug: string, subcategories: list<array{key: string, name_ar: string, name_en: string, slug: string}>}>  $proposed
     * @param  array<int|string, string|null>  $categoryMap
     * @param  array<int|string, string|null>  $subcategoryMap
     */
    private function assertMapsAreValid(array $proposed, array $categoryMap, array $subcategoryMap): void
    {
        $categoryKeys = [];
        $subcategoryKeys = [];
        foreach ($proposed as $category) {
            $categoryKeys[$category['key']] = true;
            foreach ($category['subcategories'] as $sub) {
                $subcategoryKeys[$sub['key']] = true;
            }
        }

        foreach ($categoryMap as $key) {
            $key = trim((string) $key);
            if ($key === '' || $key === 'keep') {
                continue;
            }
            if (! isset($categoryKeys[$key])) {
                throw ValidationException::withMessages([
                    'category_map' => 'A category mapping points to a classification that is not in the uploaded file.',
                ]);
            }
        }

        foreach ($subcategoryMap as $key) {
            $key = trim((string) $key);
            if ($key === '' || $key === 'keep') {
                continue;
            }
            if (! isset($subcategoryKeys[$key])) {
                throw ValidationException::withMessages([
                    'subcategory_map' => 'A subcategory mapping points to a classification that is not in the uploaded file.',
                ]);
            }
        }
    }

    /**
     * @param  list<array{key: string, name_ar: string, name_en: string, slug: string, subcategories: list<array{key: string, name_ar: string, name_en: string, slug: string}>}>  $proposed
     * @return array<string, array{category_id: int, subcategory_id: int|null}>
     */
    private function upsertProposedTree(array $proposed, CategoryRebuildApplyResult $result): array
    {
        $resolved = [];

        foreach ($proposed as $categoryRow) {
            $category = $this->upsertCategory($categoryRow, $result);
            $resolved[$categoryRow['key']] = [
                'category_id' => $category->id,
                'subcategory_id' => null,
            ];

            foreach ($categoryRow['subcategories'] as $subRow) {
                $sub = $this->upsertSubcategory($category, $subRow, $result);
                $resolved[$subRow['key']] = [
                    'category_id' => $category->id,
                    'subcategory_id' => $sub->id,
                ];
            }
        }

        return $resolved;
    }

    /**
     * @param  array{name_ar: string, name_en: string, slug: string}  $row
     */
    private function upsertCategory(array $row, CategoryRebuildApplyResult $result): Category
    {
        $category = Category::query()
            ->where('name_en', $row['name_en'])
            ->first();

        if (! $category) {
            $category = Category::query()->create([
                'name_en' => $row['name_en'],
                'name_ar' => $row['name_ar'] !== '' ? $row['name_ar'] : $row['name_en'],
                'slug' => $this->uniqueCategorySlug($row['slug']),
                'status' => 'active',
            ]);
            $result->categoriesCreated++;

            return $category;
        }

        if ($row['name_ar'] !== '' && $category->name_ar !== $row['name_ar']) {
            $category->update(['name_ar' => $row['name_ar']]);
            $result->categoriesUpdated++;
        }

        return $category;
    }

    /**
     * @param  array{name_ar: string, name_en: string, slug: string}  $row
     */
    private function upsertSubcategory(Category $category, array $row, CategoryRebuildApplyResult $result): Subcategory
    {
        $sub = Subcategory::query()
            ->where('category_id', $category->id)
            ->where(function ($query) use ($row) {
                $query->where('name_en', $row['name_en']);
                if ($row['slug'] !== '') {
                    $query->orWhere('slug', $row['slug']);
                }
            })
            ->first();

        if (! $sub) {
            $sub = Subcategory::query()->create([
                'category_id' => $category->id,
                'name_en' => $row['name_en'],
                'name_ar' => $row['name_ar'] !== '' ? $row['name_ar'] : $row['name_en'],
                'slug' => $this->uniqueSubcategorySlug($category->id, $row['slug']),
                'status' => 'active',
            ]);
            $result->subcategoriesCreated++;

            return $sub;
        }

        $updates = [];
        if ($row['name_en'] !== '' && $sub->name_en !== $row['name_en']) {
            $updates['name_en'] = $row['name_en'];
        }
        if ($row['name_ar'] !== '' && $sub->name_ar !== $row['name_ar']) {
            $updates['name_ar'] = $row['name_ar'];
        }
        if ($updates !== []) {
            $sub->update($updates);
            $result->subcategoriesUpdated++;
        }

        return $sub;
    }

    private function uniqueCategorySlug(string $slug): string
    {
        $base = $slug !== '' ? $slug : 'category';
        $candidate = $base;
        $i = 2;
        while (Category::query()->where('slug', $candidate)->exists()) {
            $candidate = $base.'-'.$i;
            $i++;
        }

        return $candidate;
    }

    private function uniqueSubcategorySlug(int $categoryId, string $slug): string
    {
        $base = $slug !== '' ? $slug : 'subcategory';
        $candidate = $base;
        $i = 2;
        while (Subcategory::query()->where('category_id', $categoryId)->where('slug', $candidate)->exists()) {
            $candidate = $base.'-'.$i;
            $i++;
        }

        return $candidate;
    }

    private function reassignSubcategoryRecords(
        Subcategory $old,
        int $newCategoryId,
        int $newSubcategoryId,
        CategoryRebuildApplyResult $result,
    ): void {
        $newCategory = Category::query()->find($newCategoryId);
        $newSub = Subcategory::query()->find($newSubcategoryId);

        $result->procurementRequestsUpdated += ProcurementRequestItem::query()
            ->where('subcategory_id', $old->id)
            ->update([
                'category_id' => $newCategoryId,
                'subcategory_id' => $newSubcategoryId,
                'category' => $newCategory?->name_en,
                'subcategory' => $newSub?->name_en,
            ]);

        $result->brochuresUpdated += VendorBrochure::query()
            ->where('subcategory_id', $old->id)
            ->update([
                'category_id' => $newCategoryId,
                'subcategory_id' => $newSubcategoryId,
            ]);

        VendorCategory::query()
            ->where('subcategory_id', $old->id)
            ->orderBy('id')
            ->get()
            ->each(function (VendorCategory $link) use ($newCategoryId, $newSubcategoryId, $result) {
                $duplicate = VendorCategory::query()
                    ->where('vendor_id', $link->vendor_id)
                    ->where('category_id', $newCategoryId)
                    ->where('subcategory_id', $newSubcategoryId)
                    ->whereKeyNot($link->id)
                    ->first();

                if ($duplicate) {
                    $link->delete();
                    $result->vendorLinksUpdated++;

                    return;
                }

                $link->update([
                    'category_id' => $newCategoryId,
                    'subcategory_id' => $newSubcategoryId,
                ]);
                $result->vendorLinksUpdated++;
            });
    }

    private function reassignCategoryOnlyRecords(
        Category $old,
        int $newCategoryId,
        CategoryRebuildApplyResult $result,
    ): void {
        $newCategory = Category::query()->find($newCategoryId);

        $result->procurementRequestsUpdated += ProcurementRequestItem::query()
            ->where('category_id', $old->id)
            ->whereNull('subcategory_id')
            ->update([
                'category_id' => $newCategoryId,
                'category' => $newCategory?->name_en,
            ]);

        $result->brochuresUpdated += VendorBrochure::query()
            ->where('category_id', $old->id)
            ->whereNull('subcategory_id')
            ->update(['category_id' => $newCategoryId]);

        $result->quickReceiptsUpdated += QuickReceipt::query()
            ->where('category_id', $old->id)
            ->update(['category_id' => $newCategoryId]);

        VendorCategory::query()
            ->where('category_id', $old->id)
            ->whereNull('subcategory_id')
            ->orderBy('id')
            ->get()
            ->each(function (VendorCategory $link) use ($newCategoryId, $result) {
                $duplicate = VendorCategory::query()
                    ->where('vendor_id', $link->vendor_id)
                    ->where('category_id', $newCategoryId)
                    ->whereNull('subcategory_id')
                    ->whereKeyNot($link->id)
                    ->first();

                if ($duplicate) {
                    $link->delete();
                    $result->vendorLinksUpdated++;

                    return;
                }

                $link->update(['category_id' => $newCategoryId]);
                $result->vendorLinksUpdated++;
            });
    }

    private function categoryRemainingReferences(int $categoryId): int
    {
        return ProcurementRequestItem::query()->where('category_id', $categoryId)->count()
            + VendorCategory::query()->where('category_id', $categoryId)->count()
            + VendorBrochure::query()->where('category_id', $categoryId)->count()
            + QuickReceipt::query()->where('category_id', $categoryId)->count();
    }
}
