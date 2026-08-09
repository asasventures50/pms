<?php

namespace App\Http\Requests\Procurement\Rfqs;

use App\Enums\Procurement\Rfqs\RfqVendorQuotationInviteLocale;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreRfqVendorQuotationInviteRequest extends FormRequest
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
        return [
            'vendor_id' => ['required', 'integer', Rule::exists('vendors', 'id')->whereNull('deleted_at')],
            'ui_locale' => ['required', 'string', Rule::in(RfqVendorQuotationInviteLocale::values())],
            'include_terms' => ['sometimes', 'boolean'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'include_terms' => $this->boolean('include_terms'),
        ]);
    }
}
