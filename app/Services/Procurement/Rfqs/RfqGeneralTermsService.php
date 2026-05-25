<?php

namespace App\Services\Procurement\Rfqs;

use App\Enums\Procurement\Rfqs\RfqTermsLocale;
use App\Models\Procurement\ProcurementRequests\ProcurementRequestItem;
use App\Models\Procurement\Rfqs\RfqGeneralTerm;
use App\Support\Procurement\ProcurementScopeType;
use App\Support\Procurement\RfqTerms;
use Illuminate\Database\Eloquent\Builder;

class RfqGeneralTermsService
{
    /** JSON key for company-wide terms on the RFQ form. */
    public const GLOBAL_SCOPE_KEY = 'global';

    /**
     * Map passed to the RFQ form: global terms + per-scope terms, each in ar/en.
     *
     * @return array<string, array{ar: list<string>, en: list<string>}>
     */
    public function termsMapForRfqForm(): array
    {
        $map = [
            self::GLOBAL_SCOPE_KEY => [
                'ar' => $this->activeGlobalTexts(RfqTermsLocale::Ar->value),
                'en' => $this->activeGlobalTexts(RfqTermsLocale::En->value),
            ],
        ];

        foreach (ProcurementScopeType::options() as $scopeType) {
            $map[$scopeType] = [
                'ar' => $this->activeTextsForScopeType($scopeType, RfqTermsLocale::Ar->value),
                'en' => $this->activeTextsForScopeType($scopeType, RfqTermsLocale::En->value),
            ];
        }

        return $map;
    }

    /**
     * Company-wide terms on every RFQ.
     *
     * @return list<string>
     */
    public function activeGlobalTexts(?string $locale = null): array
    {
        $locale = $this->normalizeLocale($locale);

        $terms = RfqGeneralTerm::query()
            ->global()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get()
            ->map(fn (RfqGeneralTerm $term) => $this->resolveBody($term, $locale))
            ->all();

        $normalized = $this->normalizeTexts($terms);

        if ($normalized !== []) {
            return $normalized;
        }

        return RfqTerms::legacyDefaults($locale);
    }

    /**
     * @return list<string>
     */
    public function activeTextsForScopeType(string $scopeType, ?string $locale = null): array
    {
        if (! in_array($scopeType, ProcurementScopeType::values(), true)) {
            return [];
        }

        $locale = $this->normalizeLocale($locale);

        $terms = RfqGeneralTerm::query()
            ->matchingScopeType($scopeType)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get()
            ->map(fn (RfqGeneralTerm $term) => $this->resolveBody($term, $locale))
            ->all();

        return $this->normalizeTexts($terms);
    }

    /**
     * Global terms first, then terms for each scope type on the RFQ lines.
     *
     * @param  list<string>  $scopeTypes
     * @return list<string>
     */
    public function activeTextsForScopeTypes(array $scopeTypes, ?string $locale = null): array
    {
        $locale = $this->normalizeLocale($locale);
        $merged = [];
        $seen = [];

        foreach ($this->activeGlobalTexts($locale) as $text) {
            $seen[$text] = true;
            $merged[] = $text;
        }

        foreach ($this->orderScopeTypes($scopeTypes) as $scopeType) {
            foreach ($this->activeTextsForScopeType($scopeType, $locale) as $text) {
                if (isset($seen[$text])) {
                    continue;
                }
                $seen[$text] = true;
                $merged[] = $text;
            }
        }

        return $merged;
    }

    public function resolveBody(RfqGeneralTerm $term, ?string $locale = null): string
    {
        $locale = $this->normalizeLocale($locale);
        $primary = trim((string) ($locale === RfqTermsLocale::Ar->value ? $term->body_ar : $term->body_en));
        $fallback = trim((string) ($locale === RfqTermsLocale::Ar->value ? $term->body_en : $term->body_ar));

        return $primary !== '' ? $primary : $fallback;
    }

    /**
     * @param  list<array<string, mixed>>  $normalizedItems
     * @return list<string>
     */
    public function scopeTypesFromNormalizedItems(array $normalizedItems): array
    {
        $prItemIds = collect($normalizedItems)
            ->pluck('procurement_request_item_id')
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();

        if ($prItemIds->isEmpty()) {
            return [];
        }

        $scopeTypes = [];

        $prItems = ProcurementRequestItem::query()
            ->whereIn('id', $prItemIds)
            ->get(['id', 'scope_type']);

        foreach ($prItems as $prItem) {
            foreach (ProcurementScopeType::selectedValues($prItem->scope_type) as $scopeType) {
                $scopeTypes[$scopeType] = true;
            }
        }

        return $this->orderScopeTypes(array_keys($scopeTypes));
    }

