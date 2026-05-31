<?php

namespace App\Http\Requests\Procurement\VendorQuotations;

use App\Enums\Procurement\VendorQuotations\QuotationCompliance;
use App\Enums\Procurement\VendorQuotations\VendorQuotationDocumentType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreVendorQuotationRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return $user !== null && (
            $user->hasPermission('vendor-quotations.create')
            || $user->hasPermission('rfqs.update')
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $rfq = $this->route('rfq');

        return [
            'quotation_number' => [
                'nullable',
                'string',
                'max:120',
                Rule::unique('vendor_quotations', 'quotation_number'),
            ],
            'vendor_id' => ['nullable', 'integer', Rule::exists('vendors', 'id')->whereNull('deleted_at')],
            'vendor_company_name' => ['required', 'string', 'max:255'],
            'vendor_contact' => ['nullable', 'string', 'max:255'],
            'vendor_email' => ['nullable', 'string', 'email', 'max:255'],
            'vendor_phone' => ['nullable', 'string', 'max:50'],
            'vendor_address' => ['nullable', 'string', 'max:2000'],
            'payment_method' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:10000'],
            'vendor_rep_name' => ['nullable', 'string', 'max:255'],
            'vendor_rep_signature' => ['nullable', 'string', 'max:255'],
            'vendor_rep_signed_at' => ['nullable', 'date'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.rfq_item_id' => [
                'required',
                'integer',
                Rule::exists('rfq_items', 'id')->where('rfq_id', $rfq?->id),
            ],
            'items.*.compliance' => ['nullable', 'string', Rule::in(QuotationCompliance::values())],
            'items.*.alternative_if_no' => ['nullable', 'string', 'max:255'],
            'items.*.item_description_if_no' => ['nullable', 'string', 'max:5000'],
            'items.*.brand_origin' => ['nullable', 'string', 'max:255'],
            'items.*.unit_price' => ['nullable', 'numeric', 'min:0'],
            'items.*.currency' => ['nullable', 'string', 'max:10'],
            'items.*.total_price' => ['nullable', 'numeric', 'min:0'],
            'items.*.tax' => ['nullable', 'numeric', 'min:0'],
            'items.*.lead_time' => ['nullable', 'string', 'max:255'],
            'items.*.warranty' => ['nullable', 'string', 'max:255'],
            'document_commercial_registration' => ['nullable', 'file', 'max:10240'],
            'document_company_profile' => ['nullable', 'file', 'max:10240'],
            'document_technical_datasheet' => ['nullable', 'file', 'max:10240'],
            'remove_documents' => ['nullable', 'array'],
            'remove_documents.*' => ['string', Rule::in(VendorQuotationDocumentType::values())],
        ];
    }
}
