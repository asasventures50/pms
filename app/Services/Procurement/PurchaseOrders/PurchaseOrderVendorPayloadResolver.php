<?php

namespace App\Services\Procurement\PurchaseOrders;

use App\Models\Procurement\Vendors\Vendor;

class PurchaseOrderVendorPayloadResolver
{
    /**
     * Fill empty vendor detail fields from the linked vendor record before persisting.
     *
     * @param  array<string, mixed>  $header
     * @return array<string, mixed>
     */
    public static function mergeMissingFromVendor(array $header): array
    {
        $vendorId = $header['vendor_id'] ?? null;
        if (! $vendorId) {
            return $header;
        }

        $vendor = Vendor::query()->find($vendorId);
        if (! $vendor) {
            return $header;
        }

        $snapshot = VendorPurchaseOrderSnapshot::fromVendor($vendor);

        foreach ([
            'vendor_company_name',
            'vendor_email',
            'vendor_phone',
            'vendor_whatsapp',
            'vendor_primary_contact_position',
            'vendor_classification',
        ] as $key) {
            $current = trim((string) ($header[$key] ?? ''));
            $fromVendor = trim((string) ($snapshot[$key] ?? ''));

            if ($current === '' && $fromVendor !== '') {
                $header[$key] = $fromVendor;
            }
        }

        return $header;
    }
}
