<?php

namespace App\Services\Procurement\PurchaseOrders;

use App\Enums\Procurement\BuyerCompany;
use App\Enums\Procurement\PrCompany;
use App\Models\Procurement\ProcurementRequests\ProcurementRequest;
use App\Models\Procurement\PurchaseOrders\PurchaseOrder;

class PurchaseOrderBuyerCompanyApplier
{
    /**
     * @param  array<string, mixed>  $header
     */
    public static function applyToHeader(array &$header): void
    {
        $procurementRequestId = $header['procurement_request_id'] ?? null;
        if ($procurementRequestId !== null && $procurementRequestId !== '') {
            $procurementRequest = ProcurementRequest::query()->find((int) $procurementRequestId);
            if ($procurementRequest !== null) {
                self::applyPrCompany(PrCompany::resolve($procurementRequest->company_key), $header);

                return;
            }
        }

        BuyerCompany::applyToHeader($header);
        $header['company_key'] = null;
    }

    public static function resolveForPurchaseOrder(PurchaseOrder $purchaseOrder): PrCompany
    {
        $storedKey = trim((string) ($purchaseOrder->company_key ?? ''));
        if ($storedKey !== '') {
            return PrCompany::resolve($storedKey);
        }

        $purchaseOrder->loadMissing('procurementRequest');
        $linkedKey = trim((string) ($purchaseOrder->procurementRequest?->company_key ?? ''));

        return PrCompany::resolve($linkedKey !== '' ? $linkedKey : null);
    }

    /**
     * @param  array<string, mixed>  $header
     */
    private static function applyPrCompany(PrCompany $company, array &$header): void
    {
        foreach ($company->toPurchaseOrderHeader() as $column => $value) {
            $header[$column] = $value;
        }
    }
}
