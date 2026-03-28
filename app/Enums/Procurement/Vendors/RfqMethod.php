<?php

namespace App\Enums\Procurement\Vendors;

enum RfqMethod: string
{
    case Email = 'email';
    case Portal = 'portal';
    case Whatsapp = 'whatsapp';
    case Phone = 'phone';
    case Mixed = 'mixed';

    /**
     * @return array<int, string>
     */
    public static function values(): array
    {
        return array_map(static fn (self $case) => $case->value, self::cases());
    }
}
