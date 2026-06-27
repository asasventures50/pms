<?php

namespace App\Services\Procurement\ScheduleOfWorks;

class ScheduleOfWorkPrSectionsNormalizer
{
    /**
     * @param  mixed  $input
     * @return array<string, mixed>|null
     */
    public static function normalize(mixed $input): ?array
    {
        if (! is_array($input)) {
            return null;
        }

        $sections = [];

        $prInfo = self::normalizePrInfo($input['pr_info'] ?? []);
        if ($prInfo !== []) {
            $sections['pr_info'] = $prInfo;
        }

        $delivery = self::normalizeDelivery($input['delivery'] ?? []);
        if ($delivery !== []) {
            $sections['delivery'] = $delivery;
        }

        $documents = self::normalizeDocuments($input['supporting_documents'] ?? []);
        if ($documents !== []) {
            $sections['supporting_documents'] = $documents;
        }

        $paymentTerms = self::normalizePaymentTerms($input['payment_terms'] ?? []);
        if ($paymentTerms !== []) {
            $sections['payment_terms'] = $paymentTerms;
        }

        $retentions = self::normalizeRetentions($input['retentions'] ?? []);
        if ($retentions !== []) {
            $sections['retentions'] = $retentions;
        }

        $maintenance = self::normalizeMaintenance($input['maintenance'] ?? []);
        if ($maintenance !== []) {
            $sections['maintenance'] = $maintenance;
        }

        $timeline = self::normalizeTimeline($input['timeline'] ?? []);
        if ($timeline !== []) {
            $sections['timeline'] = $timeline;
        }

        $compliance = self::normalizeCompliance($input['compliance'] ?? []);
        if ($compliance !== []) {
            $sections['compliance'] = $compliance;
        }

        return $sections === [] ? null : $sections;
    }

    /**
     * @return array<string, mixed>
     */
    public static function formDefaults(mixed $stored): array
    {
        $defaults = self::emptyFormDefaults();
        if (! is_array($stored)) {
            return $defaults;
        }

        $normalized = self::normalize($stored) ?? [];

        foreach (['pr_info', 'delivery', 'maintenance', 'compliance'] as $key) {
            if (! empty($normalized[$key]) && is_array($normalized[$key])) {
                $defaults[$key] = array_merge($defaults[$key], $normalized[$key]);
            }
        }

        foreach (['supporting_documents', 'payment_terms', 'retentions'] as $key) {
            if (! empty($normalized[$key]) && is_array($normalized[$key])) {
                $defaults[$key] = $normalized[$key];
            }
        }

        if (! empty($normalized['timeline']) && is_array($normalized['timeline'])) {
            $byActivity = collect($normalized['timeline'])->keyBy('activity');
            foreach ($defaults['timeline'] as $index => $row) {
                $activity = $row['activity'] ?? '';
                if ($byActivity->has($activity)) {
                    $defaults['timeline'][$index]['duration_days'] = $byActivity->get($activity)['duration_days'] ?? '';
                }
            }
        }

        return $defaults;
    }

    /**
     * @return array<string, mixed>
     */
    public static function emptyFormDefaults(): array
    {
        return [
            'pr_info' => [
                'project' => '',
                'zone' => '',
                'category' => '',
                'subcategory' => '',
                'procurement_type' => '',
                'geographic_scope' => '',
                'vendor_type' => '',
                'samples_required' => '',
            ],
            'delivery' => [
                'lead_time_days' => '',
                'location' => '',
                'flexible_delivery_date' => '',
            ],
            'supporting_documents' => [
                ['document_type' => '', 'file_name' => '', 'file_description' => '', 'file_url' => ''],
            ],
            'payment_terms' => [
                ['milestone' => '', 'amount' => '', 'percentage' => '', 'due_upon' => ''],
            ],
            'retentions' => [
                ['retention_percent' => '', 'release_period' => ''],
            ],
            'maintenance' => [
                'after_sale_service_applicable' => '',
                'warranty_years' => '',
                'warranty_coverage' => '',
            ],
            'timeline' => collect(\App\Enums\Procurement\ProcurementRequests\ProcurementTimelineActivity::cases())
                ->map(static fn ($activity) => [
                    'activity' => $activity->value,
                    'label' => $activity->label(),
                    'duration_days' => '',
                ])
                ->all(),
            'compliance' => [
                'verification_required' => '',
                'prequalification_required' => '',
                'prequalification_level' => '',
                'nda_required' => '',
                'conflict_of_interest_required' => '',
                'commitment_compliance_required' => '',
            ],
        ];
    }

    /**
     * @param  mixed  $value
     * @return array<string, mixed>
     */
    private static function normalizePrInfo(mixed $value): array
    {
        if (! is_array($value)) {
            return [];
        }

        $samples = self::nullableBool($value['samples_required'] ?? null);
        $info = array_filter([
            'project' => self::nullableString($value['project'] ?? null),
            'zone' => self::nullableString($value['zone'] ?? null),
            'category' => self::nullableString($value['category'] ?? null),
            'subcategory' => self::nullableString($value['subcategory'] ?? null),
            'procurement_type' => self::nullableString($value['procurement_type'] ?? null),
            'geographic_scope' => self::nullableString($value['geographic_scope'] ?? null),
            'vendor_type' => self::nullableString($value['vendor_type'] ?? null),
        ]);

        if ($samples !== null) {
            $info['samples_required'] = $samples;
        }

        return $info;
    }

