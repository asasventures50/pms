<?php

namespace App\Services\Procurement\Categories;

use App\DataTransferObjects\Procurement\CategoriesImportResult;
use App\Models\Procurement\Vendors\Category;
use App\Models\Procurement\Vendors\Subcategory;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CategoryImportProcessor
{
    public function process(Collection $rows): CategoriesImportResult
    {
        $result = new CategoriesImportResult;

        foreach ($rows as $index => $row) {
            $line = $index + 2;
            $data = $this->normalizeRowKeys($row);

            if ($this->isRowEmpty($data)) {
                continue;
            }

            try {
                $this->processRow($data, $result);
            } catch (\Throwable $e) {
                $result->failedRows++;
                $result->errors[] = "Row {$line}: ".$e->getMessage();
            }
        }

        return $result;
    }

    /**
     * @param  array<string, mixed>|Collection<int|string, mixed>  $row
     * @return array<string, mixed>
     */
    private function normalizeRowKeys(array|Collection $row): array
    {
        $arr = $row instanceof Collection ? $row->toArray() : $row;
        $out = [];

        foreach ($arr as $k => $v) {
            if (! is_string($k)) {
                continue;
            }

            $key = strtolower(preg_replace('/[^a-z0-9]+/i', '_', $k));
            $key = trim($key, '_');
            $out[$key] = is_string($v) ? trim($v) : $v;
        }

        return $out;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function processRow(array $data, CategoriesImportResult $result): void
    {
        $catNameEn = (string) ($data['category_name_en'] ?? '');
        $catNameAr = (string) ($data['category_name_ar'] ?? '');
        $catSlugRaw = (string) ($data['category_slug'] ?? '');
        $catStatus = (string) ($data['category_status'] ?? '');

        if ($catNameEn === '' && $catSlugRaw === '') {
            throw new \InvalidArgumentException('Category English name or category slug is required.');
        }

        $catSlug = $catSlugRaw !== '' ? Str::slug($catSlugRaw) : Str::slug($catNameEn);

        if ($catSlug === '') {
            throw new \InvalidArgumentException('Could not derive category slug.');
        }

        $subNameEn = (string) ($data['subcategory_name_en'] ?? '');
        $subNameAr = (string) ($data['subcategory_name_ar'] ?? '');
        $subSlugRaw = (string) ($data['subcategory_slug'] ?? '');
        $subStatus = (string) ($data['subcategory_status'] ?? '');

        $hasSubData = $subNameEn !== '' || $subNameAr !== '' || $subSlugRaw !== '' || $subStatus !== '';

        DB::transaction(function () use (
            $catNameEn,
            $catNameAr,
            $catSlug,
            $catStatus,
            $subNameEn,
            $subNameAr,
            $subSlugRaw,
            $subStatus,
            $hasSubData,
            $result
        ) {
            $category = $this->resolveCategory($catNameEn, $catSlug, $catNameAr, $catStatus, $result);

            if (! $hasSubData) {
                return;
            }

            if ($subNameEn === '' && $subSlugRaw === '') {
                throw new \InvalidArgumentException('Subcategory requires English name or slug.');
            }

            $subSlug = $subSlugRaw !== '' ? Str::slug($subSlugRaw) : Str::slug($subNameEn);

            if ($subSlug === '') {
                throw new \InvalidArgumentException('Could not derive subcategory slug.');
            }

            $this->upsertSubcategory($category, $subNameEn, $subNameAr, $subSlug, $subStatus, $result);
        });
    }

    private function resolveCategory(
        string $catNameEn,
        string $catSlug,
        string $catNameAr,
        string $catStatus,
        CategoriesImportResult $result
    ): Category {
        $category = Category::query()
            ->where('slug', $catSlug)
            ->first();

        if (! $category && $catNameEn !== '') {
            $category = Category::query()
                ->where('name_en', $catNameEn)
                ->first();
        }

        $status = in_array($catStatus, ['active', 'inactive'], true) ? $catStatus : 'active';

        if (! $category) {
            $category = Category::query()->create([
                'name_en' => $catNameEn !== '' ? $catNameEn : $catSlug,
                'name_ar' => $catNameAr !== '' ? $catNameAr : ($catNameEn !== '' ? $catNameEn : $catSlug),
                'slug' => $catSlug,
                'status' => $status,
            ]);
            $result->categoriesCreated++;

            return $category;
        }

        $category->fill([
            'name_en' => $catNameEn !== '' ? $catNameEn : $category->name_en,
            'name_ar' => $catNameAr !== '' ? $catNameAr : $category->name_ar,
            'slug' => $catSlug,
            'status' => $catStatus !== '' ? $status : $category->status,
        ]);

        if ($category->isDirty()) {
            $category->save();
            $result->categoriesUpdated++;
        }

        return $category;
    }

    private function upsertSubcategory(
        Category $category,
        string $subNameEn,
        string $subNameAr,
        string $subSlug,
        string $subStatus,
        CategoriesImportResult $result
    ): void {
        $status = in_array($subStatus, ['active', 'inactive'], true) ? $subStatus : 'active';

        $sub = Subcategory::query()
            ->where('category_id', $category->id)
            ->where(function ($q) use ($subSlug, $subNameEn) {
                $q->where('slug', $subSlug);
                if ($subNameEn !== '') {
                    $q->orWhere('name_en', $subNameEn);
                }
            })
            ->first();

        if (! $sub) {
            Subcategory::query()->create([
                'category_id' => $category->id,
                'name_en' => $subNameEn !== '' ? $subNameEn : $subSlug,
                'name_ar' => $subNameAr !== '' ? $subNameAr : ($subNameEn !== '' ? $subNameEn : $subSlug),
                'slug' => $subSlug,
                'status' => $status,
            ]);
            $result->subcategoriesCreated++;

            return;
        }

        $sub->fill([
            'name_en' => $subNameEn !== '' ? $subNameEn : $sub->name_en,
            'name_ar' => $subNameAr !== '' ? $subNameAr : $sub->name_ar,
            'slug' => $subSlug,
            'status' => $subStatus !== '' ? $status : $sub->status,
        ]);

        if ($sub->isDirty()) {
            $sub->save();
            $result->subcategoriesUpdated++;
        }
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function isRowEmpty(array $data): bool
    {
        $keys = [
            'category_name_ar', 'category_name_en', 'category_slug', 'category_status',
            'subcategory_name_ar', 'subcategory_name_en', 'subcategory_slug', 'subcategory_status',
        ];

        foreach ($keys as $k) {
            $v = $data[$k] ?? null;
            if ($v !== null && trim((string) $v) !== '') {
                return false;
            }
        }

        return true;
    }
}
