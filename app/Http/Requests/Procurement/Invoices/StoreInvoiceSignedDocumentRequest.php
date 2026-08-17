<?php

namespace App\Http\Requests\Procurement\Invoices;

use Illuminate\Foundation\Http\FormRequest;

class StoreInvoiceSignedDocumentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasPermission('invoices.create') ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'document' => ['required', 'file', 'mimes:jpg,jpeg,png,webp,pdf', 'max:2048'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'document.required' => 'Choose the signed invoice file.',
            'document.mimes' => 'Signed invoice must be a JPG, PNG, WEBP, or PDF file.',
            'document.max' => 'Signed invoice may not be larger than 2 MB.',
        ];
    }
}
