<?php

namespace App\Services\Procurement\ProcurementRequests;

class ProcurementRequestPayloadResolver
{
    /**
     * @param  array<string, mixed>  $validated
     */
    public static function finalizeForStore(array &$validated): void
    {
        $raw = $validated['request_number'] ?? null;
        $trimmed = $raw !== null ? trim((string) $raw) : '';

        if ($trimmed === '') {
            $date = isset($validated['requested_at'])
                ? \Illuminate\Support\Carbon::parse($validated['requested_at'])
                : now();
            $validated['request_number'] = app(ProcurementRequestCodeGenerator::class)->next($date);
        } else {
            $validated['request_number'] = $trimmed;
        }
    }

    /**
     * @param  array<string, mixed>  $validated
     */
    public static function finalizeForUpdate(array &$validated): void
    {
        if (! array_key_exists('request_number', $validated)) {
            return;
        }

        $raw = $validated['request_number'];
        if ($raw === null || trim((string) $raw) === '') {
            unset($validated['request_number']);

            return;
        }

        $validated['request_number'] = trim((string) $raw);
    }
}
