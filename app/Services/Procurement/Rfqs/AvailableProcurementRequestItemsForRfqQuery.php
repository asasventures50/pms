<?php

namespace App\Services\Procurement\Rfqs;

use App\Models\Procurement\ProcurementRequests\ProcurementRequestItem;
use App\Models\Procurement\Rfqs\RfqItem;
use App\Support\Procurement\ProcurementScopeType;
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
            ->with([
                'procurementRequest:id,request_number',
                'project:id,code,name',
                'zone:id,code,name',
            ])
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

        $requiredDeliveryDate = $item->required_delivery_date?->format('Y-m-d') ?? '';

        return [
            'id' => $item->id,
            'label' => "{$prNumber} - {$name}",
            'pr_number' => $prNumber,
            'item' => $item->line_number ?? '',
            'project' => $item->project
                ? trim($item->project->code.' — '.$item->project->name)
                : '',
            'zone' => $item->zone
                ? trim($item->zone->code.' — '.$item->zone->name)
                : '',
            'category' => $item->category ?? '',
            'subcategory' => $item->subcategory ?? '',
            'scope_type' => ProcurementScopeType::display($item->scope_type),
            'description' => $item->description ?? '',
            'unit' => $item->unit ?? '',
            'quantity' => (float) $item->quantity,
            'justification' => $item->justification ?? '',
            'required_delivery_date' => $requiredDeliveryDate,
            'flexible_delivery_date' => (bool) $item->flexible_delivery_date,
            'delivery_location' => $item->delivery_location ?? '',
            'request_lead_time' => $requiredDeliveryDate,
        ];
    }
}
