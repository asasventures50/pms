<?php

namespace App\Http\Requests\Procurement\Rfqs;

use Illuminate\Foundation\Http\FormRequest;

class StorePublicVendorQuotationExcelRequest extends FormRequest
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
        return [
            'excel_file' => ['required', 'file', 'max:10240', 'mimes:xlsx,xls'],
            'attachment' => ['nullable', 'file', 'max:10240', 'mimes:pdf,jpg,jpeg,png,webp,doc,docx,xls,xlsx'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'excel_file.required' => __('vendor_quotation_invite.excel.errors.file_required'),
            'excel_file.mimes' => __('vendor_quotation_invite.excel.errors.file_mimes'),
            'excel_file.max' => __('vendor_quotation_invite.excel.errors.file_max'),
        ];
    }
}
