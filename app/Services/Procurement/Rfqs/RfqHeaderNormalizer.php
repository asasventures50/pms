<?php

namespace App\Services\Procurement\Rfqs;

use App\Support\Procurement\VendorQuotations\VendorQuotationValidityOptions;
use Carbon\Carbon;

final class RfqHeaderNormalizer
{
    /**
     * @param  array<string, mixed>  $header
     * @return array<string, mixed>
     */
    public static function normalize(array $header): array
    {
        $header['revision_number'] = max(0, (int) ($header['revision_number'] ?? 0));

        $preset = $header['quotation_validity_preset'] ?? null;
        if (is_string($preset) && $preset !== '' && $preset !== 'custom') {
            $days = (int) $preset;
            if (isset(VendorQuotationValidityOptions::dayOptions()[$days])) {
                $header['quotation_validity'] = VendorQuotationValidityOptions::dayOptions()[$days];
            }
        }
        unset($header['quotation_validity_preset']);

        $date = $header['submission_deadline'] ?? null;
        $time = trim((string) ($header['submission_deadline_time'] ?? '17:00'));
        $timezone = trim((string) ($header['submission_timezone'] ?? config('app.timezone')));

        if ($date) {
            $header['submission_timezone'] = $timezone !== '' ? $timezone : config('app.timezone');
            $header['submission_deadline_at'] = Carbon::parse(
                $date.' '.($time !== '' ? $time : '17:00'),
                $header['submission_timezone'],
            );
        } else {
            $header['submission_deadline_at'] = null;
        }

        unset($header['submission_deadline_time']);

        return $header;
    }
}
