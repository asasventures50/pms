<?php

namespace App\Enums\Procurement\ProcurementRequests;

enum CompliancePrequalificationLevel: string
{
    case A = 'a';
    case B = 'b';
    case C = 'c';
    case D = 'd';

    public function label(): string
    {
        return strtoupper($this->value);
    }

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_map(static fn (self $case) => $case->value, self::cases());
    }

    public static function display(?string $value): string
    {
        if ($value === null || $value === '') {
            return '—';
        }

        return self::tryFrom($value)?->label() ?? strtoupper($value);
    }
}
