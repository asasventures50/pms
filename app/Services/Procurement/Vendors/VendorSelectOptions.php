<?php

namespace App\Services\Procurement\Vendors;

use App\Models\Procurement\Vendors\Vendor;

class VendorSelectOptions
{
    /**
     * @return list<array{id: int, label: string}>
     */
    public static function all(): array
    {
        return Vendor::query()
            ->orderBy('name')
            ->get(['id', 'vendor_code', 'name'])
            ->map(fn (Vendor $vendor) => [
                'id' => $vendor->id,
                'label' => trim($vendor->vendor_code.' — '.$vendor->name),
            ])
            ->values()
            ->all();
    }
}
