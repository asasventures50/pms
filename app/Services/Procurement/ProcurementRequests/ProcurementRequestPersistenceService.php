<?php

namespace App\Services\Procurement\ProcurementRequests;

use App\Enums\Procurement\ProcurementRequests\GeographicScope;
use App\Enums\Procurement\ProcurementRequests\ProcurementApprovalRole;
use App\Enums\Procurement\ProcurementRequests\ProcurementTimelineActivity;
use App\Enums\Procurement\ProcurementRequests\ProcurementType;
use App\Enums\Procurement\ProcurementRequests\ProcurementVendorType;
use App\Models\Procurement\ProcurementRequests\ProcurementRequest;
use App\Models\Procurement\ProcurementRequests\ProcurementRequestApproval;
use App\Models\Procurement\ProcurementRequests\ProcurementRequestItem;
use App\Models\Procurement\ProcurementRequests\ProcurementRequestPaymentTerm;
use App\Models\Procurement\ProcurementRequests\ProcurementRequestRetention;
use App\Models\Procurement\ProcurementRequests\ProcurementRequestTimelineEntry;
use App\Support\Procurement\ProcurementCheckboxGroup;
use Illuminate\Support\Facades\DB;

class ProcurementRequestPersistenceService
{
    /**
     * @param  array<string, mixed>  $header
     * @param  list<array<string, mixed>>  $items
     * @param  list<array<string, mixed>>  $paymentTerms
     * @param  list<array<string, mixed>>  $retentions
     * @param  list<array<string, mixed>>  $timeline
     * @param  list<array<string, mixed>>  $approvals
     */
    public function create(
        array $header,
        array $items,
        array $paymentTerms = [],
        array $retentions = [],
        array $timeline = [],
        array $approvals = [],
    ): ProcurementRequest {
        return DB::transaction(function () use ($header, $items, $paymentTerms, $retentions, $timeline, $approvals) {
            $request = ProcurementRequest::query()->create($header);
            $this->syncItems($request, $items);
            $this->syncPaymentTerms($request, $paymentTerms);
            $this->syncRetentions($request, $retentions);
            $this->ensureTimeline($request);
            $this->syncTimeline($request, $timeline);
            $this->ensureApprovals($request);
            $this->syncApprovals($request, $approvals);

            return $request->load(['items', 'creator', 'paymentTerms', 'retentions', 'timelineEntries', 'approvals']);
        });
    }

    /**
     * @param  array<string, mixed>  $header
     * @param  list<array<string, mixed>>  $items
     * @param  list<array<string, mixed>>  $paymentTerms
     * @param  list<array<string, mixed>>  $retentions
     * @param  list<array<string, mixed>>  $timeline
     * @param  list<array<string, mixed>>  $approvals
     */
    public function update(
        ProcurementRequest $request,
        array $header,
        array $items,
        array $paymentTerms = [],
        array $retentions = [],
        array $timeline = [],
        array $approvals = [],
    ): ProcurementRequest {
        return DB::transaction(function () use ($request, $header, $items, $paymentTerms, $retentions, $timeline, $approvals) {
            $request->fill($header);
            $request->save();
            $this->syncItems($request, $items);
            $this->syncPaymentTerms($request, $paymentTerms);
            $this->syncRetentions($request, $retentions);
            $this->ensureTimeline($request);
            $this->syncTimeline($request, $timeline);
            $this->ensureApprovals($request);
            $this->syncApprovals($request, $approvals);

            return $request->load(['items', 'creator', 'paymentTerms', 'retentions', 'timelineEntries', 'approvals']);
        });
    }

    /**
     * @param  list<array<string, mixed>>  $items
     */
    private function syncItems(ProcurementRequest $request, array $items): void
    {
        $request->refresh();
        $keptIds = [];

        foreach (array_values($items) as $index => $row) {
            $quantity = max(0, (float) ($row['quantity'] ?? 0));
            $unitPrice = max(0, (float) ($row['unit_price'] ?? 0));
            $totalPrice = isset($row['total_price']) && $row['total_price'] !== ''
                ? max(0, (float) $row['total_price'])
                : round($quantity * $unitPrice, 4);

            $attributes = [
                'sort_order' => $index,
                'line_number' => ProcurementRequestLineNumberFormatter::format($request->request_number, $index),
                'item_name' => isset($row['item_name']) ? trim((string) $row['item_name']) : null,
                'description' => $row['description'] ?? null,
                'unit' => isset($row['unit']) ? trim((string) $row['unit']) : null,
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'total_price' => $totalPrice,
            ];

            $itemId = $row['id'] ?? null;
            $item = null;

            if ($itemId !== null && $itemId !== '') {
                $item = ProcurementRequestItem::query()
                    ->where('procurement_request_id', $request->id)
                    ->whereKey((int) $itemId)
                    ->first();
            }

            if ($item) {
                $item->fill($attributes);
                ProcurementRequestLegacyItemSync::applyToItem($request, $item);
                $item->save();
                $keptIds[] = $item->id;

                continue;
            }

            $item = ProcurementRequestItem::query()->create([
                'procurement_request_id' => $request->id,
                ...$attributes,
            ]);
            ProcurementRequestLegacyItemSync::applyToItem($request, $item);
            $item->save();

            $keptIds[] = $item->id;
        }

        if ($keptIds !== []) {
            $request->items()->whereNotIn('id', $keptIds)->delete();
        } else {
            $request->items()->delete();
        }
    }

