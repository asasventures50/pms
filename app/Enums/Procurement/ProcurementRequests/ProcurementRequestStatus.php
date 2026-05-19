<?php

namespace App\Enums\Procurement\ProcurementRequests;

enum ProcurementRequestStatus: string
{
    case Draft = 'draft';
    case Submitted = 'submitted';
    case Received = 'received';
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
