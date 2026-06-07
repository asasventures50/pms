<?php

namespace App\Support\Procurement;

final class ProcurementScopeType
{
    public const Contractor = 'Contractor';

    public const Supplier = 'Supplier';

    public const Studies = 'Studies';

    /**
     * Scope types selectable on procurement request line items.
     *
     * @return list<string>
     */
    public static function requestLineOptions(): array
    {
        return [
            self::Supplier,
            self::Contractor,
            self::Studies,
        ];
    }

    /**
     * @return list<string>
     */
    public static function options(): array
    {
        return [
            self::Contractor,
            self::Supplier,
            self::Studies,
        ];
    }

    public static function label(string $value): string
    {
        return match ($value) {
            self::Supplier => 'Supplier',
            self::Contractor => 'Contractor',
            self::Studies => 'Studies',
            default => $value,
        };
    }

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return self::options();
    }

    /**
     * @param  mixed  $value  Stored string, submitted array, or null
     * @return list<string>
     */
    public static function selectedValues(mixed $value): array
    {
        if (is_array($value)) {
            $candidates = $value;
        } elseif (is_string($value) && trim($value) !== '') {
            $candidates = preg_split('/\s*,\s*/', trim($value)) ?: [];
        } else {
            return [];
        }

        $allowed = array_flip(self::options());
        $selected = [];

        foreach ($candidates as $candidate) {
            $label = trim((string) $candidate);
            if ($label !== '' && isset($allowed[$label])) {
                $selected[$label] = true;
            }
        }

        $ordered = [];
        foreach (self::options() as $option) {
            if (isset($selected[$option])) {
                $ordered[] = $option;
            }
        }

        return $ordered;
    }

    /**
     * @param  mixed  $value
     */
    public static function encode(mixed $value): ?string
    {
        $selected = self::selectedValues($value);

        return $selected === [] ? null : implode(', ', $selected);
    }

    public static function display(?string $stored): string
    {
        $selected = self::selectedValues($stored);
        if ($selected !== []) {
            return implode(', ', array_map(self::label(...), $selected));
        }

        $legacy = trim((string) $stored);

        return $legacy !== '' ? $legacy : '';
    }
}
