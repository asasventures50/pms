<?php

namespace App\Support;

use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Unique;

class CatalogIdentifiers
{
    private const DELETED_SLUG_SUFFIX = '-deleted-';

    private const DELETED_NAME_SUFFIX = ' (deleted #';

    public static function releaseSlug(string $slug, int $id): string
    {
        if (self::isReleasedSlug($slug)) {
            return $slug;
        }

        return self::trimToFit($slug, self::DELETED_SLUG_SUFFIX.$id);
    }

    public static function releaseNameEn(string $nameEn, int $id): string
    {
        if (self::isReleasedNameEn($nameEn)) {
            return $nameEn;
        }

        return self::trimToFit($nameEn, self::DELETED_NAME_SUFFIX.$id.')');
    }

    public static function isReleasedSlug(string $slug): bool
    {
        return (bool) preg_match('/-deleted-\d+$/', $slug);
    }

    public static function isReleasedNameEn(string $nameEn): bool
    {
        return str_contains($nameEn, self::DELETED_NAME_SUFFIX);
    }

    public static function uniqueCategorySlug(?int $ignoreId = null): Unique
    {
        $rule = Rule::unique('categories', 'slug')->whereNull('deleted_at');

        return $ignoreId !== null ? $rule->ignore($ignoreId) : $rule;
    }

    public static function uniqueCategoryNameEn(?int $ignoreId = null): Unique
    {
        $rule = Rule::unique('categories', 'name_en')->whereNull('deleted_at');

        return $ignoreId !== null ? $rule->ignore($ignoreId) : $rule;
    }

    public static function uniqueSubcategorySlug(int $categoryId, ?int $ignoreId = null): Unique
    {
        $rule = Rule::unique('subcategories', 'slug')
            ->where('category_id', $categoryId)
            ->whereNull('deleted_at');

        return $ignoreId !== null ? $rule->ignore($ignoreId) : $rule;
    }

    public static function uniqueSubcategoryNameEn(int $categoryId, ?int $ignoreId = null): Unique
    {
        $rule = Rule::unique('subcategories', 'name_en')
            ->where('category_id', $categoryId)
            ->whereNull('deleted_at');

        return $ignoreId !== null ? $rule->ignore($ignoreId) : $rule;
    }

    private static function trimToFit(string $value, string $suffix): string
    {
        $maxBase = 255 - strlen($suffix);
        if ($maxBase < 1) {
            return substr($suffix, 0, 255);
        }

        if (strlen($value) <= $maxBase) {
            return $value.$suffix;
        }

        $base = rtrim(substr($value, 0, $maxBase), '- ');

        return $base.$suffix;
    }
}
