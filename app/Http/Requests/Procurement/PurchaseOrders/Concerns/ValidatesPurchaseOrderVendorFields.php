<?php

namespace App\Http\Requests\Procurement\PurchaseOrders\Concerns;

trait ValidatesPurchaseOrderVendorFields
{
    /**
     * @return array<string, mixed>
     */
    protected function vendorFieldRules(bool $sometimes = false): array
    {
        $lead = $sometimes ? ['sometimes'] : [];

        $rules = [
            'vendor_company_name' => ['nullable', 'string', 'max:255'],
            'vendor_email' => ['nullable', 'string', 'email', 'max:255'],
            'vendor_phone' => ['nullable', 'string', 'max:50'],
            'vendor_whatsapp' => ['nullable', 'string', 'max:50'],
            'vendor_primary_contact_position' => ['nullable', 'string', 'max:255'],
            'vendor_classification' => ['nullable', 'string', 'max:5000'],
        ];

        if ($lead === []) {
            return $rules;
        }

        return array_map(fn (array $fieldRules) => array_merge($lead, $fieldRules), $rules);
    }
}