    /**
     * @param  mixed  $value
     * @return array<string, mixed>
     */
    private static function normalizeDelivery(mixed $value): array
    {
        if (! is_array($value)) {
            return [];
        }

        $flexible = self::nullableBool($value['flexible_delivery_date'] ?? null);
        $delivery = array_filter([
            'lead_time_days' => self::nullableString($value['lead_time_days'] ?? null),
            'location' => self::nullableString($value['location'] ?? null),
        ]);

        if ($flexible !== null) {
            $delivery['flexible_delivery_date'] = $flexible;
        }

        return $delivery;
    }

    /**
     * @param  mixed  $rows
     * @return list<array<string, mixed>>
     */
    private static function normalizeDocuments(mixed $rows): array
    {
        if (! is_array($rows)) {
            return [];
        }

        $documents = [];
        foreach ($rows as $row) {
            if (! is_array($row)) {
                continue;
            }

            $document = array_filter([
                'document_type' => self::nullableString($row['document_type'] ?? null),
                'file_name' => self::nullableString($row['file_name'] ?? null),
                'file_description' => self::nullableString($row['file_description'] ?? null),
                'file_url' => self::nullableString($row['file_url'] ?? null),
            ]);

            if ($document !== []) {
                $documents[] = $document;
            }
        }

        return $documents;
    }

    /**
     * @param  mixed  $rows
     * @return list<array<string, mixed>>
     */
    private static function normalizePaymentTerms(mixed $rows): array
    {
        if (! is_array($rows)) {
            return [];
        }

        $terms = [];
        foreach ($rows as $row) {
            if (! is_array($row)) {
                continue;
            }

            $term = array_filter([
                'milestone' => self::nullableString($row['milestone'] ?? null),
                'amount' => self::nullableString($row['amount'] ?? null),
                'percentage' => self::nullableString($row['percentage'] ?? null),
                'due_upon' => self::nullableString($row['due_upon'] ?? null),
            ]);

            if ($term !== []) {
                $terms[] = $term;
            }
        }

        return $terms;
    }

    /**
     * @param  mixed  $rows
     * @return list<array<string, mixed>>
     */
    private static function normalizeRetentions(mixed $rows): array
    {
        if (! is_array($rows)) {
            return [];
        }

        $retentions = [];
        foreach ($rows as $row) {
            if (! is_array($row)) {
                continue;
            }

            $retention = array_filter([
                'retention_percent' => self::nullableString($row['retention_percent'] ?? null),
                'release_period' => self::nullableString($row['release_period'] ?? null),
            ]);

            if ($retention !== []) {
                $retentions[] = $retention;
            }
        }

        return $retentions;
    }

    /**
     * @param  mixed  $value
     * @return array<string, mixed>
     */
    private static function normalizeMaintenance(mixed $value): array
    {
        if (! is_array($value)) {
            return [];
        }

        $afterSale = self::nullableBool($value['after_sale_service_applicable'] ?? null);
        $maintenance = array_filter([
            'warranty_years' => self::nullableString($value['warranty_years'] ?? null),
            'warranty_coverage' => self::nullableString($value['warranty_coverage'] ?? null),
        ]);

        if ($afterSale !== null) {
            $maintenance['after_sale_service_applicable'] = $afterSale;
        }

        return $maintenance;
    }

    /**
     * @param  mixed  $rows
     * @return list<array<string, mixed>>
     */
    private static function normalizeTimeline(mixed $rows): array
    {
        if (! is_array($rows)) {
            return [];
        }

        $timeline = [];
        foreach ($rows as $row) {
            if (! is_array($row)) {
                continue;
            }

            $duration = self::nullableString($row['duration_days'] ?? null);
            if ($duration === null) {
                continue;
            }

            $activity = self::nullableString($row['activity'] ?? null) ?? '';
            $label = self::nullableString($row['label'] ?? null) ?? $activity;

            $timeline[] = [
                'activity' => $activity,
                'label' => $label,
                'duration_days' => $duration,
            ];
        }

        return $timeline;
    }

    /**
     * @param  mixed  $value
     * @return array<string, mixed>
     */
    private static function normalizeCompliance(mixed $value): array
    {
        if (! is_array($value)) {
            return [];
        }

        $compliance = [];
        foreach ([
            'verification_required',
            'prequalification_required',
            'nda_required',
            'conflict_of_interest_required',
            'commitment_compliance_required',
        ] as $key) {
            $bool = self::nullableBool($value[$key] ?? null);
            if ($bool !== null) {
                $compliance[$key] = $bool;
            }
        }

        $level = self::nullableString($value['prequalification_level'] ?? null);
        if ($level !== null) {
            $compliance['prequalification_level'] = $level;
        }

        return $compliance;
    }

    private static function nullableString(mixed $value): ?string
    {
        $trimmed = trim((string) ($value ?? ''));

        return $trimmed !== '' ? $trimmed : null;
    }

    private static function nullableBool(mixed $value): ?bool
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_bool($value)) {
            return $value;
        }

        if ($value === 1 || $value === '1' || $value === 'true' || $value === 'yes') {
            return true;
        }

        if ($value === 0 || $value === '0' || $value === 'false' || $value === 'no') {
            return false;
        }

        return null;
    }
}
