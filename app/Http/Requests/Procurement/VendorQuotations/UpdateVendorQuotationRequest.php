<?php

namespace App\Http\Requests\Procurement\VendorQuotations;

use Illuminate\Validation\Rule;

class UpdateVendorQuotationRequest extends StoreVendorQuotationRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return $user !== null && (
            $user->hasPermission('vendor-quotations.update')
            || $user->hasPermission('rfqs.update')
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $rules = parent::rules();
        $quotation = $this->route('quotation');

        $rules['quotation_number'] = [
            'nullable',
            'string',
            'max:120',
            Rule::unique('vendor_quotations', 'quotation_number')->ignore($quotation?->id),
        ];

        return $rules;
    }
}
