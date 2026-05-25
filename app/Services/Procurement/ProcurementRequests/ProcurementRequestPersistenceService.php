<?php

namespace App\Services\Procurement\ProcurementRequests;

use App\Support\Procurement\ProcurementScopeType;
use App\Models\Procurement\ProcurementRequests\ProcurementRequest;
use App\Models\Procurement\ProcurementRequests\ProcurementRequestItem;
use Illuminate\Support\Facades\DB;

class ProcurementRequestPersistenceService
{
    /**
     * @param  array<string, mixed>  $header
     * @param  list<array<string, mixed>>  $items
     */
    public function create(array $header, array $items): ProcurementRequest
    {
        return DB::transaction(function () use ($header, $items) {
            $request = ProcurementRequest::query()->create($header);
            $this->syncItems($request, $items);

            return $request->load(['items', 'creator']);
        });
    }

    /**
     * @param  array<string, mixed>  $header
     * @param  list<array<string, mixed>>  $items
     */
    public function update(ProcurementRequest $request, array $header, array $items): ProcurementRequest
    {
        return DB::transaction(function () use ($request, $header, $items) {
            $request->fill($header);
            $request->save();
            $this->syncItems($request, $items);

            return $request->load(['items', 'creator']);
        });
    }

    /**
     * @param  list<array<string, mixed>>  $items
     */
    private function syncItems(ProcurementRequest $request, array $items): void
    {
        $keptIds = [];

        foreach (array_values($items) as $index => $row) {
            $attributes = [
                'sort_order' => $index,
                'line_number' => ProcurementRequestLineNumberFormatter::format($request->request_number, $index),
                'project_id' => $row['project_id'] ?? null,
                'zone_id' => $row['zone_id'] ?? null,
                'category' => $row['category'] ?? null,
                'subcategory' => $row['subcategory'] ?? null,
                'scope_type' => $row['scope_type'] ?? null,
                'description' => $row['description'] ?? null,
                'unit' => $row['unit'] ?? null,
                'quantity' => $row['quantity'],
                'justification' => $row['justification'] ?? null,
                'scope_of_work' => $row['scope_of_work'] ?? null,
                'required_delivery_date' => $row['required_delivery_date'] ?? null,
                'flexible_delivery_date' => $row['flexible_delivery_date'] ?? true,
                'delivery_location' => $row['delivery_location'] ?? null,
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
                $item->update($attributes);
                $keptIds[] = $item->id;

                continue;
            }

            $item = ProcurementRequestItem::query()->create([
                'procurement_request_id' => $request->id,
                ...$attributes,
            ]);

            $keptIds[] = $item->id;
        }

        if ($keptIds !== []) {
            $request->items()->whereNotIn('id', $keptIds)->delete();
        } else {
            $request->items()->delete();
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
            if ($description === '') {
                continue;
            }

            $projectId = $row['project_id'] ?? null;
            $zoneId = $row['zone_id'] ?? null;

            $flexible = filter_var($row['flexible_delivery_date'] ?? false, FILTER_VALIDATE_BOOLEAN);
            $deliveryDate = trim((string) ($row['required_delivery_date'] ?? ''));
            $scopeOfWork = trim((string) ($row['scope_of_work'] ?? ''));

            $entry = [
                'project_id' => $projectId !== null && $projectId !== '' ? (int) $projectId : null,
                'zone_id' => $zoneId !== null && $zoneId !== '' ? (int) $zoneId : null,
                'category' => isset($row['category']) ? trim((string) $row['category']) : null,
                'subcategory' => isset($row['subcategory']) ? trim((string) $row['subcategory']) : null,
                'scope_type' => ProcurementScopeType::encode($row['scope_type'] ?? null),
                'description' => $description,
                'unit' => isset($row['unit']) ? trim((string) $row['unit']) : null,
                'quantity' => max(0, (float) ($row['quantity'] ?? 0)),
                'justification' => isset($row['justification']) ? trim((string) $row['justification']) : null,
                'scope_of_work' => $scopeOfWork !== '' ? $scopeOfWork : null,
                'required_delivery_date' => $deliveryDate !== '' ? $deliveryDate : null,
                'flexible_delivery_date' => $flexible,
                'delivery_location' => trim((string) ($row['delivery_location'] ?? '')),
            ];

            $itemId = $row['id'] ?? null;
            if ($itemId !== null && $itemId !== '') {
                $entry['id'] = (int) $itemId;
            }

            $normalized[] = $entry;
        }

        return $normalized;
    }
}
