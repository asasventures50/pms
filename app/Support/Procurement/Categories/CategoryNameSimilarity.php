<?php

namespace App\Support\Procurement\Categories;

use Illuminate\Support\Str;

class CategoryNameSimilarity
{
    /**
     * @var list<string>
     */
    private const EN_STOP = [
        'a', 'an', 'and', 'of', 'or', 'the', 'to', 'for',
        'work', 'works', 'service', 'services', 'operation', 'operations',
    ];

    /**
     * @var list<string>
     */
    private const AR_STOP = ['اعمال', 'عمل', 'و'];

    /**
     * @param  array{name_ar?: string, name_en?: string, slug?: string}  $left
     * @param  array{name_ar?: string, name_en?: string, slug?: string}  $right
     */
    public function score(array $left, array $right): int
    {
        $arA = $this->normalizeAr((string) ($left['name_ar'] ?? ''));
        $arB = $this->normalizeAr((string) ($right['name_ar'] ?? ''));
        $enA = $this->normalizeEn((string) ($left['name_en'] ?? ''));
        $enB = $this->normalizeEn((string) ($right['name_en'] ?? ''));
        $slugA = $this->normalizeSlug((string) ($left['slug'] ?? ''), $enA);
        $slugB = $this->normalizeSlug((string) ($right['slug'] ?? ''), $enB);

        $score = 0;

        if ($slugA !== '' && $slugA === $slugB) {
            $score = max($score, 100);
        }
        if ($enA !== '' && $enA === $enB) {
            $score = max($score, 100);
        }
        if ($arA !== '' && $arA === $arB) {
            $score = max($score, 98);
        }

        $score = max($score, $this->tokenScore($enA, $enB, self::EN_STOP));
        $score = max($score, $this->tokenScore($arA, $arB, self::AR_STOP));

        return min(100, $score);
    }

    public function normalizeAr(string $value): string
    {
        $value = trim($value);
        if ($value === '') {
            return '';
        }

        $value = strtr($value, [
            'أ' => 'ا',
            'إ' => 'ا',
            'آ' => 'ا',
            'ٱ' => 'ا',
            'ة' => 'ه',
            'ى' => 'ي',
            'ؤ' => 'و',
            'ئ' => 'ي',
            'ـ' => '',
        ]);
        $value = (string) preg_replace('/[\x{064B}-\x{065F}\x{0670}]/u', '', $value);
        $value = (string) preg_replace('/[^\p{L}\p{N}]+/u', ' ', $value);

        return trim((string) preg_replace('/\s+/u', ' ', $value));
    }

    public function normalizeEn(string $value): string
    {
        $value = strtolower(trim($value));
        if ($value === '') {
            return '';
        }

        $value = strtr($value, [
            '&' => ' and ',
            '/' => ' ',
            '–' => ' ',
            '—' => ' ',
        ]);
        $value = (string) preg_replace('/[^a-z0-9]+/', ' ', $value);

        return trim((string) preg_replace('/\s+/', ' ', $value));
    }

    public function normalizeSlug(string $slug, string $fallbackEn = ''): string
    {
        $slug = trim($slug);
        if ($slug !== '') {
            return Str::slug($slug);
        }

        return $fallbackEn !== '' ? Str::slug($fallbackEn) : '';
    }

    /**
     * @param  list<string>  $stop
     */
    private function tokenScore(string $left, string $right, array $stop): int
    {
        if ($left === '' || $right === '') {
            return 0;
        }

        if ($left === $right) {
            return 96;
        }

        $leftTokens = $this->tokens($left, $stop);
        $rightTokens = $this->tokens($right, $stop);

        if ($leftTokens === [] || $rightTokens === []) {
            return 0;
        }

        if ($this->allMatched($leftTokens, $rightTokens) || $this->allMatched($rightTokens, $leftTokens)) {
            $short = min(count($leftTokens), count($rightTokens));
            $long = max(count($leftTokens), count($rightTokens));

            return (int) round(62 + (28 * ($short / $long)));
        }

        $matched = 0;
        $used = [];
        foreach ($leftTokens as $token) {
            foreach ($rightTokens as $index => $candidate) {
                if (isset($used[$index])) {
                    continue;
                }
                if ($this->tokensMatch($token, $candidate)) {
                    $matched++;
                    $used[$index] = true;
                    break;
                }
            }
        }

        $union = count($leftTokens) + count($rightTokens) - $matched;
        if ($union === 0 || $matched === 0) {
            return 0;
        }

        return (int) round(85 * ($matched / $union));
    }

    /**
     * @param  list<string>  $stop
     * @return list<string>
     */
    private function tokens(string $value, array $stop): array
    {
        $parts = preg_split('/\s+/u', $value) ?: [];
        $out = [];

        foreach ($parts as $part) {
            $part = $this->stripArabicArticle($part);
            if ($part === '' || in_array($part, $stop, true) || mb_strlen($part) < 2) {
                continue;
            }
            $out[] = $part;
        }

        return array_values(array_unique($out));
    }

    /**
     * @param  list<string>  $needles
     * @param  list<string>  $haystack
     */
    private function allMatched(array $needles, array $haystack): bool
    {
        foreach ($needles as $needle) {
            $found = false;
            foreach ($haystack as $candidate) {
                if ($this->tokensMatch($needle, $candidate)) {
                    $found = true;
                    break;
                }
            }
            if (! $found) {
                return false;
            }
        }

        return $needles !== [];
    }

    private function tokensMatch(string $left, string $right): bool
    {
        if ($left === $right) {
            return true;
        }

        $short = mb_strlen($left) <= mb_strlen($right) ? $left : $right;
        $long = $short === $left ? $right : $left;

        return mb_strlen($short) >= 4 && str_starts_with($long, $short);
    }

    private function stripArabicArticle(string $token): string
    {
        if (str_starts_with($token, 'ال') && mb_strlen($token) > 3) {
            return mb_substr($token, 2);
        }

        return $token;
    }
}
