<?php

namespace App\Http\Requests\Procurement\VendorQuotations;

use App\Enums\Procurement\VendorQuotations\QuotationCompliance;
use App\Enums\Procurement\VendorQuotations\VendorQuotationDocumentType;
use App\Support\Procurement\VendorQuotations\VendorQuotationDeclarations;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

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
            'delivery_terms' => ['nullable', 'string', 'max:2000'],
            'quotation_valid_until' => ['nullable', 'date'],
            'after_sales_service' => ['nullable', 'string', 'max:5000'],
            'delivery_charges' => ['nullable', 'numeric', 'min:0'],
            'installation_charges' => ['nullable', 'numeric', 'min:0'],
            'total_discount' => ['nullable', 'numeric', 'min:0'],
            'price_includes_delivery' => ['nullable', 'boolean'],
            'price_includes_installation' => ['nullable', 'boolean'],
            'notes' => ['nullable', 'string', 'max:10000'],
            'vendor_rep_name' => ['nullable', 'string', 'max:255'],
            'vendor_rep_job_title' => ['nullable', 'string', 'max:255'],
            'vendor_rep_email' => ['nullable', 'string', 'email', 'max:255'],
            'vendor_rep_phone' => ['nullable', 'string', 'max:50'],
            'vendor_rep_signature' => ['nullable', 'string', 'max:255'],
            'vendor_rep_signature_file' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'remove_signature' => ['nullable', 'boolean'],
            'vendor_rep_signed_at' => ['nullable', 'date'],
            'vendor_declarations' => ['required', 'array', 'min:1'],
            'vendor_declarations.*' => ['string', Rule::in(VendorQuotationDeclarations::keys())],
            'items' => ['required', 'array', 'min:1'],
            'items.*.rfq_item_id' => [
                'required',
                'integer',
                Rule::exists('rfq_items', 'id')->where('rfq_id', $rfq?->id),
            ],
            'items.*.quantity_quoted' => ['nullable', 'numeric', 'min:0'],
            'items.*.compliance' => ['nullable', 'string', Rule::in(QuotationCompliance::values())],
            'items.*.alternative_if_no' => ['nullable', 'string', 'max:255'],
            'items.*.item_description_if_no' => ['nullable', 'string', 'max:5000'],
            'items.*.brand' => ['nullable', 'string', 'max:255'],
            'items.*.model' => ['nullable', 'string', 'max:255'],
            'items.*.country_of_origin' => ['nullable', 'string', 'max:255'],
            'items.*.brand_origin' => ['nullable', 'string', 'max:255'],
            'items.*.unit_price' => ['nullable', 'numeric', 'min:0'],
            'items.*.currency' => ['nullable', 'string', 'max:10'],
            'items.*.total_price' => ['nullable', 'numeric', 'min:0'],
            'items.*.discount' => ['nullable', 'numeric', 'min:0'],
            'items.*.tax_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'items.*.tax' => ['nullable', 'numeric', 'min:0'],
            'items.*.delivery_charges' => ['nullable', 'numeric', 'min:0'],
            'items.*.installation' => ['nullable', 'numeric', 'min:0'],
            'items.*.lead_time' => ['nullable', 'string', 'max:255'],
            'items.*.warranty' => ['nullable', 'string', 'max:255'],
            'items.*.remarks' => ['nullable', 'string', 'max:5000'],
            'remove_documents' => ['nullable', 'array'],
            'remove_documents.*' => ['string', Rule::in(VendorQuotationDocumentType::values())],
            ...$this->documentUploadRules(),
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            $confirmed = VendorQuotationDeclarations::normalize($this->input('vendor_declarations', []));
            $missing = array_diff(VendorQuotationDeclarations::keys(), $confirmed);

            if ($missing !== []) {
                $validator->errors()->add(
                    'vendor_declarations',
                    'All 7 vendor declarations must be confirmed before saving this quotation.',
                );
            }

            $items = $this->input('items', []);
            if (! is_array($items)) {
                return;
            }

            foreach ($items as $index => $row) {
                if (! is_array($row)) {
                    continue;
                }

                $compliance = trim((string) ($row['compliance'] ?? ''));
                if ($compliance === '') {
                    continue;
                }

                if (in_array($compliance, [QuotationCompliance::PartialCompliance->value, QuotationCompliance::NotCompliant->value], true)) {
                    if (trim((string) ($row['alternative_if_no'] ?? '')) === '') {
                        $validator->errors()->add(
                            "items.{$index}.alternative_if_no",
                            'Alternative item is required when compliance is partial or not compliant.',
                        );
                    }

                    if (trim((string) ($row['item_description_if_no'] ?? '')) === '') {
                        $validator->errors()->add(
                            "items.{$index}.item_description_if_no",
                            'Item description is required when compliance is partial or not compliant.',
                        );
                    }
                }

                $unitPrice = $row['unit_price'] ?? null;
                if ($unitPrice === null || $unitPrice === '' || (float) $unitPrice <= 0) {
                    $validator->errors()->add(
                        "items.{$index}.unit_price",
                        'Unit price is required when a compliance status is selected.',
                    );
                }
            }
        });
    }

    /**
     * @return array<string, mixed>
     */
    private function documentUploadRules(): array
    {
        $rules = [];

        foreach (VendorQuotationDocumentType::cases() as $type) {
            $rules[$type->inputName()] = ['nullable', 'file', 'max:10240'];
        }

        return $rules;
    }
}
