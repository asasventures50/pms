<?php

namespace App\Services\Procurement\Rfqs;

use App\Models\Procurement\ProcurementRequests\ProcurementRequestItem;
use App\Models\Procurement\Rfqs\RfqItem;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;

class AvailableProcurementRequestItemsForRfqQuery
{
    /**
     * PR line items not yet linked to another RFQ (current RFQ excluded when editing).
     *
     * @return Collection<int, ProcurementRequestItem>
     */
    public function get(?int $exceptRfqId = null): Collection
    {
        $assignedIds = RfqItem::query()
            ->whereNotNull('procurement_request_item_id')
            ->when($exceptRfqId, fn ($q) => $q->where('rfq_id', '!=', $exceptRfqId))
            ->pluck('procurement_request_item_id');

        return ProcurementRequestItem::query()
            ->with(['procurementRequest:id,request_number'])
            ->whereHas('procurementRequest', fn ($q) => $q->whereNull('deleted_at'))
            ->whereNotIn('id', $assignedIds)
            ->orderByDesc('id')
            ->get();
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function optionsForForm(?int $exceptRfqId = null): array
    {
        return $this->get($exceptRfqId)
            ->map(fn (ProcurementRequestItem $item) => $this->toOption($item))
            ->values()
            ->all();
    }

    /**
     * @return array<string, mixed>
     */
    public function toOption(ProcurementRequestItem $item): array
    {
        $prNumber = $item->procurementRequest?->request_number ?? 'PR';
        $name = Str::limit(trim((string) $item->description), 80) ?: 'Item';

        return [
            'id' => $item->id,
            'label' => "{$prNumber} - {$name}",
            'item' => $item->line_number ?? '',
            'description' => $item->description ?? '',
            'quantity' => (float) $item->quantity,
            'unit' => $item->unit ?? '',
        ];
    }
}
