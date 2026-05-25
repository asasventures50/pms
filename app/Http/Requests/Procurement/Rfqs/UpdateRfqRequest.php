<?php

namespace App\Http\Requests\Procurement\Rfqs;

use App\Enums\Procurement\Rfqs\RfqStatus;
use App\Enums\Procurement\Rfqs\RfqTermsLocale;
use App\Http\Requests\Procurement\Rfqs\Concerns\ValidatesRfqProcurementRequestItems;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UpdateRfqRequest extends FormRequest
{
    use ValidatesRfqProcurementRequestItems;

    public function authorize(): bool
    {
        return $this->user()?->hasPermission('rfqs.update') ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'rfq_number' => ['sometimes', 'nullable', 'string', 'max:100', Rule::unique('rfqs', 'rfq_number')->ignore($this->route('rfq'))],
            'issue_date' => ['sometimes', 'nullable', 'date'],
            'submission_deadline' => ['sometimes', 'nullable', 'date'],
            'vendor_id' => ['sometimes', 'nullable', 'integer', Rule::exists('vendors', 'id')->whereNull('deleted_at')],
            'vendor_company_name' => ['sometimes', 'nullable', 'string', 'max:255'],
            'vendor_contact' => ['sometimes', 'nullable', 'string', 'max:255'],
            'vendor_email' => ['sometimes', 'nullable', 'string', 'email', 'max:255'],
            'vendor_phone' => ['sometimes', 'nullable', 'string', 'max:50'],
            'vendor_address' => ['sometimes', 'nullable', 'string', 'max:2000'],
            'payment_method' => ['sometimes', 'nullable', 'string', 'max:255'],
            'status' => ['sometimes', 'string', Rule::in(RfqStatus::values())],
            'vendor_rep_name' => ['sometimes', 'nullable', 'string', 'max:255'],
            'vendor_rep_signature' => ['sometimes', 'nullable', 'string', 'max:255'],
            'vendor_rep_signed_at' => ['sometimes', 'nullable', 'date'],
            'vendor_company_stamp' => ['sometimes', 'nullable', 'string', 'max:255'],
            'terms_locale' => ['sometimes', 'nullable', 'string', Rule::in(RfqTermsLocale::values())],
            'terms_custom' => ['nullable', 'array'],
            'terms_custom.*' => ['nullable', 'string', 'max:5000'],
            'payment_terms' => ['nullable', 'array'],
            'payment_terms.*' => ['nullable', 'string', 'max:5000'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.procurement_request_item_id' => ['required', 'integer', Rule::exists('procurement_request_items', 'id')],
            'items.*.item' => ['nullable', 'string', 'max:100'],
            'items.*.description' => ['required', 'string', 'max:5000'],
            'items.*.quantity' => ['required', 'numeric', 'min:0'],
            'items.*.unit' => ['nullable', 'string', 'max:50'],
            'items.*.request_lead_time' => ['nullable', 'string', 'max:255'],
            'items.*.compliance' => ['nullable', 'string', 'max:255'],
            'items.*.unit_price' => ['nullable', 'numeric', 'min:0'],
            'items.*.quote_lead_time' => ['nullable', 'string', 'max:255'],
            'items.*.warranty' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(fn (Validator $validator) => $this->validateRfqProcurementRequestItems($validator));
    }
}