    /**
     * @param  list<array<string, mixed>>  $rows
     */
    private function syncPaymentTerms(ProcurementRequest $request, array $rows): void
    {
        $normalized = self::normalizePaymentTerms($rows);
        $keptIds = [];

        foreach ($normalized as $index => $row) {
            $attributes = [
                'sort_order' => $index,
                'milestone' => $row['milestone'],
                'amount' => $row['amount'],
                'percentage' => $row['percentage'],
                'due_upon' => $row['due_upon'],
            ];

            $rowId = $row['id'] ?? null;
            $term = null;

            if ($rowId !== null && $rowId !== '') {
                $term = ProcurementRequestPaymentTerm::query()
                    ->where('procurement_request_id', $request->id)
                    ->whereKey((int) $rowId)
                    ->first();
            }

            if ($term) {
                $term->update($attributes);
                $keptIds[] = $term->id;

                continue;
            }

            $term = $request->paymentTerms()->create($attributes);
            $keptIds[] = $term->id;
        }

        if ($keptIds !== []) {
            $request->paymentTerms()->whereNotIn('id', $keptIds)->delete();
        } else {
            $request->paymentTerms()->delete();
        }
    }

    /**
     * @param  list<array<string, mixed>>  $rows
     */
    private function syncRetentions(ProcurementRequest $request, array $rows): void
    {
        $normalized = self::normalizeRetentions($rows);
        $keptIds = [];

        foreach ($normalized as $index => $row) {
            $attributes = [
                'sort_order' => $index,
                'retention_percent' => $row['retention_percent'],
                'release_period' => $row['release_period'],
            ];

            $rowId = $row['id'] ?? null;
            $retention = null;

            if ($rowId !== null && $rowId !== '') {
                $retention = ProcurementRequestRetention::query()
                    ->where('procurement_request_id', $request->id)
                    ->whereKey((int) $rowId)
                    ->first();
            }

            if ($retention) {
                $retention->update($attributes);
                $keptIds[] = $retention->id;

                continue;
            }

            $retention = $request->retentions()->create($attributes);
            $keptIds[] = $retention->id;
        }

        if ($keptIds !== []) {
            $request->retentions()->whereNotIn('id', $keptIds)->delete();
        } else {
            $request->retentions()->delete();
        }
    }

    private function ensureTimeline(ProcurementRequest $request): void
    {
        foreach (ProcurementTimelineActivity::cases() as $activity) {
            ProcurementRequestTimelineEntry::query()->firstOrCreate(
                [
                    'procurement_request_id' => $request->id,
                    'activity' => $activity->value,
                ],
                ['duration_days' => null]
            );
        }
    }

    /**
     * @param  list<array<string, mixed>>  $rows
     */
    private function syncTimeline(ProcurementRequest $request, array $rows): void
    {
        foreach ($rows as $row) {
            if (! is_array($row)) {
                continue;
            }

            $activity = trim((string) ($row['activity'] ?? ''));
            if ($activity === '' || ! in_array($activity, ProcurementTimelineActivity::values(), true)) {
                continue;
            }

            $duration = $row['duration_days'] ?? null;
            $durationDays = ($duration === null || $duration === '') ? null : max(0, (int) $duration);

            ProcurementRequestTimelineEntry::query()
                ->where('procurement_request_id', $request->id)
                ->where('activity', $activity)
                ->update(['duration_days' => $durationDays]);
        }
    }

    private function ensureApprovals(ProcurementRequest $request): void
    {
        foreach (ProcurementApprovalRole::cases() as $role) {
            ProcurementRequestApproval::query()->firstOrCreate(
                [
                    'procurement_request_id' => $request->id,
                    'role' => $role->value,
                ],
                [
                    'name' => null,
                    'signature' => null,
                    'signed_at' => null,
                ]
            );
        }
    }

    /**
     * @param  list<array<string, mixed>>  $rows
     */
    private function syncApprovals(ProcurementRequest $request, array $rows): void
    {
        foreach ($rows as $row) {
            if (! is_array($row)) {
                continue;
            }

            $role = trim((string) ($row['role'] ?? ''));
            if ($role === '' || ! in_array($role, ProcurementApprovalRole::values(), true)) {
                continue;
            }

            $signedAt = trim((string) ($row['signed_at'] ?? ''));

            ProcurementRequestApproval::query()
                ->where('procurement_request_id', $request->id)
                ->where('role', $role)
                ->update([
                    'name' => isset($row['name']) ? trim((string) $row['name']) : null,
                    'signature' => isset($row['signature']) ? trim((string) $row['signature']) : null,
                    'signed_at' => $signedAt !== '' ? $signedAt : null,
                ]);
        }
    }

