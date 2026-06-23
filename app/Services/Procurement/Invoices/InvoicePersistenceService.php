<?php

namespace App\Services\Procurement\Invoices;

use App\Models\Procurement\Invoices\Invoice;
use App\Models\Procurement\Invoices\InvoiceItem;
use App\Models\Procurement\PurchaseOrders\PurchaseOrder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class InvoicePersistenceService
{
    public function __construct(
        private readonly InvoiceCodeGenerator $codeGenerator,
    ) {}

    /**
     * @param  array<string, mixed>  $header
     * @param  list<int>  $purchaseOrderIds
     * @param  list<array<string, mixed>>  $lines
     */
    public function create(array $header, array $purchaseOrderIds, array $lines): Invoice
    {
        return DB::transaction(function () use ($header, $purchaseOrderIds, $lines) {
            $header['invoice_number'] = $header['invoice_number'] ?? $this->codeGenerator->next();
            $linesSubtotal = round((float) collect($lines)->sum('line_total'), 2);
            $feesSubtotal = round(
                (float) ($header['transport_fees'] ?? 0)
                + (float) ($header['supervision_fees'] ?? 0)
                + (float) ($header['administrative_fees'] ?? 0)
                + (float) ($header['logistics_fees'] ?? 0),
                2,
            );
            $header['total_price'] = round($linesSubtotal + $feesSubtotal, 2);

            $invoice = Invoice::query()->create($header);
            $invoice->purchaseOrders()->sync($purchaseOrderIds);

            foreach (array_values($lines) as $index => $row) {
                InvoiceItem::query()->create([
                    'invoice_id' => $invoice->id,
                    'sort_order' => $index,
                    'line_number' => $row['line_number'],
                    'description' => $row['description'],
                    'project_zone' => $row['project_zone'] ?? null,
                    'quantity' => $row['quantity'],
                    'unit' => $row['unit'] ?? null,
                    'unit_price' => $row['unit_price'],
                    'line_total' => $row['line_total'],
                    'source_purchase_order_item_ids' => $row['source_purchase_order_item_ids'] ?? null,
                ]);
            }

            return $invoice->load(['items', 'purchaseOrders', 'creator']);
        });
    }

    /**
     * @param  Collection<int, PurchaseOrder>  $purchaseOrders
     * @return array<string, mixed>
     */
    public static function headerFromPurchaseOrders(
        Collection $purchaseOrders,
        string $recipientName,
        ?string $projectManagerName,
        int $createdBy,
        bool $mergedLines,
        string $currencyCode,
        float $transportFees,
        float $supervisionFees,
        float $administrativeFees,
        float $logisticsFees,
        array $notes,
    ): array {
        $vendorNames = $purchaseOrders
            ->map(fn (PurchaseOrder $po) => trim((string) ($po->vendor_company_name ?? $po->vendor?->name ?? '')))
            ->filter()
            ->unique()
            ->values();

        $poNumbers = $purchaseOrders
            ->pluck('po_number')
            ->filter()
            ->unique()
            ->values();

        $cleanNotes = collect($notes)
            ->map(static fn (mixed $note): string => trim((string) $note))
            ->filter()
            ->values()
            ->all();

        return [
            'created_by' => $createdBy,
            'recipient_name' => $recipientName,
            'project_manager_name' => filled($projectManagerName) ? trim($projectManagerName) : null,
            'invoiced_at' => now()->toDateString(),
            'po_number' => $poNumbers->implode(', '),
            'vendor_company_name' => $vendorNames->isNotEmpty() ? $vendorNames->implode(' · ') : null,
            'currency_code' => InvoiceCurrencyResolver::normalizeCode($currencyCode) ?? InvoiceCurrencyResolver::DEFAULT,
            'transport_fees' => $transportFees,
            'supervision_fees' => $supervisionFees,
            'administrative_fees' => $administrativeFees,
            'logistics_fees' => $logisticsFees,
            'merged_lines' => $mergedLines,
            'notes' => $cleanNotes !== [] ? $cleanNotes : null,
        ];
    }
}
