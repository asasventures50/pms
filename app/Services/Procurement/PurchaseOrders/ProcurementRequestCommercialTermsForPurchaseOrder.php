<?php

namespace App\Services\Procurement\PurchaseOrders;

use App\Models\Procurement\ProcurementRequests\ProcurementRequest;
use App\Models\Procurement\ProcurementRequests\ProcurementRequestPaymentTerm;
use App\Models\Procurement\ProcurementRequests\ProcurementRequestRetention;

class ProcurementRequestCommercialTermsForPurchaseOrder
{
    /**
     * @return array{
     *     payment_terms: string,
     *     retentions: list<array{retention_percent: float|string|null, release_period: string|null}>,
     *     primary_insurance_applicable: bool|null,
     *     primary_insurance_requirements: string|null,
     *     final_insurance_applicable: bool|null,
     *     final_insurance_requirements: string|null,
     *     has_retention: bool,
     *     has_insurance: bool,
     * }
     */
    public function snapshot(ProcurementRequest $procurementRequest): array
    {
        $procurementRequest->loadMissing(['paymentTerms', 'retentions']);

        $retentions = $procurementRequest->retentions
            ->sortBy(['sort_order', 'id'])
            ->values()
            ->map(fn (ProcurementRequestRetention $row) => [
                'retention_percent' => $row->retention_percent,
                'release_period' => $row->release_period,
            ])
            ->all();

        $primaryRequirements = trim((string) ($procurementRequest->primary_insurance_requirements ?? ''));
        $finalRequirements = trim((string) ($procurementRequest->final_insurance_requirements ?? ''));

        $hasInsurance = $procurementRequest->primary_insurance_applicable !== null
            || $procurementRequest->final_insurance_applicable !== null
            || $primaryRequirements !== ''
            || $finalRequirements !== '';

        return [
            'payment_terms' => $this->formatPaymentTerms($procurementRequest),
            'retentions' => $retentions,
            'primary_insurance_applicable' => $procurementRequest->primary_insurance_applicable,
            'primary_insurance_requirements' => $primaryRequirements !== '' ? $primaryRequirements : null,
            'final_insurance_applicable' => $procurementRequest->final_insurance_applicable,
            'final_insurance_requirements' => $finalRequirements !== '' ? $finalRequirements : null,
            'has_retention' => $this->hasRetentionRows($retentions),
            'has_insurance' => $hasInsurance,
        ];
    }

    public function formatPaymentTerms(ProcurementRequest $procurementRequest): string
    {
        $procurementRequest->loadMissing('paymentTerms');

        $lines = $procurementRequest->paymentTerms
            ->sortBy(['sort_order', 'id'])
            ->values()
            ->map(fn (ProcurementRequestPaymentTerm $row) => $this->formatPaymentTermLine($row))
            ->filter(static fn (string $line) => $line !== '')
            ->all();

        return implode("\n", $lines);
    }

    private function formatPaymentTermLine(ProcurementRequestPaymentTerm $row): string
    {
        $parts = [];

        $milestone = trim((string) ($row->milestone ?? ''));
        if ($milestone !== '') {
            $parts[] = $milestone;
        }

        $amount = trim((string) ($row->amount ?? ''));
        if ($amount !== '') {
            $parts[] = 'Amount: '.$amount;
        }

        if ($row->percentage !== null && $row->percentage !== '') {
            $parts[] = rtrim(rtrim(number_format((float) $row->percentage, 2), '0'), '.').'%';
        }

        $dueUpon = trim((string) ($row->due_upon ?? ''));
        if ($dueUpon !== '') {
            $parts[] = 'Due upon: '.$dueUpon;
        }

        return implode(' — ', $parts);
    }

    /**
     * @param  list<array{retention_percent?: mixed, release_period?: mixed}>  $rows
     */
    public function hasRetentionRows(array $rows): bool
    {
        foreach ($rows as $row) {
            $percent = $row['retention_percent'] ?? null;
            $period = trim((string) ($row['release_period'] ?? ''));

            if (($percent !== null && $percent !== '') || $period !== '') {
                return true;
            }
        }

        return false;
    }

    /**
     * @param  array<string, mixed>  $validated
     */
    public static function normalizeHeader(array &$validated): void
    {
        $validated['show_retention'] = filter_var(
            $validated['show_retention'] ?? true,
            FILTER_VALIDATE_BOOLEAN,
            FILTER_NULL_ON_FAILURE
        ) ?? true;

        $validated['show_insurance'] = filter_var(
            $validated['show_insurance'] ?? true,
            FILTER_VALIDATE_BOOLEAN,
            FILTER_NULL_ON_FAILURE
        ) ?? true;

        foreach (['primary_insurance_applicable', 'final_insurance_applicable'] as $field) {
            if (! array_key_exists($field, $validated)) {
                continue;
            }

            $raw = $validated[$field];
            if ($raw === null || $raw === '') {
                $validated[$field] = null;
                continue;
            }

            $validated[$field] = filter_var($raw, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
        }

        $validated['retentions'] = self::normalizeRetentions($validated['retentions'] ?? []);
    }

    /**
     * @param  mixed  $raw
     * @return list<array{retention_percent: float|null, release_period: string|null}>
     */
    public static function normalizeRetentions(mixed $raw): array
    {
        if (! is_array($raw)) {
            return [];
        }

        $normalized = [];

        foreach ($raw as $row) {
            if (! is_array($row)) {
                continue;
            }

            $percentRaw = $row['retention_percent'] ?? null;
            $percent = ($percentRaw === null || $percentRaw === '')
                ? null
                : (float) $percentRaw;
            $period = trim((string) ($row['release_period'] ?? ''));
            $period = $period !== '' ? $period : null;

            if ($percent === null && $period === null) {
                continue;
            }

            $normalized[] = [
                'retention_percent' => $percent,
                'release_period' => $period,
            ];
        }

        return $normalized;
    }
}
