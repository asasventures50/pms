<?php

namespace App\Services\Procurement\Invoices;

use App\Models\Procurement\Invoices\Invoice;
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

    /**
     * @return array{project: string, zone: string}
     */
    public static function splitStoredLabel(?string $stored): array
    {
        $stored = trim((string) $stored);

        if ($stored === '') {
            return ['project' => '', 'zone' => ''];
        }

        if (str_contains($stored, '/')) {
            [$project, $zone] = explode('/', $stored, 2);

            return [
                'project' => trim($project),
                'zone' => trim($zone),
            ];
        }

        return ['project' => $stored, 'zone' => ''];
    }

    public function projectForPoItem(PurchaseOrderItem $item): ?string
    {
        $parts = $this->projectAndZoneFromPrItem($this->prItemForPoItem($item));

        return $parts['project'] !== '' ? $parts['project'] : null;
    }

    public function zoneForPoItem(PurchaseOrderItem $item): ?string
    {
        $parts = $this->projectAndZoneFromPrItem($this->prItemForPoItem($item));

        return $parts['zone'] !== '' ? $parts['zone'] : null;
    }

    public function projectForInvoiceItem(InvoiceItem $item, Collection $poItemsById): ?string
    {
        $stored = trim((string) ($item->project_zone ?? ''));

        if ($stored !== '') {
            if (str_contains($stored, '/')) {
                $project = self::splitStoredLabel($stored)['project'];

                return $project !== '' ? $project : null;
            }

            $fromPo = $this->uniqueValuesFromPoItems(
                $this->sourcePoItemsForInvoiceItem($item, $poItemsById),
                fn (PurchaseOrderItem $poItem) => $this->projectForPoItem($poItem),
            );

            if ($fromPo !== []) {
                return implode('; ', $fromPo);
            }

            return $stored;
        }

        $fromPo = $this->uniqueValuesFromPoItems(
            $this->sourcePoItemsForInvoiceItem($item, $poItemsById),
            fn (PurchaseOrderItem $poItem) => $this->projectForPoItem($poItem),
        );

        return $fromPo === [] ? null : implode('; ', $fromPo);
    }

    public function zoneForInvoiceItem(InvoiceItem $item, Collection $poItemsById): ?string
    {
        $stored = trim((string) ($item->project_zone ?? ''));

        if ($stored !== '') {
            if (str_contains($stored, '/')) {
                $zone = self::splitStoredLabel($stored)['zone'];

                return $zone !== '' ? $zone : null;
            }

            $fromPo = $this->uniqueValuesFromPoItems(
                $this->sourcePoItemsForInvoiceItem($item, $poItemsById),
                fn (PurchaseOrderItem $poItem) => $this->zoneForPoItem($poItem),
            );

            return $fromPo === [] ? null : implode('; ', $fromPo);
        }

        $fromPo = $this->uniqueValuesFromPoItems(
            $this->sourcePoItemsForInvoiceItem($item, $poItemsById),
            fn (PurchaseOrderItem $poItem) => $this->zoneForPoItem($poItem),
        );

        return $fromPo === [] ? null : implode('; ', $fromPo);
    }

    public function uniqueProjectsLabelForInvoice(Invoice $invoice, Collection $poItemsById): ?string
    {
        $projects = [];

        foreach ($invoice->items as $item) {
            $project = $this->projectForInvoiceItem($item, $poItemsById);

            if ($project === null || $project === '') {
                continue;
            }

            foreach (preg_split('/\s*;\s*/', $project) ?: [] as $part) {
                $part = trim((string) $part);

                if ($part !== '') {
                    $projects[$part] = true;
                }
            }
        }

        foreach ($invoice->feeRowsForPrint() as $fee) {
            $project = self::splitStoredLabel($fee['project_zone'] ?? '')['project'];

            if ($project !== '') {
                $projects[$project] = true;
            }
        }

        if ($projects === []) {
            return null;
        }

        return implode(' / ', array_keys($projects));
    }

    private function prItemForPoItem(PurchaseOrderItem $item): ?ProcurementRequestItem
    {
        $lineCode = trim((string) ($item->item ?? ''));

        if ($lineCode === '') {
            return null;
        }

        return $this->prItemsByLine[$lineCode] ?? null;
    }

    /**
     * @return array{project: string, zone: string}
     */
    private function projectAndZoneFromPrItem(?ProcurementRequestItem $prItem): array
    {
        if ($prItem === null) {
            return ['project' => '', 'zone' => ''];
        }

        $project = $prItem->project ?? $this->procurementRequest?->project;
        $zone = $prItem->zone ?? $this->procurementRequest?->zone;

        return [
            'project' => trim((string) ($project?->name ?? '')),
            'zone' => trim((string) ($zone?->name ?? '')),
        ];
    }

    /**
     * @return Collection<int, PurchaseOrderItem>
     */
    private function sourcePoItemsForInvoiceItem(InvoiceItem $item, Collection $poItemsById): Collection
    {
        return collect($item->source_purchase_order_item_ids ?? [])
            ->map(fn ($id) => (int) $id)
            ->filter()
            ->map(fn (int $id) => $poItemsById->get($id))
            ->filter()
            ->values();
    }

    /**
     * @param  Collection<int, PurchaseOrderItem>  $sourceItems
     * @param  callable(PurchaseOrderItem): ?string  $extractor
     * @return list<string>
     */
    private function uniqueValuesFromPoItems(Collection $sourceItems, callable $extractor): array
    {
        $values = [];

        foreach ($sourceItems as $item) {
            $value = $extractor($item);

            if ($value !== null && $value !== '') {
                $values[$value] = true;
            }
        }

        return array_keys($values);
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
