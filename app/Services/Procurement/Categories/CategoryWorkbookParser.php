<?php

namespace App\Services\Procurement\Categories;

use App\Support\Procurement\Categories\CategoryExcelHeadings;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class CategoryWorkbookParser
{
    /**
     * @return array{
     *     sheet: string,
     *     categories: list<array{
     *         key: string,
     *         name_ar: string,
     *         name_en: string,
     *         slug: string,
     *         subcategories: list<array{
     *             key: string,
     *             name_ar: string,
     *             name_en: string,
     *             slug: string
     *         }>
     *     }>
     * }
     */
    public function parse(UploadedFile $file): array
    {
        try {
            $spreadsheet = IOFactory::load($file->getRealPath() ?: $file->getPathname());
        } catch (\Throwable) {
            throw ValidationException::withMessages([
                'file' => 'The Excel file could not be read.',
            ]);
        }

        $worksheet = $this->pickWorksheet($spreadsheet->getAllSheets());
        $rows = $this->rowsFromWorksheet($worksheet);
        $categories = $this->categoriesFromRows($rows);

        if ($categories === []) {
            throw ValidationException::withMessages([
                'file' => 'No categories were found. Use the export headers (Category Arabic/English and Subcategory Arabic/English).',
            ]);
        }

        return [
            'sheet' => $worksheet->getTitle(),
            'categories' => $categories,
        ];
    }

    /**
     * @param  list<Worksheet>  $sheets
     */
    private function pickWorksheet(array $sheets): Worksheet
    {
        if ($sheets === []) {
            throw ValidationException::withMessages([
                'file' => 'The workbook has no sheets.',
            ]);
        }

        foreach ($sheets as $sheet) {
            if (stripos($sheet->getTitle(), 'updated') !== false) {
                return $sheet;
            }
        }

        $best = $sheets[0];
        $bestScore = -1;

        foreach ($sheets as $sheet) {
            $rows = $this->rowsFromWorksheet($sheet);
            $score = count($rows);
            if ($this->headingMap($rows[0] ?? []) !== []) {
                $score += 1000;
            }
            if ($score > $bestScore) {
                $bestScore = $score;
                $best = $sheet;
            }
        }

        return $best;
    }

    /**
     * @return list<list<mixed>>
     */
    private function rowsFromWorksheet(Worksheet $worksheet): array
    {
        $rows = [];

        foreach ($worksheet->toArray(null, true, true, false) as $row) {
            if (! is_array($row)) {
                continue;
            }
            $rows[] = array_values($row);
        }

        return $rows;
    }

    /**
     * @param  list<list<mixed>>  $rows
     * @return list<array{key: string, name_ar: string, name_en: string, slug: string, subcategories: list<array{key: string, name_ar: string, name_en: string, slug: string}>}>
     */
    private function categoriesFromRows(array $rows): array
    {
        if ($rows === []) {
            return [];
        }

        $headingMap = $this->headingMap($rows[0]);
        if ($headingMap === []) {
            return [];
        }

        $carried = [
            'category_name_ar' => '',
            'category_name_en' => '',
            'category_slug' => '',
        ];
        $categories = [];
        $indexByKey = [];

        foreach (array_slice($rows, 1) as $row) {
            $data = $this->rowToCanonical($row, $headingMap);
            if ($this->isEmpty($data)) {
                continue;
            }

            $catAr = trim((string) ($data['category_name_ar'] ?? ''));
            $catEn = trim((string) ($data['category_name_en'] ?? ''));
            $catSlugRaw = trim((string) ($data['category_slug'] ?? ''));

            if ($catAr !== '' || $catEn !== '' || $catSlugRaw !== '') {
                $carried = [
                    'category_name_ar' => $catAr,
                    'category_name_en' => $catEn,
                    'category_slug' => $catSlugRaw,
                ];
            }

            $catEn = trim((string) $carried['category_name_en']);
            $catAr = trim((string) $carried['category_name_ar']);
            $catSlugRaw = trim((string) $carried['category_slug']);

            if ($catEn === '' && $catSlugRaw === '') {
                continue;
            }

            $catSlug = $catSlugRaw !== '' ? Str::slug($catSlugRaw) : Str::slug($catEn);
            if ($catSlug === '') {
                $catSlug = 'category-'.(count($categories) + 1);
            }

            $catKey = 'c:'.$catSlug;
            if (! isset($indexByKey[$catKey])) {
                $indexByKey[$catKey] = count($categories);
                $categories[] = [
                    'key' => $catKey,
                    'name_ar' => $catAr !== '' ? $catAr : $catEn,
                    'name_en' => $catEn !== '' ? $catEn : $catSlug,
                    'slug' => $catSlug,
                    'subcategories' => [],
                ];
            }

            $subAr = trim((string) ($data['subcategory_name_ar'] ?? ''));
            $subEn = trim((string) ($data['subcategory_name_en'] ?? ''));
            $subSlugRaw = trim((string) ($data['subcategory_slug'] ?? ''));

            if ($subEn === '' && $subAr === '' && $subSlugRaw === '') {
                continue;
            }

            if ($subEn === '' && $subSlugRaw === '') {
                continue;
            }

            $subSlug = $subSlugRaw !== '' ? Str::slug($subSlugRaw) : Str::slug($subEn);
            if ($subSlug === '') {
                $subSlug = 'subcategory-'.(count($categories[$indexByKey[$catKey]]['subcategories']) + 1);
            }

            $subKey = 's:'.$catSlug.'/'.$subSlug;
            $parent = &$categories[$indexByKey[$catKey]];
            $exists = false;
            foreach ($parent['subcategories'] as $existing) {
                if ($existing['key'] === $subKey) {
                    $exists = true;
                    break;
                }
            }
            if (! $exists) {
                $parent['subcategories'][] = [
                    'key' => $subKey,
                    'name_ar' => $subAr !== '' ? $subAr : ($subEn !== '' ? $subEn : $subSlug),
                    'name_en' => $subEn !== '' ? $subEn : $subSlug,
                    'slug' => $subSlug,
                ];
            }
            unset($parent);
        }

        return $categories;
    }

    /**
     * @param  list<mixed>  $headingRow
     * @return array<int, string>
     */
    private function headingMap(array $headingRow): array
    {
        $map = [];
        $recognized = [
            'category_name_ar', 'category_name_en', 'category_slug', 'category_status',
            'subcategory_name_ar', 'subcategory_name_en', 'subcategory_slug', 'subcategory_status',
        ];

        foreach ($headingRow as $index => $header) {
            if (! is_string($header) || trim($header) === '') {
                continue;
            }
            $key = CategoryExcelHeadings::normalizeKey($header);
            if (in_array($key, $recognized, true)) {
                $map[(int) $index] = $key;
            }
        }

        return $map;
    }

    /**
     * @param  list<mixed>  $row
     * @param  array<int, string>  $headingMap
     * @return array<string, mixed>
     */
    private function rowToCanonical(array $row, array $headingMap): array
    {
        $data = [];
        foreach ($headingMap as $index => $key) {
            $value = $row[$index] ?? null;
            $data[$key] = is_string($value) ? trim($value) : $value;
        }

        return $data;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function isEmpty(array $data): bool
    {
        foreach ($data as $value) {
            if ($value !== null && trim((string) $value) !== '') {
                return false;
            }
        }

        return true;
    }
}
