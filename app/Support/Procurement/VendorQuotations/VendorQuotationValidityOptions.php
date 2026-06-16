<?php

namespace App\Support\Procurement\VendorQuotations;

final class VendorQuotationValidityOptions
{
    /**
     * @return array<int, string>
     */
    public static function dayOptions(): array
    {
        return [
            30 => '30 days',
            60 => '60 days',
            90 => '90 days',
        ];
    }

    public static function labelForStored(?string $stored): ?string
    {
        $stored = trim((string) ($stored ?? ''));
        if ($stored === '') {
            return null;
        }

        foreach (self::dayOptions() as $days => $label) {
            if ($stored === $label || $stored === (string) $days) {
                return $label;
            }
        }

        return $stored;
    }

    public static function selectedDays(?string $stored): string
    {
        $stored = trim((string) ($stored ?? ''));

        foreach (self::dayOptions() as $days => $label) {
            if ($stored === $label || $stored === (string) $days) {
                return (string) $days;
            }
        }

        return 'custom';
    }
}
