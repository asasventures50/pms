<?php

namespace App\Services\Procurement\Rfqs;

class RfqPayloadResolver
{
    /**
     * @param  array<string, mixed>  $validated
     */
    public static function finalizeForStore(array &$validated): void
    {
        $raw = $validated['rfq_number'] ?? null;
        $trimmed = $raw !== null ? trim((string) $raw) : '';

        if ($trimmed === '') {
            $validated['rfq_number'] = app(RfqCodeGenerator::class)->next();
        } else {
            $validated['rfq_number'] = $trimmed;
        }
    }

    /**
     * @param  array<string, mixed>  $validated
     */
    public static function finalizeForUpdate(array &$validated): void
    {
        if (! array_key_exists('rfq_number', $validated)) {
            return;
        }

        $raw = $validated['rfq_number'];
        if ($raw === null || trim((string) $raw) === '') {
            unset($validated['rfq_number']);

            return;
        }

        $validated['rfq_number'] = trim((string) $raw);
    }
}
