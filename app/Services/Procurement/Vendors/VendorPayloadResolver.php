<?php

namespace App\Services\Procurement\Vendors;

class VendorPayloadResolver
{
    /**
     * Ensure vendor_code is set: generate when blank after validation.
     *
     * @param  array<string, mixed>  $validated
     */
    public static function finalizeForStore(array &$validated): void
    {
        $raw = $validated['vendor_code'] ?? null;
        $trimmed = $raw !== null ? trim((string) $raw) : '';

        if ($trimmed === '') {
            $validated['vendor_code'] = app(VendorCodeGenerator::class)->next();
        } else {
            $validated['vendor_code'] = $trimmed;
        }
    }

    /**
     * Normalize vendor_code on update; drop key when empty so the existing value is kept.
     *
     * @param  array<string, mixed>  $validated
     */
    public static function finalizeForUpdate(array &$validated): void
    {
        if (! array_key_exists('vendor_code', $validated)) {
            return;
        }

        $raw = $validated['vendor_code'];
        if ($raw === null || trim((string) $raw) === '') {
            unset($validated['vendor_code']);

            return;
        }

        $validated['vendor_code'] = trim((string) $raw);
    }
}
