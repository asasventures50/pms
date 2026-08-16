<?php

namespace App\Http\Requests\Procurement\Categories;

use Illuminate\Foundation\Http\FormRequest;

class ApplyCategoryRebuildRequest extends FormRequest
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
            'confirm' => ['accepted'],
            'retire_mapped' => ['sometimes', 'boolean'],
            'category_map' => ['nullable', 'array'],
            'category_map.*' => ['nullable', 'string', 'max:255'],
            'subcategory_map' => ['nullable', 'array'],
            'subcategory_map.*' => ['nullable', 'string', 'max:255'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'confirm.accepted' => 'Confirm that PR, vendor, brochure, and quick-receipt records will move to the mapped classifications.',
        ];
    }
}
