<?php

namespace App\Support\Procurement\Categories;

class CategoryExcelHeadings
{
    /**
     * Friendly export headers → canonical import keys.
     *
     * @var array<string, string>
     */
    public const ALIASES = [
        'category_arabic' => 'category_name_ar',
        'category_english' => 'category_name_en',
        'subcategory_arabic' => 'subcategory_name_ar',
        'subcategory_english' => 'subcategory_name_en',
    ];

    public static function normalizeKey(string $header): string
    {
        $key = strtolower((string) preg_replace('/[^a-z0-9]+/i', '_', $header));
        $key = trim($key, '_');

        return self::ALIASES[$key] ?? $key;
    }
}