    /**
     * @param  list<string>  $general
     * @param  list<string>  $custom
     * @return array{general: list<string>, custom: list<string>}
     */
    public function buildTermsPayload(array $general, array $custom): array
    {
        return [
            'general' => $this->normalizeTexts($general),
            'custom' => $this->normalizeTexts($custom),
        ];
    }

    /**
     * @return array{general: list<string>, custom: list<string>, all: list<string>}
     */
    public function parseStoredTerms(mixed $stored): array
    {
        if (! is_array($stored)) {
            return ['general' => [], 'custom' => [], 'all' => []];
        }

        if (array_key_exists('general', $stored) || array_key_exists('custom', $stored)) {
            $general = $this->normalizeTexts($stored['general'] ?? []);
            $custom = $this->normalizeTexts($stored['custom'] ?? []);

            return [
                'general' => $general,
                'custom' => $custom,
                'all' => array_values(array_merge($general, $custom)),
            ];
        }

        $legacy = $this->normalizeTexts($stored);

        return [
            'general' => $legacy,
            'custom' => [],
            'all' => $legacy,
        ];
    }

    /**
     * @return list<string>
     */
    public function activeTexts(?string $locale = null): array
    {
        return $this->activeGlobalTexts($locale);
    }

    /**
     * @return list<string>
     */
    public function normalizeTexts(mixed $raw): array
    {
        if (! is_array($raw)) {
            return [];
        }

        $normalized = [];

        foreach ($raw as $value) {
            if (is_array($value) && isset($value['body'])) {
                $text = trim((string) $value['body']);
            } else {
                $text = trim((string) $value);
            }

            if ($text !== '') {
                $normalized[] = $text;
            }
        }

        return array_values($normalized);
    }

    /**
     * @return list<string>
     */
    public function normalizeLineTerms(mixed $raw): array
    {
        if (is_string($raw)) {
            $lines = preg_split('/\R+/', $raw) ?: [];

            return $this->normalizeTexts($lines);
        }

        return $this->normalizeTexts($raw);
    }

    /**
     * @param  list<string>|null  $scopeTypes
     */
    public function nextSortOrder(?array $scopeTypes): int
    {
        $query = RfqGeneralTerm::query();

        $normalized = ProcurementScopeType::selectedValues($scopeTypes);

        if ($normalized === []) {
            $query->global();
        } else {
            $query->matchingAnyScopeType($normalized);
        }

        $max = $query->max('sort_order');

        return ((int) $max) + 1;
    }

    /**
     * @param  mixed  $scopeTypes
     */
    public static function scopeTypesLabel(mixed $scopeTypes): string
    {
        $values = ProcurementScopeType::selectedValues($scopeTypes);

        return $values === []
            ? 'General (all RFQs)'
            : implode(', ', $values);
    }

    /**
     * @deprecated Use scopeTypesLabel()
     */
    public static function scopeTypeLabel(?string $scopeType): string
    {
        return self::scopeTypesLabel($scopeType === null || $scopeType === '' ? null : [$scopeType]);
    }

    /**
     * @param  Builder<RfqGeneralTerm>  $query
     */
    public function applyScopeTypeFilter(Builder $query, string $filter): void
    {
        if ($filter === self::GLOBAL_SCOPE_KEY) {
            $query->global();

            return;
        }

        if (in_array($filter, ProcurementScopeType::values(), true)) {
            $query->matchingScopeType($filter);
        }
    }

    /**
     * @param  list<string>  $scopeTypes
     * @return list<string>
     */
    private function orderScopeTypes(array $scopeTypes): array
    {
        $allowed = array_flip(ProcurementScopeType::options());
        $selected = [];

        foreach ($scopeTypes as $scopeType) {
            $label = trim((string) $scopeType);
            if ($label !== '' && isset($allowed[$label])) {
                $selected[$label] = true;
            }
        }

        $ordered = [];
        foreach (ProcurementScopeType::options() as $option) {
            if (isset($selected[$option])) {
                $ordered[] = $option;
            }
        }

        return $ordered;
    }

    private function normalizeLocale(?string $locale): string
    {
        $locale = $locale ?? RfqTermsLocale::default()->value;

        return in_array($locale, RfqTermsLocale::values(), true)
            ? $locale
            : RfqTermsLocale::default()->value;
    }
}
