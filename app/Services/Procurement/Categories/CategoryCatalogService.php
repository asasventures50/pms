<?php

namespace App\Services\Procurement\Categories;

use App\Models\Procurement\ProcurementRequests\ProcurementRequestItem;
use App\Models\Procurement\Vendors\Category;
use App\Models\Procurement\Vendors\Subcategory;
use App\Models\Procurement\Vendors\VendorBrochure;
use App\Models\Procurement\Vendors\VendorCategory;
use App\Support\CatalogIdentifiers;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CategoryCatalogService
{
    /**
     * @param  array<int, array<string, mixed>>  $rows
     */
    public function syncSubcategories(Category $category, array $rows): void
    {
        $normalized = $this->normalizeSubcategoryRows($rows);

        $keptIds = [];

        foreach ($normalized as $row) {
            $slug = Str::slug((string) $row['slug']);
            $payload = [
                'name_en' => $row['name_en'],
                'name_ar' => $row['name_ar'],
                'slug' => $slug,
                'status' => $row['status'],
            ];

            if (! empty($row['id'])) {
                $sub = Subcategory::query()
                    ->where('category_id', $category->id)
                    ->whereKey($row['id'])
                    ->first();

                if ($sub) {
                    $sub->fill($payload);
                    $sub->save();
                    $keptIds[] = $sub->id;

                    continue;
                }
            }

            $created = Subcategory::query()->create(array_merge($payload, [
                'category_id' => $category->id,
            ]));
            $keptIds[] = $created->id;
        }

        Subcategory::query()
            ->where('category_id', $category->id)
            ->whereNotIn('id', $keptIds)
            ->get()
            ->each(fn (Subcategory $subcategory) => $this->softDeleteSubcategory($subcategory));
    }

    public function linkedVendorCountForCategory(Category $category): int
    {
        return (int) VendorCategory::query()
            ->where('category_id', $category->id)
            ->distinct()
            ->count('vendor_id');
    }

    public function linkedVendorCountForSubcategory(Subcategory $subcategory): int
    {
        return (int) VendorCategory::query()
            ->where('subcategory_id', $subcategory->id)
            ->distinct()
            ->count('vendor_id');
    }

    /**
     * @param  array<int, array<string, mixed>>  $rows
     * @return list<array{id?: int, name_en: string, name_ar: string, slug: string, status: string}>
     */
    public function normalizeSubcategoryRows(array $rows): array
    {
        $out = [];

        foreach ($rows as $row) {
            if (! is_array($row)) {
                continue;
            }

            $nameEn = isset($row['name_en']) ? trim((string) $row['name_en']) : '';
            $nameAr = isset($row['name_ar']) ? trim((string) $row['name_ar']) : '';
            $slugRaw = isset($row['slug']) ? trim((string) $row['slug']) : '';
            $status = isset($row['status']) ? trim((string) $row['status']) : '';

            if ($nameEn === '' && $nameAr === '' && $slugRaw === '' && $status === '') {
                continue;
            }

            $slug = $slugRaw !== '' ? Str::slug($slugRaw) : Str::slug($nameEn);

            $out[] = [
                'id' => isset($row['id']) && $row['id'] !== '' ? (int) $row['id'] : null,
                'name_en' => $nameEn,
                'name_ar' => $nameAr,
                'slug' => $slug,
                'status' => $status !== '' ? $status : 'active',
            ];
        }

        return $out;
    }

    public function softDeleteCategoryCascade(Category $category): int
    {
        $detachedVendorLinks = 0;

        DB::transaction(function () use ($category, &$detachedVendorLinks) {
            $detachedVendorLinks = $this->detachVendorLinksForCategory($category);
            $this->nullifyCategoryReferences($category);

            Subcategory::query()
                ->where('category_id', $category->id)
                ->get()
                ->each(function (Subcategory $subcategory): void {
                    $this->releaseSubcategoryIdentifiers($subcategory);
                    $subcategory->delete();
                });

            $this->releaseCategoryIdentifiers($category);
            $category->delete();
        });

        return $detachedVendorLinks;
    }

    public function softDeleteSubcategory(Subcategory $subcategory): int
    {
        $detachedVendorLinks = 0;

        DB::transaction(function () use ($subcategory, &$detachedVendorLinks) {
            $detachedVendorLinks = $this->detachVendorLinksForSubcategory($subcategory);
            $this->nullifySubcategoryReferences($subcategory);
            $this->releaseSubcategoryIdentifiers($subcategory);
            $subcategory->delete();
        });

        return $detachedVendorLinks;
    }

    public function detachVendorLinksForCategory(Category $category): int
    {
        return VendorCategory::query()
            ->where('category_id', $category->id)
            ->delete();
    }

    public function detachVendorLinksForSubcategory(Subcategory $subcategory): int
    {
        return VendorCategory::query()
            ->where('subcategory_id', $subcategory->id)
            ->delete();
    }

    private function releaseCategoryIdentifiers(Category $category): void
    {
        $category->update([
            'slug' => CatalogIdentifiers::releaseSlug($category->slug, $category->id),
            'name_en' => CatalogIdentifiers::releaseNameEn($category->name_en, $category->id),
        ]);
    }

    private function releaseSubcategoryIdentifiers(Subcategory $subcategory): void
    {
        $subcategory->update([
            'slug' => CatalogIdentifiers::releaseSlug($subcategory->slug, $subcategory->id),
            'name_en' => CatalogIdentifiers::releaseNameEn($subcategory->name_en, $subcategory->id),
        ]);
    }

    private function nullifyCategoryReferences(Category $category): void
    {
        VendorBrochure::query()
            ->where('category_id', $category->id)
            ->update([
                'category_id' => null,
                'subcategory_id' => null,
            ]);

        ProcurementRequestItem::query()
            ->where('category_id', $category->id)
            ->update([
                'category_id' => null,
                'subcategory_id' => null,
            ]);
    }

    private function nullifySubcategoryReferences(Subcategory $subcategory): void
    {
        VendorBrochure::query()
            ->where('subcategory_id', $subcategory->id)
            ->update(['subcategory_id' => null]);

        ProcurementRequestItem::query()
            ->where('subcategory_id', $subcategory->id)
            ->update(['subcategory_id' => null]);
    }
}
