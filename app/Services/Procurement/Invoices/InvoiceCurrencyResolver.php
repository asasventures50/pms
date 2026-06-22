<?php

namespace App\Services\Procurement\Invoices;

use App\Models\Procurement\PurchaseOrders\PurchaseOrder;

class InvoiceCurrencyResolver
{
    public const DEFAULT = 'USD';

    public static function normalizeCode(?string $raw): ?string
    {
        $code = strtoupper(preg_replace('/[^A-Za-z]/', '', trim((string) ($raw ?? ''))) ?? '');

        return strlen($code) === 3 ? $code : null;
    }

    /**
     * PO currency, then linked PR currency, then USD.
     */
    public static function resolveFromPurchaseOrder(PurchaseOrder $purchaseOrder): string
    {
        $purchaseOrder->loadMissing('procurementRequest');

        $fromPo = self::normalizeCode($purchaseOrder->currency_code);
        if ($fromPo !== null) {
            return $fromPo;
        }

        $fromPr = self::normalizeCode($purchaseOrder->procurementRequest?->currency_code);
        if ($fromPr !== null) {
            return $fromPr;
        }

        return self::DEFAULT;
    }

    /**
     * @return array{code: string, source: 'po'|'pr'|'default'}
     */
    public static function resolveWithSource(PurchaseOrder $purchaseOrder): array
    {
        $purchaseOrder->loadMissing('procurementRequest');

        $fromPo = self::normalizeCode($purchaseOrder->currency_code);
        if ($fromPo !== null) {
            return ['code' => $fromPo, 'source' => 'po'];
        }

        $fromPr = self::normalizeCode($purchaseOrder->procurementRequest?->currency_code);
        if ($fromPr !== null) {
            return ['code' => $fromPr, 'source' => 'pr'];
        }

        return ['code' => self::DEFAULT, 'source' => 'default'];
    }

    public static function resolveForStore(?string $submitted, PurchaseOrder $purchaseOrder): string
    {
        $normalized = self::normalizeCode($submitted);

        return $normalized ?? self::resolveFromPurchaseOrder($purchaseOrder);
    }
}
