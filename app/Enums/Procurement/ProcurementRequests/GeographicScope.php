<?php

namespace App\Enums\Procurement\ProcurementRequests;

use App\Support\Procurement\ProcurementCheckboxGroup;

enum GeographicScope: string
{
    case Local = 'local';
    case International = 'international';

    public function label(): string
    {
        return match ($this) {
            self::Local => 'Local',
            self::International => 'International',
        };
    }

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_map(static fn (self $case) => $case->value, self::cases());
    }

    /**
     * @return list<string>
     */
    public static function selectedValues(mixed $value): array
    {
        if (is_string($value) && strtolower(trim($value)) === 'both') {
            return self::values();
        }

        $selected = ProcurementCheckboxGroup::selectedValues(
            $value,
            array_merge(self::values(), ['both'])
        );

        if (in_array('both', $selected, true)) {
            return self::values();
        }

        return $selected;
    }

    /**
     * @return list<string>|null
     */
    public static function encode(mixed $value): ?array
    {
        $selected = self::selectedValues($value);

        return $selected === [] ? null : $selected;
    }

    public static function display(mixed $stored): string
    {
        $selected = self::selectedValues($stored);
        if ($selected === []) {
            return '';
        }

        if (count(array_intersect(self::values(), $selected)) === count(self::values())) {
            return 'Both';
        }

        return implode(', ', array_map(
            static fn (string $value) => self::from($value)->label(),
            $selected
        ));
    }

    /**
     * Values to pre-check on the PR form (local + international when both apply).
     *
     * @return list<string>
     */
    public static function formSelectedValues(mixed $stored): array
    {
        return self::selectedValues($stored);
    }
}