    /**
     * @param  list<array<string, mixed>>  $rawItems
     * @return list<array<string, mixed>>
     */
    public static function normalizeItems(array $rawItems): array
    {
        $normalized = [];

        foreach ($rawItems as $row) {
            if (! is_array($row)) {
                continue;
            }

            $description = trim((string) ($row['description'] ?? ''));
            $itemName = trim((string) ($row['item_name'] ?? ''));

            if ($description === '' && $itemName === '') {
                continue;
            }

            $quantity = max(0, (float) ($row['quantity'] ?? 0));
            $unitPrice = max(0, (float) ($row['unit_price'] ?? 0));
            $totalPrice = isset($row['total_price']) && $row['total_price'] !== ''
                ? max(0, (float) $row['total_price'])
                : round($quantity * $unitPrice, 4);

            $entry = [
                'item_name' => $itemName !== '' ? $itemName : null,
                'description' => $description !== '' ? $description : $itemName,
                'unit' => isset($row['unit']) ? trim((string) $row['unit']) : null,
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'total_price' => $totalPrice,
            ];

            $itemId = $row['id'] ?? null;
            if ($itemId !== null && $itemId !== '') {
                $entry['id'] = (int) $itemId;
            }

            $normalized[] = $entry;
        }

        return $normalized;
    }

    /**
     * @param  array<string, mixed>  $validated
     * @return array<string, mixed>
     */
    public static function normalizeHeader(array $validated): array
    {
        $header = [];

        foreach ([
            'request_number', 'classification', 'status', 'project_id', 'zone_id',
            'category_id', 'subcategory_id', 'justification', 'delivery_lead_time_days',
            'delivery_location', 'currency_code', 'scope_of_work', 'warranty_coverage',
        ] as $key) {
            if (array_key_exists($key, $validated)) {
                $header[$key] = $validated[$key];
            }
        }

        foreach (['flexible_delivery_date', 'samples_required', 'nda_required', 'primary_insurance_applicable', 'final_insurance_applicable'] as $boolKey) {
            if (array_key_exists($boolKey, $validated)) {
                $header[$boolKey] = filter_var($validated[$boolKey], FILTER_VALIDATE_BOOLEAN);
            }
        }

        if (array_key_exists('warranty_years', $validated)) {
            $raw = $validated['warranty_years'];
            $header['warranty_years'] = ($raw === null || $raw === '') ? null : $raw;
        }

        if (array_key_exists('procurement_types', $validated)) {
            $header['procurement_types'] = ProcurementCheckboxGroup::encode(
                $validated['procurement_types'],
                ProcurementType::values()
            );
        }

        if (array_key_exists('geographic_scopes', $validated)) {
            $header['geographic_scopes'] = GeographicScope::encode($validated['geographic_scopes']);
        }

        if (array_key_exists('vendor_types', $validated)) {
            $header['vendor_types'] = ProcurementCheckboxGroup::encode(
                $validated['vendor_types'],
                ProcurementVendorType::values()
            );
        }

        return $header;
    }

    /**
     * @param  list<array<string, mixed>>  $rows
     * @return list<array<string, mixed>>
     */
    public static function normalizePaymentTerms(array $rows): array
    {
        $normalized = [];

        foreach ($rows as $row) {
            if (! is_array($row)) {
                continue;
            }

            $milestone = trim((string) ($row['milestone'] ?? ''));
            $amount = trim((string) ($row['amount'] ?? ''));
            $dueUpon = trim((string) ($row['due_upon'] ?? ''));
            $percentage = $row['percentage'] ?? null;

            if ($milestone === '' && $amount === '' && $dueUpon === '' && ($percentage === null || $percentage === '')) {
                continue;
            }

            $entry = [
                'milestone' => $milestone !== '' ? $milestone : null,
                'amount' => $amount !== '' ? $amount : null,
                'due_upon' => $dueUpon !== '' ? $dueUpon : null,
                'percentage' => ($percentage === null || $percentage === '') ? null : $percentage,
            ];

            $rowId = $row['id'] ?? null;
            if ($rowId !== null && $rowId !== '') {
                $entry['id'] = (int) $rowId;
            }

            $normalized[] = $entry;
        }

        return $normalized;
    }

    /**
     * @param  list<array<string, mixed>>  $rows
     * @return list<array<string, mixed>>
     */
    public static function normalizeRetentions(array $rows): array
    {
        $normalized = [];

        foreach ($rows as $row) {
            if (! is_array($row)) {
                continue;
            }

            $releasePeriod = trim((string) ($row['release_period'] ?? ''));
            $percent = $row['retention_percent'] ?? null;

            if ($releasePeriod === '' && ($percent === null || $percent === '')) {
                continue;
            }

            $entry = [
                'release_period' => $releasePeriod !== '' ? $releasePeriod : null,
                'retention_percent' => ($percent === null || $percent === '') ? null : $percent,
            ];

            $rowId = $row['id'] ?? null;
            if ($rowId !== null && $rowId !== '') {
                $entry['id'] = (int) $rowId;
            }

            $normalized[] = $entry;
        }

        return $normalized;
    }

}
