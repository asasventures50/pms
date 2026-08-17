<?php

namespace App\Services\Procurement\PurchaseOrders;

use App\Enums\Procurement\Rfqs\RfqTermsLocale;
use App\Models\Procurement\ProcurementRequests\ProcurementRequest;
use App\Models\Procurement\PurchaseOrders\PurchaseOrder;
use App\Models\Procurement\PurchaseOrders\PurchaseOrderItem;
use App\Services\Procurement\Rfqs\RfqGeneralTermsService;
use App\Services\Procurement\PurchaseOrders\PurchaseOrderBuyerCompanyApplier;
use Illuminate\Support\Facades\DB;

class PurchaseOrderPersistenceService
{
    public function __construct(
        private readonly RfqGeneralTermsService $termsService,
    ) {}

    /**
     * @param  array<string, mixed>  $header
     * @param  list<array<string, mixed>>  $items
     */
    public function create(array $header, array $items): PurchaseOrder
    {
        return DB::transaction(function () use ($header, $items) {
            PurchaseOrderBuyerCompanyApplier::applyToHeader($header);
            $header = $this->applyTotals($header, $items);
            $paymentTermRows = array_key_exists('payment_term_rows', $header) ? $header['payment_term_rows'] : null;
            unset($header['payment_term_rows']);

            $purchaseOrder = PurchaseOrder::query()->create($header);
            $this->syncItems($purchaseOrder, $items);
            if ($paymentTermRows !== null) {
                $this->syncPaymentTerms($purchaseOrder, $paymentTermRows);
            }

            return $purchaseOrder->load(['items', 'vendor', 'creator', 'paymentTermRows']);
        });
    }

    /**
     * @param  array<string, mixed>  $header
     * @param  list<array<string, mixed>>  $items
     */
    public function update(PurchaseOrder $purchaseOrder, array $header, array $items): PurchaseOrder
    {
        return DB::transaction(function () use ($purchaseOrder, $header, $items) {
            PurchaseOrderBuyerCompanyApplier::applyToHeader($header);
            $header = $this->applyTotals($header, $items);
            $paymentTermRows = array_key_exists('payment_term_rows', $header) ? $header['payment_term_rows'] : null;
            unset($header['payment_term_rows']);

            $purchaseOrder->fill($header);
            $purchaseOrder->save();
            $this->syncItems($purchaseOrder, $items);
            if ($paymentTermRows !== null) {
                $this->syncPaymentTerms($purchaseOrder, $paymentTermRows);
            }

            return $purchaseOrder->load(['items', 'vendor', 'creator', 'paymentTermRows']);
        });
    }

    /**
     * @param  array<string, mixed>  $header
     * @param  list<array<string, mixed>>  $items
     * @return array<string, mixed>
     */
    private function applyTotals(array $header, array $items): array
    {
        $linesSubtotal = 0.0;

        foreach ($items as $row) {
            $linesSubtotal += (float) ($row['line_total'] ?? 0);
        }

        $deliveryFee = max(0, (float) ($header['delivery_fee'] ?? 0));
        $discount = max(0, (float) ($header['discount'] ?? 0));
        $header['delivery_fee'] = round($deliveryFee, 2);
        $header['discount'] = round($discount, 2);
        $header['total_price'] = round(max(0, $linesSubtotal + $deliveryFee - $discount), 2);

        if (empty($header['title'])) {
            $poNumber = $header['po_number'] ?? '';
            $header['title'] = $poNumber !== '' ? 'Purchase Order '.$poNumber : 'Purchase Order';
        }

        $locale = $header['terms_locale'] ?? RfqTermsLocale::default()->value;
        if (! in_array($locale, RfqTermsLocale::values(), true)) {
            $locale = RfqTermsLocale::default()->value;
        }
        $header['terms_locale'] = $locale;

        $poLineNumbers = [];
        foreach ($items as $row) {
            $lineNumber = trim((string) ($row['item'] ?? ''));
            if ($lineNumber !== '') {
                $poLineNumbers[] = $lineNumber;
            }
        }

        $scopeTypes = $this->termsService->scopeTypesFromLinkedProcurementRequest(
            isset($header['procurement_request_id']) && $header['procurement_request_id'] !== ''
                ? (int) $header['procurement_request_id']
                : null,
            array_values(array_unique($poLineNumbers)),
            $header['handover_at'] ?? null,
            $header['dismantling_at'] ?? null,
        );
        $general = $this->termsService->activeTextsForScopeTypes($scopeTypes, $locale);
        $custom = $this->termsService->normalizeCustomTermsInput($header['terms_custom'] ?? [], $locale);
        unset($header['terms_custom']);

        $header['terms'] = $this->termsService->buildTermsPayload($general, $custom);

        if (array_key_exists('payment_term_rows', $header)) {
            $paymentTermRows = PurchaseOrderPaymentTermsSynchronizer::normalize($header['payment_term_rows']);
            $header['payment_term_rows'] = $paymentTermRows;
            $header['payment_terms'] = PurchaseOrderPaymentTermsSynchronizer::flatten($paymentTermRows);
        }

        return $header;
    }

    /**
     * @param  list<array{id: int|null, milestone: string, percentage: float|null, amount: float|null}>  $rows
     */
    private function syncPaymentTerms(PurchaseOrder $purchaseOrder, array $rows): void
    {
        app(PurchaseOrderPaymentTermsSynchronizer::class)->sync($purchaseOrder, $rows);
    }

    /**
     * @param  list<array<string, mixed>>  $items
     */
    private function syncItems(PurchaseOrder $purchaseOrder, array $items): void
    {
        $purchaseOrder->items()->delete();

        foreach (array_values($items) as $index => $row) {
            PurchaseOrderItem::query()->create([
                'purchase_order_id' => $purchaseOrder->id,
                'sort_order' => $index,
                'item' => $row['item'] ?? null,
                'description' => $row['description'] ?? null,
                'quantity' => $row['quantity'],
                'unit' => $row['unit'] ?? null,
                'unit_price' => $row['unit_price'],
                'line_total' => $row['line_total'],
            ]);
        }
    }

    /**
     * @param  list<array<string, mixed>>  $rawItems
     * @return list<array<string, mixed>>
     */
    public static function normalizeItems(array $rawItems, ?int $procurementRequestId = null): array
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

            $quantity = max(0, (float) ($row['quantity'] ?? 0));
            $unitPrice = max(0, (float) ($row['unit_price'] ?? 0));
            $lineTotal = round($quantity * $unitPrice, 2);

            $unit = isset($row['unit']) ? trim((string) $row['unit']) : '';

            $normalized[] = [
                'item' => isset($row['item']) ? trim((string) $row['item']) : null,
                'description' => $description,
                'quantity' => $quantity,
                'unit' => $unit !== '' ? $unit : null,
                'unit_price' => $unitPrice,
                'line_total' => $lineTotal,
            ];
        }

        if ($procurementRequestId === null) {
            return $normalized;
        }

        $procurementRequest = ProcurementRequest::query()
            ->with('items')
            ->find($procurementRequestId);

        return ProcurementRequestLineUnitLookup::applyToPoItemRows(
            $normalized,
            ProcurementRequestLineUnitLookup::unitsByLineCode($procurementRequest),
        );
    }
}
