<?php

namespace App\Services\Procurement\ProcurementRequests;

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
        $request->items()->delete();

        foreach (array_values($items) as $index => $row) {
            ProcurementRequestItem::query()->create([
                'procurement_request_id' => $request->id,
                'sort_order' => $index,
                'zone' => $row['zone'] ?? null,
                'category' => $row['category'] ?? null,
                'subcategory' => $row['subcategory'] ?? null,
                'scope_type' => $row['scope_type'] ?? null,
                'description' => $row['description'] ?? null,
                'unit' => $row['unit'] ?? null,
                'quantity' => $row['quantity'],
                'justification' => $row['justification'] ?? null,
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
            if ($description === '') {
                continue;
            }

            $normalized[] = [
                'zone' => isset($row['zone']) ? trim((string) $row['zone']) : null,
                'category' => isset($row['category']) ? trim((string) $row['category']) : null,
                'subcategory' => isset($row['subcategory']) ? trim((string) $row['subcategory']) : null,
                'scope_type' => isset($row['scope_type']) ? trim((string) $row['scope_type']) : null,
                'description' => $description,
                'unit' => isset($row['unit']) ? trim((string) $row['unit']) : null,
                'quantity' => max(0, (float) ($row['quantity'] ?? 0)),
                'justification' => isset($row['justification']) ? trim((string) $row['justification']) : null,
            ];
        }

        return $normalized;
    }
}
