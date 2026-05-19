<?php

namespace App\Enums\Procurement\Rfqs;

enum RfqStatus: string
{
    case Draft = 'draft';
    case Issued = 'issued';
    case Closed = 'closed';
    case Cancelled = 'cancelled';

    /**
     * @return array<int, string>
     */
    public static function values(): array
    {
        return array_map(static fn (self $case) => $case->value, self::cases());
    }
}
