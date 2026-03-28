<?php

namespace App\Enums\Procurement\Vendors;

enum CompanyType: string
{
    case Individual = 'individual';
    case Company = 'company';

    /**
     * @return array<int, string>
     */
    public static function values(): array
    {
        return array_map(static fn (self $case) => $case->value, self::cases());
    }
}
