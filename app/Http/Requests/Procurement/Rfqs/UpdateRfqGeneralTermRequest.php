<?php

namespace App\Http\Requests\Procurement\Rfqs;

use App\Http\Requests\Procurement\Rfqs\Concerns\NormalizesRfqGeneralTermScopeTypes;
use App\Support\Procurement\ProcurementScopeType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateRfqGeneralTermRequest extends FormRequest
{
    use NormalizesRfqGeneralTermScopeTypes;

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
            'scope_types' => ['nullable', 'array'],
            'scope_types.*' => ['required', 'string', Rule::in(ProcurementScopeType::values())],
            'key_ar' => ['nullable', 'string', 'max:255'],
            'key_en' => ['nullable', 'string', 'max:255'],
            'value_ar' => ['nullable', 'string', 'max:5000'],
            'value_en' => ['nullable', 'string', 'max:5000'],
            'body_ar' => ['nullable', 'string', 'max:5000'],
            'body_en' => ['nullable', 'string', 'max:5000'],
            'sort_order' => ['nullable', 'numeric', 'decimal:0,2', 'min:0', 'max:99999.99'],
            'is_active' => ['required', 'boolean'],
        ];
    }
}
