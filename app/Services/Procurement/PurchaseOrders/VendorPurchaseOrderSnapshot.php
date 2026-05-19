<?php

namespace App\Services\Procurement\PurchaseOrders;

use App\Models\Procurement\Vendors\Vendor;

class VendorPurchaseOrderSnapshot
{
    /**
     * @return array{vendor_company_name: string|null, vendor_contact: string|null, vendor_email: string|null, vendor_phone: string|null, vendor_address: string|null, payment_terms: string|null}
     */
    public static function fromVendor(Vendor $vendor): array
    {
        $vendor->loadMissing('primaryLocation.country', 'primaryLocation.city');

        $location = $vendor->primaryLocation;
        $addressParts = array_filter([
            $location?->address,
            $location?->city?->name_en ?? $location?->city?->name,
            $location?->country?->name_en ?? $location?->country?->name,
        ]);

        return [
            'vendor_company_name' => $vendor->name,
            'vendor_contact' => $vendor->primary_contact_name,
            'vendor_email' => $vendor->primary_contact_email ?: $vendor->email,
            'vendor_phone' => $vendor->primary_contact_phone ?: $vendor->phone,
            'vendor_address' => $addressParts !== [] ? implode(', ', $addressParts) : null,
            'payment_terms' => $vendor->payment_terms,
        ];
    }
}
