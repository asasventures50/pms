<?php

namespace App\Enums\Procurement\Vendors;

enum CoverageType: string
{
    case Local = 'local';
    case Regional = 'regional';
    case International = 'international';

    /**
     * @return array<int, string>
     */
    public static function values(): array
    {
        return array_map(static fn (self $case) => $case->value, self::cases());
    }
}
