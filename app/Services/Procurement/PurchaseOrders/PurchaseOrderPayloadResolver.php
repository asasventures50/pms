<?php

namespace App\Services\Procurement\PurchaseOrders;

class PurchaseOrderPayloadResolver
{
    /**
     * Ensure po_number is set: generate when blank after validation.
     *
     * @param  array<string, mixed>  $validated
     */
    public static function finalizeForStore(array &$validated): void
    {
        $raw     = $validated['po_number'] ?? null;
        $trimmed = $raw !== null ? trim((string) $raw) : '';

        if ($trimmed === '') {
            $validated['po_number'] = app(PurchaseOrderCodeGenerator::class)->next();
        } else {
            $validated['po_number'] = $trimmed;
        }
    }

    /**
     * Normalize po_number on update; drop key when empty so the existing value is kept.
     *
     * @param  array<string, mixed>  $validated
     */
    public static function finalizeForUpdate(array &$validated): void
    {
        if (! array_key_exists('po_number', $validated)) {
            return;
        }

        $raw = $validated['po_number'];
        if ($raw === null || trim((string) $raw) === '') {
            unset($validated['po_number']);

            return;
        }

        $validated['po_number'] = trim((string) $raw);
    }
}
