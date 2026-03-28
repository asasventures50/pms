<?php

namespace App\Enums\Procurement\Vendors;

enum VendorLanguage: string
{
    case Ar = 'ar';
    case En = 'en';

    /**
     * @return array<int, string>
     */
    public static function values(): array
    {
        return array_map(static fn (self $case) => $case->value, self::cases());
    }
}
