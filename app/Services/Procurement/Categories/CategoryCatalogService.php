<?php

namespace App\Services\Procurement\Categories;

use App\Models\Procurement\Vendors\Category;
use App\Models\Procurement\Vendors\Subcategory;
use App\Models\Procurement\Vendors\VendorCategory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

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
            ->each(function (Subcategory $subcategory) {
                $this->assertSubcategoryCanBeDeleted($subcategory);
                $subcategory->delete();
            });
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

    public function categoryCanBeDeleted(Category $category): bool
    {
        return $this->linkedVendorCountForCategory($category) === 0;
    }

    public function subcategoryCanBeDeleted(Subcategory $subcategory): bool
    {
        return $this->linkedVendorCountForSubcategory($subcategory) === 0;
    }

    public function assertCategoryCanBeDeleted(Category $category): void
    {
        if (! $this->categoryCanBeDeleted($category)) {
            throw new RuntimeException('This category cannot be deleted because it is linked to one or more vendors.');
        }
    }

    public function assertSubcategoryCanBeDeleted(Subcategory $subcategory): void
    {
        if (! $this->subcategoryCanBeDeleted($subcategory)) {
            throw new RuntimeException(
                'Subcategory "'.$subcategory->name_en.'" cannot be removed because it is linked to one or more vendors.'
            );
        }
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

    public function softDeleteCategoryCascade(Category $category): void
    {
        $this->assertCategoryCanBeDeleted($category);

        DB::transaction(function () use ($category) {
            Subcategory::query()
                ->where('category_id', $category->id)
                ->get()
                ->each(fn (Subcategory $s) => $s->delete());

            $category->delete();
        });
    }
}
