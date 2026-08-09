<?php

namespace App\Http\Requests\Procurement\Rfqs;

use App\Models\Procurement\Rfqs\RfqVendorQuotationInvite;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StorePublicVendorQuotationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        /** @var RfqVendorQuotationInvite $invite */
        $invite = $this->route('invite');

        return [
            'vendor_rep_name' => ['required', 'string', 'max:255'],
            'vendor_rep_email' => ['nullable', 'email', 'max:255'],
            'vendor_rep_phone' => ['nullable', 'string', 'max:50'],
            'notes' => ['nullable', 'string', 'max:5000'],
            'attachment' => ['nullable', 'file', 'max:10240', 'mimes:pdf,jpg,jpeg,png,webp,doc,docx,xls,xlsx'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.rfq_item_id' => [
                'required',
                'integer',
                Rule::exists('rfq_items', 'id')->where('rfq_id', $invite->rfq_id),
            ],
            'items.*.unit_price' => ['nullable', 'numeric', 'min:0'],
            'items.*.currency' => ['nullable', 'string', 'max:10'],
            'items.*.quantity_quoted' => ['nullable', 'numeric', 'min:0'],
            'items.*.brand' => ['nullable', 'string', 'max:255'],
            'items.*.model' => ['nullable', 'string', 'max:255'],
            'items.*.discount' => ['nullable', 'numeric', 'min:0'],
            'items.*.installation' => ['nullable', 'numeric', 'min:0'],
            'items.*.delivery_charges' => ['nullable', 'numeric', 'min:0'],
            'items.*.remarks' => ['nullable', 'string', 'max:2000'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $items = $this->input('items', []);
            if (! is_array($items)) {
                return;
            }

            $hasPrice = false;
            foreach ($items as $row) {
                if (! is_array($row)) {
                    continue;
                }
                if ((float) ($row['unit_price'] ?? 0) > 0) {
                    $hasPrice = true;
                    break;
                }
            }

            if (! $hasPrice) {
                $validator->errors()->add('items', __('vendor_quotation_invite.errors.price_required'));
            }

            foreach ($items as $index => $row) {
                if (! is_array($row)) {
                    continue;
                }

                foreach (['unit_price', 'quantity_quoted', 'discount', 'installation', 'delivery_charges'] as $key) {
                    if (! array_key_exists($key, $row) || $row[$key] === '' || $row[$key] === null) {
                        continue;
                    }

                    if ((float) $row[$key] < 0) {
                        $validator->errors()->add(
                            "items.{$index}.{$key}",
                            __('vendor_quotation_invite.errors.no_negative')
                        );
                    }
                }
            }
        });
    }
}