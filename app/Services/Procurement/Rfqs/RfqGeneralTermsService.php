<?php

namespace App\Services\Procurement\Rfqs;

use App\Enums\Procurement\Rfqs\RfqTermsLocale;
use App\Models\Procurement\ProcurementRequests\ProcurementRequestItem;
use App\Models\Procurement\PurchaseOrders\PurchaseOrder;
use App\Models\Procurement\Rfqs\RfqGeneralTerm;
use App\Services\Procurement\PurchaseOrders\PurchaseOrderProcurementRequestContext;
use App\Support\Procurement\ProcurementScopeType;
use App\Support\Procurement\RfqTerms;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class RfqGeneralTermsService
{
    /** JSON key for company-wide terms on the RFQ form. */
    public const GLOBAL_SCOPE_KEY = 'global';
    private const TERM_ENTRY_KEYS = ['key_ar', 'key_en', 'value_ar', 'value_en'];

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
        $customEntries = $this->normalizeTermEntries($custom);

        if ($customEntries !== []) {
            return [
                'general' => $this->normalizeTexts($general),
                'custom' => $customEntries,
            ];
        }

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
     * @return list<array{key_ar: string, key_en: string, value_ar: string, value_en: string}>
     */
    public function normalizeTermEntries(mixed $raw): array
    {
        if (! is_array($raw)) {
            return [];
        }

        $entries = [];
        foreach ($raw as $value) {
            $normalized = $this->normalizeSingleTermEntry($value);
            if ($normalized !== null) {
                $entries[] = $normalized;
            }
        }

        return array_values($entries);
    }

    /**
     * @return list<array{key_ar: string, key_en: string, value_ar: string, value_en: string}>
     */
    public function customTermEntriesFromStored(mixed $stored): array
    {
        if (! is_array($stored)) {
            return [];
        }

        if (array_key_exists('custom', $stored)) {
            $entries = $this->normalizeTermEntries($stored['custom']);
            if ($entries !== []) {
                return $entries;
            }

            return $this->normalizeTermEntries($this->normalizeTexts($stored['custom']));
        }

        return $this->normalizeTermEntries($stored);
    }

    /**
     * General terms from the live library plus PO-specific custom terms from storage.
     *
     * @return list<string>
     */
    public function resolveLiveTermsForPurchaseOrder(PurchaseOrder $purchaseOrder): array
    {
        $locale = $this->normalizeLocale($purchaseOrder->terms_locale);
        $scopeTypes = $this->scopeTypesForPurchaseOrder($purchaseOrder);
        $general = $this->activeTextsForScopeTypes($scopeTypes, $locale);
        $custom = $this->resolveStoredCustomTermsForLocale($purchaseOrder->terms, $locale);

        return array_values(array_merge($general, $custom));
    }

    /**
     * Scope types for PO general terms: linked P.R. line scope types plus order-term dates.
     *
     * @return list<string>
     */
    public function scopeTypesForPurchaseOrder(PurchaseOrder $purchaseOrder): array
    {
        $prContext = PurchaseOrderProcurementRequestContext::resolve($purchaseOrder);

        return $this->mergePurchaseOrderScopeTypes(
            PurchaseOrderProcurementRequestContext::scopeTypeKeys(
                collect($prContext['pr_items_by_line'])->values()
            ),
            $this->scopeTypesFromOrderTermDates(
                $purchaseOrder->handover_at,
                $purchaseOrder->dismantling_at,
            ),
        );
    }

    /**
     * @param  list<string>  $poLineNumbers
     * @return list<string>
     */
    public function scopeTypesFromLinkedProcurementRequest(
        ?int $procurementRequestId,
        array $poLineNumbers,
        mixed $handoverAt = null,
        mixed $dismantlingAt = null,
    ): array {
        $fromPr = [];

        if ($procurementRequestId !== null && $poLineNumbers !== []) {
            $items = ProcurementRequestItem::query()
                ->where('procurement_request_id', $procurementRequestId)
                ->whereIn('line_number', $poLineNumbers)
                ->get(['scope_type']);

            $fromPr = PurchaseOrderProcurementRequestContext::scopeTypeKeys($items);
        }

        return $this->mergePurchaseOrderScopeTypes(
            $fromPr,
            $this->scopeTypesFromOrderTermDates($handoverAt, $dismantlingAt),
        );
    }

    /**
     * @param  list<string>  $prScopeTypes
     * @param  list<string>  $dateScopeTypes
     * @return list<string>
     */
    public function mergePurchaseOrderScopeTypes(array $prScopeTypes, array $dateScopeTypes): array
    {
        return array_values(array_unique(array_merge(
            $this->orderScopeTypes($prScopeTypes),
            $dateScopeTypes,
        )));
    }

    /**
     * @return list<string>
     */
    public function scopeTypesFromOrderTermDates(mixed $handoverAt, mixed $dismantlingAt): array
    {
        $types = [];

        if (! empty($handoverAt)) {
            $types[] = 'Maintenance';
        }

        if (! empty($dismantlingAt)) {
            $types[] = 'Dismantling';
        }

        return $types;
    }

    /**
     * @return list<string>
     */
    public function resolveStoredTermsForLocale(mixed $stored, ?string $locale = null): array
    {
        $parsed = $this->parseStoredTerms($stored);
        $custom = $this->resolveStoredCustomTermsForLocale($stored, $locale);

        return array_values(array_merge($parsed['general'], $custom));
    }

    /**
     * @return list<string>
     */
    public function resolveStoredCustomTermsForLocale(mixed $stored, ?string $locale = null): array
    {
        $locale = $this->normalizeLocale($locale);
        $entryValueKey = $locale === RfqTermsLocale::Ar->value ? 'value_ar' : 'value_en';
        $entryFallbackValueKey = $locale === RfqTermsLocale::Ar->value ? 'value_en' : 'value_ar';
        $entryKeyKey = $locale === RfqTermsLocale::Ar->value ? 'key_ar' : 'key_en';
        $entryFallbackKeyKey = $locale === RfqTermsLocale::Ar->value ? 'key_en' : 'key_ar';

        $entries = $this->customTermEntriesFromStored($stored);
        if ($entries === []) {
            return $this->parseStoredTerms($stored)['custom'];
        }

        $resolvedCustom = [];
        foreach ($entries as $entry) {
            $key = trim($entry[$entryKeyKey] ?: $entry[$entryFallbackKeyKey]);
            $value = trim($entry[$entryValueKey] ?: $entry[$entryFallbackValueKey]);

            if ($key !== '' && $value !== '') {
                $resolvedCustom[] = $key.': '.$value;

                continue;
            }

            if ($value !== '') {
                $resolvedCustom[] = $value;

                continue;
            }

            if ($key !== '') {
                $resolvedCustom[] = $key;
            }
        }

        return array_values($resolvedCustom);
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
            } elseif (is_array($value) && $this->isTermEntryArray($value)) {
                $text = trim((string) ($value['value_en'] ?? $value['value_ar'] ?? ''));
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
    public function nextSortOrder(?array $scopeTypes): float
    {
        $query = RfqGeneralTerm::query();

        $normalized = ProcurementScopeType::selectedValues($scopeTypes);

        if ($normalized === []) {
            $query->global();
        } else {
            $query->matchingAnyScopeType($normalized);
        }

        $max = $query->max('sort_order');

        return round(((float) ($max ?? 0)) + 1, 2);
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

    public function printScopeFilterLabel(?string $scopeFilter): string
    {
        if ($scopeFilter === null || $scopeFilter === '') {
            return 'All scopes';
        }

        if ($scopeFilter === self::GLOBAL_SCOPE_KEY) {
            return 'General (all RFQs) only';
        }

        return $scopeFilter;
    }

    /**
     * @param  Collection<int, RfqGeneralTerm>  $terms
     * @return list<array{label: string, terms: Collection<int, RfqGeneralTerm>}>
     */
    public function sectionsForPrint(Collection $terms, ?string $scopeFilter): array
    {
        if ($scopeFilter !== null && $scopeFilter !== '') {
            return [
                [
                    'label' => $this->printScopeFilterLabel($scopeFilter),
                    'terms' => $terms->values(),
                ],
            ];
        }

        $sections = [];

        $global = $terms->filter(fn (RfqGeneralTerm $term) => $term->isGlobal())->values();
        if ($global->isNotEmpty()) {
            $sections[] = [
                'label' => 'General (all RFQs)',
                'terms' => $global,
            ];
        }

        foreach (ProcurementScopeType::options() as $scopeType) {
            $scoped = $terms
                ->filter(fn (RfqGeneralTerm $term) => $term->appliesToScopeType($scopeType))
                ->values();

            if ($scoped->isNotEmpty()) {
                $sections[] = [
                    'label' => $scopeType,
                    'terms' => $scoped,
                ];
            }
        }

        return $sections;
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

    private function isTermEntryArray(array $value): bool
    {
        foreach (self::TERM_ENTRY_KEYS as $key) {
            if (array_key_exists($key, $value)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return array{key_ar: string, key_en: string, value_ar: string, value_en: string}|null
     */
    /**
     * @return array{key: string, value: string}
     */
    public function splitKeyValueText(?string $text): array
    {
        $raw = trim((string) ($text ?? ''));
        if ($raw === '') {
            return ['key' => '', 'value' => ''];
        }

        $parts = explode(':', $raw, 2);
        if (count($parts) < 2) {
            return ['key' => '', 'value' => $raw];
        }

        return [
            'key' => trim($parts[0]),
            'value' => trim($parts[1]),
        ];
    }

    /**
     * Rows for Additional terms form (key + value) with legacy support.
     *
     * @return list<array{key: string, value: string}>
     */
    public function customTermRowsForForm(mixed $stored, ?string $locale = null): array
    {
        $locale = $this->normalizeLocale($locale);
        $keyField = $locale === RfqTermsLocale::Ar->value ? 'key_ar' : 'key_en';
        $valueField = $locale === RfqTermsLocale::Ar->value ? 'value_ar' : 'value_en';
        $fallbackKeyField = $locale === RfqTermsLocale::Ar->value ? 'key_en' : 'key_ar';
        $fallbackValueField = $locale === RfqTermsLocale::Ar->value ? 'value_en' : 'value_ar';

        $entries = $this->customTermEntriesFromStored($stored);
        if ($entries !== []) {
            $rows = [];
            foreach ($entries as $entry) {
                $value = trim($entry[$valueField] ?: $entry[$fallbackValueField]);
                if ($value === '') {
                    continue;
                }

                $rows[] = [
                    'key' => trim($entry[$keyField] ?: $entry[$fallbackKeyField]),
                    'value' => $value,
                ];
            }

            return $rows;
        }

        $parsed = $this->parseStoredTerms($stored);
        $rows = [];
        foreach ($parsed['custom'] as $text) {
            $row = $this->splitKeyValueText($text);
            if ($row['value'] !== '') {
                $rows[] = $row;
            }
        }

        return $rows;
    }

    /**
     * @param  mixed  $raw
     * @return list<array{key_ar: string, key_en: string, value_ar: string, value_en: string}>
     */
    public function normalizeCustomTermsInput(mixed $raw, ?string $locale = null): array
    {
        if (! is_array($raw)) {
            return [];
        }

        $locale = $this->normalizeLocale($locale);
        $entries = [];

        foreach ($raw as $value) {
            if (is_array($value) && (array_key_exists('key', $value) || array_key_exists('value', $value))) {
                $key = trim((string) ($value['key'] ?? ''));
                $val = trim((string) ($value['value'] ?? ''));
                if ($val === '') {
                    continue;
                }

                $entry = [
                    'key_ar' => '',
                    'key_en' => '',
                    'value_ar' => '',
                    'value_en' => '',
                ];

                if ($locale === RfqTermsLocale::Ar->value) {
                    $entry['key_ar'] = $key;
                    $entry['value_ar'] = $val;
                } else {
                    $entry['key_en'] = $key;
                    $entry['value_en'] = $val;
                }

                $entries[] = $entry;

                continue;
            }

            $normalized = $this->normalizeSingleTermEntry($value);
            if ($normalized !== null) {
                $entries[] = $normalized;
            }
        }

        return array_values($entries);
    }

    private function normalizeSingleTermEntry(mixed $value): ?array
    {
        if (is_array($value) && $this->isTermEntryArray($value)) {
            $entry = [
                'key_ar' => trim((string) ($value['key_ar'] ?? '')),
                'key_en' => trim((string) ($value['key_en'] ?? '')),
                'value_ar' => trim((string) ($value['value_ar'] ?? '')),
                'value_en' => trim((string) ($value['value_en'] ?? '')),
            ];

            if ($entry['value_ar'] === '' && $entry['value_en'] === '') {
                return null;
            }

            return $entry;
        }

        $text = trim((string) $value);
        if ($text === '') {
            return null;
        }

        return [
            'key_ar' => '',
            'key_en' => '',
            'value_ar' => $text,
            'value_en' => $text,
        ];
    }
}
