<?php

namespace App\Support\Procurement;

final class ProcurementCheckboxGroup
{
    /**
     * @param  list<string>  $allowed
     * @return list<string>
     */
    public static function selectedValues(mixed $value, array $allowed): array
    {
        if (is_string($value) && $value !== '') {
            $decoded = json_decode($value, true);
            if (is_array($decoded)) {
                $value = $decoded;
            } else {
                $value = preg_split('/\s*,\s*/', trim($value)) ?: [];
            }
        }

        if (! is_array($value)) {
            return [];
        }

        $allowedMap = array_flip($allowed);
        $selected = [];

        foreach ($value as $candidate) {
            $key = strtolower(trim((string) $candidate));
            if ($key !== '' && isset($allowedMap[$key])) {
                $selected[$key] = true;
            }
        }

        $ordered = [];
        foreach ($allowed as $option) {
            if (isset($selected[$option])) {
                $ordered[] = $option;
            }
        }

        return $ordered;
    }

    /**
     * @param  list<string>  $allowed
     */
    public static function encode(mixed $value, array $allowed): ?array
    {
        $selected = self::selectedValues($value, $allowed);

        return $selected === [] ? null : $selected;
    }

    /**
     * @param  list<string>  $allowed
     */
    public static function display(mixed $stored, array $allowed, callable $labelFn): string
    {
        $selected = self::selectedValues($stored, $allowed);
        if ($selected === []) {
            return '';
        }

        return implode(', ', array_map($labelFn, $selected));
    }
}
