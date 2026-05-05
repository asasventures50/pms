<?php

namespace App\Enums\Procurement\PurchaseOrders;

enum PurchaseOrderStatus: string
{
    case Draft      = 'draft';
    case Ordered    = 'ordered';
    case Shipped    = 'shipped';
    case Delivered  = 'delivered';
    case Cancelled  = 'cancelled';

    /**
     * @return array<int, string>
     */
    public static function values(): array
    {
        return array_map(static fn (self $case) => $case->value, self::cases());
    }
}
