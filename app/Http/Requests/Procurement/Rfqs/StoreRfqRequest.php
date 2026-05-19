<?php

namespace App\Http\Requests\Procurement\Rfqs;

use App\Enums\Procurement\Rfqs\RfqStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreRfqRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasPermission('rfqs.create') ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'rfq_number' => ['nullable', 'string', 'max:100', Rule::unique('rfqs', 'rfq_number')],
            'issue_date' => ['nullable', 'date'],
            'submission_deadline' => ['nullable', 'date', 'after_or_equal:issue_date'],
            'vendor_id' => ['nullable', 'integer', Rule::exists('vendors', 'id')->whereNull('deleted_at')],
            'vendor_company_name' => ['nullable', 'string', 'max:255'],
            'vendor_contact' => ['nullable', 'string', 'max:255'],
            'vendor_email' => ['nullable', 'string', 'email', 'max:255'],
            'vendor_phone' => ['nullable', 'string', 'max:50'],
            'vendor_address' => ['nullable', 'string', 'max:2000'],
            'payment_method' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', 'string', Rule::in(RfqStatus::values())],
            'vendor_rep_name' => ['nullable', 'string', 'max:255'],
            'vendor_rep_signature' => ['nullable', 'string', 'max:255'],
            'vendor_rep_signed_at' => ['nullable', 'date'],
            'vendor_company_stamp' => ['nullable', 'string', 'max:255'],
            'items' => ['required', 'array', 'min:1'],
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
}
