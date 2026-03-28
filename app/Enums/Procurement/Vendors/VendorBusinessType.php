<?php

namespace App\Enums\Procurement\Vendors;

enum VendorBusinessType: string
{
    case Supplier = 'supplier';
    case Installer = 'installer';
    case Manufacturer = 'manufacturer';
    case ServiceProvider = 'service_provider';

    /**
     * @return array<int, string>
     */
    public static function values(): array
    {
        return array_map(static fn (self $case) => $case->value, self::cases());
    }
}
