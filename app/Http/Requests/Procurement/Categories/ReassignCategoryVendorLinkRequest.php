<?php

namespace App\Http\Requests\Procurement\Categories;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ReassignCategoryVendorLinkRequest extends FormRequest
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
            'target_category_id' => ['required', 'integer', 'exists:categories,id'],
            'target_subcategory_id' => ['nullable', 'integer', 'exists:subcategories,id'],
            'update_brochures' => ['sometimes', 'boolean'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $subcategoryId = $this->input('target_subcategory_id');
        if ($subcategoryId === '' || $subcategoryId === '0') {
            $this->merge(['target_subcategory_id' => null]);
        }
    }
}
