<?php

namespace App\Enums\Procurement\ScheduleOfWorks;

use App\Support\Procurement\ProcurementScopeType;

enum ScheduleOfWorkScope: string
{
    case Global = 'global';
    case Support = 'support';
    case Constructor = 'constructor';
    case Studies = 'studies';

    public function labelEn(): string
    {
        return match ($this) {
            self::Global => 'Global',
            self::Support => 'Support',
            self::Constructor => 'Constructor',
            self::Studies => 'Studies',
        };
    }

    public function labelAr(): string
    {
        return match ($this) {
            self::Global => 'عام',
            self::Support => 'دعم',
            self::Constructor => 'مقاول',
            self::Studies => 'دراسات',
        };
    }

    /**
     * Maps SOW scope selections to RFQ general-term scope types (PO print uses the same library).
     *
     * @return list<string>
     */
    public function rfqTermsScopeTypes(): array
    {
        return match ($this) {
            self::Global => [],
            self::Support => [ProcurementScopeType::Supplier],
            self::Constructor => [ProcurementScopeType::Contractor],
            self::Studies => [ProcurementScopeType::Studies],
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
     * @param  mixed  $value
     * @return list<string>
     */
    public static function selectedValues(mixed $value): array
    {
        if (! is_array($value)) {
            return [];
        }

        $allowed = array_flip(self::values());
        $selected = [];

        foreach ($value as $candidate) {
            $normalized = strtolower(trim((string) $candidate));
            if ($normalized !== '' && isset($allowed[$normalized])) {
                $selected[$normalized] = true;
            }
        }

        $ordered = [];
        foreach (self::cases() as $case) {
            if (isset($selected[$case->value])) {
                $ordered[] = $case->value;
            }
        }

        return $ordered;
    }

    /**
     * @param  mixed  $value
     * @return list<string>|null
     */
    public static function encode(mixed $value): ?array
    {
        $selected = self::selectedValues($value);

        return $selected === [] ? null : $selected;
    }

    public static function labelFor(string $value, bool $arabic = false): string
    {
        $case = self::tryFrom($value);

        if ($case === null) {
            return $value;
        }

        return $arabic ? $case->labelAr() : $case->labelEn();
    }

    /**
     * @param  mixed  $stored
     */
    public static function display(mixed $stored, bool $arabic = false): string
    {
        $selected = is_array($stored) ? self::selectedValues($stored) : self::selectedValues($stored ?? []);

        if ($selected === []) {
            return '';
        }

        return implode(', ', array_map(
            static fn (string $value) => self::labelFor($value, $arabic),
            $selected,
        ));
    }

    /**
     * Maps procurement request scope type keys to SOW checkbox values.
     *
     * @param  list<string>  $procurementScopeTypeKeys  {@see ProcurementScopeType} values
     * @return list<string>
     */
    public static function fromProcurementScopeTypeKeys(array $procurementScopeTypeKeys, bool $includeGlobal = false): array
    {
        $map = [
            ProcurementScopeType::Contractor => self::Constructor->value,
            ProcurementScopeType::Supplier => self::Support->value,
            ProcurementScopeType::Studies => self::Studies->value,
        ];

        $selected = [];
        foreach ($procurementScopeTypeKeys as $key) {
            $normalized = trim((string) $key);
            if ($normalized !== '' && isset($map[$normalized])) {
                $selected[$map[$normalized]] = true;
            }
        }

        if ($includeGlobal) {
            $selected[self::Global->value] = true;
        }

        return self::selectedValues(array_keys($selected));
    }
}
