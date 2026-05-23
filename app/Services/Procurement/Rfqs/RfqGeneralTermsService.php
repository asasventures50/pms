<?php

namespace App\Services\Procurement\Rfqs;

use App\Models\Procurement\ProcurementRequests\ProcurementRequestItem;
use App\Models\Procurement\Rfqs\RfqGeneralTerm;
use App\Support\Procurement\ProcurementScopeType;
use App\Support\Procurement\RfqTerms;

class RfqGeneralTermsService
{
    /** JSON key for company-wide terms on the RFQ form. */
    public const GLOBAL_SCOPE_KEY = 'global';

    /**
     * Map passed to the RFQ form: global terms + per-scope terms.
     *
     * @return array<string, list<string>>
     */
    public function termsMapForRfqForm(): array
    {
        $map = [self::GLOBAL_SCOPE_KEY => $this->activeGlobalTexts()];

        foreach (ProcurementScopeType::options() as $scopeType) {
            $map[$scopeType] = $this->activeTextsForScopeType($scopeType);
        }

        return $map;
    }

    /**
     * Company-wide terms on every RFQ.
     *
     * @return list<string>
     */
    public function activeGlobalTexts(): array
    {
        $terms = RfqGeneralTerm::query()
            ->whereNull('scope_type')
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->pluck('body')
            ->all();

        $normalized = $this->normalizeTexts($terms);

        if ($normalized !== []) {
            return $normalized;
        }

        return RfqTerms::legacyDefaults();
    }

    /**
     * @return list<string>
     */
    public function activeTextsForScopeType(string $scopeType): array
    {
        if (! in_array($scopeType, ProcurementScopeType::values(), true)) {
            return [];
        }

        $terms = RfqGeneralTerm::query()
            ->where('scope_type', $scopeType)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->pluck('body')
            ->all();

        return $this->normalizeTexts($terms);
    }

    /**
     * Global terms first, then terms for each scope type on the RFQ lines.
     *
     * @param  list<string>  $scopeTypes
     * @return list<string>
     */
    public function activeTextsForScopeTypes(array $scopeTypes): array
    {
        $merged = [];
        $seen = [];

        foreach ($this->activeGlobalTexts() as $text) {
            $seen[$text] = true;
            $merged[] = $text;
        }

        foreach ($this->orderScopeTypes($scopeTypes) as $scopeType) {
            foreach ($this->activeTextsForScopeType($scopeType) as $text) {
                if (isset($seen[$text])) {
                    continue;
                }
                $seen[$text] = true;
                $merged[] = $text;
            }
        }

        return $merged;
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
    public function activeTexts(): array
    {
        return $this->activeGlobalTexts();
    }

    /**
     * @param  mixed  $raw
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
     * @param  mixed  $raw
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

    public function nextSortOrder(?string $scopeType): int
    {
        $query = RfqGeneralTerm::query();

        if ($scopeType === null || $scopeType === '') {
            $query->whereNull('scope_type');
        } else {
            $query->where('scope_type', $scopeType);
        }

        $max = $query->max('sort_order');

        return ((int) $max) + 1;
    }

    public static function scopeTypeLabel(?string $scopeType): string
    {
        return $scopeType === null || $scopeType === ''
            ? 'General (all RFQs)'
            : $scopeType;
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
}
