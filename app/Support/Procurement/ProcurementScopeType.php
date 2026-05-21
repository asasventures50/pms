<?php

namespace App\Support\Procurement;

final class ProcurementScopeType
{
    public const Supply = 'Supply';

    public const Service = 'Service';

    public const Installation = 'Installation';

    public const Maintenance = 'Maintenance';

    public const Dismantling = 'Dismantling';

    public const Studies = 'Studies';

    /**
     * @return list<string>
     */
    public static function options(): array
    {
        return [
            self::Supply,
            self::Service,
            self::Installation,
            self::Maintenance,
            self::Dismantling,
            self::Studies,
        ];
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
        $encoded = self::encode($stored);
        if ($encoded !== null && $encoded !== '') {
            return $encoded;
        }

        $legacy = trim((string) $stored);

        return $legacy;
    }
}
