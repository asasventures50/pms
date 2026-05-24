<?php

namespace App\Http\Requests\Procurement\Rfqs;

use App\Support\Procurement\ProcurementScopeType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreRfqGeneralTermRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasPermission('rfq-terms.manage') ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'scope_type' => ['nullable', 'string', Rule::in(ProcurementScopeType::values())],
            'body_ar' => ['required_without:body_en', 'nullable', 'string', 'max:5000'],
            'body_en' => ['required_without:body_ar', 'nullable', 'string', 'max:5000'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:65535'],
            'is_active' => ['required', 'boolean'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $scopeType = $this->input('scope_type');
        $this->merge([
            'is_active' => $this->boolean('is_active'),
            'scope_type' => $scopeType === '' || $scopeType === null ? null : $scopeType,
            'body_ar' => $this->filled('body_ar') ? trim((string) $this->input('body_ar')) : null,
            'body_en' => $this->filled('body_en') ? trim((string) $this->input('body_en')) : null,
        ]);
    }
}
