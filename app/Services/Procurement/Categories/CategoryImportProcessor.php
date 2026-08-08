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
    /**
     * Last explicit category values — used when following rows leave category cells blank.
     *
     * @var array{
     *     category_name_ar: string,
     *     category_name_en: string,
     *     category_slug: string,
     *     category_status: string
     * }|null
     */
    private ?array $carriedCategory = null;

    public function process(Collection $rows): CategoriesImportResult
    {
        $result = new CategoriesImportResult;
        $this->carriedCategory = null;

        foreach ($rows as $index => $row) {
            $line = $index + 2;
            $data = $this->normalizeRowKeys($row);

            if ($this->isRowEmpty($data)) {
                continue;
            }

            $data = $this->applyCategoryCarryForward($data);

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
     * Friendly export headers → canonical import keys.
     *
     * @var array<string, string>
     */
    private const HEADER_ALIASES = [
        'category_arabic' => 'category_name_ar',
        'category_english' => 'category_name_en',
        'subcategory_arabic' => 'subcategory_name_ar',
        'subcategory_english' => 'subcategory_name_en',
    ];

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
            $key = self::HEADER_ALIASES[$key] ?? $key;
            $out[$key] = is_string($v) ? trim($v) : $v;
        }

        return $out;
    }

    private function normalizeStatus(string $status, string $fallback = 'active'): string
    {
        $value = strtolower(trim($status));

        return in_array($value, ['active', 'inactive'], true) ? $value : $fallback;
    }

    /**
     * Fill blank category cells from the previous explicit category row.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function applyCategoryCarryForward(array $data): array
    {
        $catNameEn = trim((string) ($data['category_name_en'] ?? ''));
        $catNameAr = trim((string) ($data['category_name_ar'] ?? ''));
        $catSlug = trim((string) ($data['category_slug'] ?? ''));
        $catStatus = trim((string) ($data['category_status'] ?? ''));

        $hasCategoryIdentity = $catNameEn !== '' || $catSlug !== '';

        if ($hasCategoryIdentity) {
            $this->carriedCategory = [
                'category_name_ar' => $catNameAr,
                'category_name_en' => $catNameEn,
                'category_slug' => $catSlug,
                'category_status' => $catStatus,
            ];

            return $data;
        }

        if ($this->carriedCategory === null) {
            return $data;
        }

        foreach ($this->carriedCategory as $key => $value) {
            if (trim((string) ($data[$key] ?? '')) === '') {
                $data[$key] = $value;
            }
        }

        return $data;
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

        $status = $this->normalizeStatus($catStatus);

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
            'status' => $catStatus !== '' ? $this->normalizeStatus($catStatus, (string) $category->status) : $category->status,
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
        $status = $this->normalizeStatus($subStatus);

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
            'status' => $subStatus !== '' ? $this->normalizeStatus($subStatus, (string) $sub->status) : $sub->status,
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
