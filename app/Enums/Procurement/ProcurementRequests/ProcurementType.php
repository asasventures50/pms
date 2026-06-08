<?php

namespace App\Enums\Procurement\ProcurementRequests;

enum ProcurementType: string
{
    case Purchase = 'purchase';
    case Rental = 'rental';

    public function label(): string
    {
        return match ($this) {
            self::Purchase => 'Purchase',
            self::Rental => 'Rental',
        };
    }

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_map(static fn (self $case) => $case->value, self::cases());
    }
}
