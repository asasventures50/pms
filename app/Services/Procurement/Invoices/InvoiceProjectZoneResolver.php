<?php

namespace App\Services\Procurement\Invoices;

use App\Models\Procurement\Invoices\InvoiceItem;
use App\Models\Procurement\ProcurementRequests\ProcurementRequest;
use App\Models\Procurement\ProcurementRequests\ProcurementRequestItem;
use App\Models\Procurement\PurchaseOrders\PurchaseOrderItem;
use Illuminate\Support\Collection;

class InvoiceProjectZoneResolver
{
    /** @var array<string, ProcurementRequestItem> */
    private array $prItemsByLine = [];

    public function __construct(private readonly ?ProcurementRequest $procurementRequest)
    {
        if ($procurementRequest === null) {
            return;
        }

        $procurementRequest->loadMissing(['items.project', 'items.zone', 'project', 'zone']);

        foreach ($procurementRequest->items as $item) {
            $lineNumber = trim((string) ($item->line_number ?? ''));

            if ($lineNumber !== '') {
                $this->prItemsByLine[$lineNumber] = $item;
            }
        }
    }

    /**
     * @param  Collection<int, PurchaseOrderItem>  $items
     */
    public static function fromPurchaseOrderItems(Collection $items): self
    {
        $items->loadMissing([
            'purchaseOrder.procurementRequest.items.project',
            'purchaseOrder.procurementRequest.items.zone',
            'purchaseOrder.procurementRequest.project',
            'purchaseOrder.procurementRequest.zone',
        ]);

        $request = $items->first()?->purchaseOrder?->procurementRequest;

        return new self($request);
    }

    public function forPoItem(PurchaseOrderItem $item): ?string
    {
        $lineCode = trim((string) ($item->item ?? ''));
        $prItem = $lineCode !== '' ? ($this->prItemsByLine[$lineCode] ?? null) : null;

        if ($prItem === null) {
            return null;
        }

        return self::formatLabel($prItem, $this->procurementRequest);
    }

    /**
     * @param  Collection<int, PurchaseOrderItem>  $items
     */
    public function forPoItems(Collection $items): ?string
    {
        $labels = [];

        foreach ($items as $item) {
            $label = $this->forPoItem($item);

            if ($label !== null && $label !== '') {
                $labels[$label] = true;
            }
        }

        if ($labels === []) {
            return null;
        }

        return implode('; ', array_keys($labels));
    }

    public function forInvoiceItem(InvoiceItem $item, Collection $poItemsById): ?string
    {
        $stored = trim((string) ($item->project_zone ?? ''));

        if ($stored !== '') {
            return $stored;
        }

        $sourceIds = collect($item->source_purchase_order_item_ids ?? [])
            ->map(fn ($id) => (int) $id)
            ->filter()
            ->values();

        if ($sourceIds->isEmpty()) {
            return null;
        }

        $sourceItems = $sourceIds
            ->map(fn (int $id) => $poItemsById->get($id))
            ->filter()
            ->values();

        return $this->forPoItems($sourceItems);
    }

    public static function formatLabel(ProcurementRequestItem $item, ?ProcurementRequest $request = null): ?string
    {
        $project = $item->project ?? $request?->project;
        $zone = $item->zone ?? $request?->zone;

        $projectName = trim((string) ($project?->name ?? ''));
        $zoneName = trim((string) ($zone?->name ?? ''));

        if ($projectName === '' && $zoneName === '') {
            return null;
        }

        if ($zoneName !== '') {
            return $projectName !== '' ? "{$projectName}/{$zoneName}" : $zoneName;
        }

        return $projectName;
    }
}
