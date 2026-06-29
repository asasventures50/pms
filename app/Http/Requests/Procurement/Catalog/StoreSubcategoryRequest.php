<?php

namespace App\Http\Requests\Procurement\Catalog;

use App\Support\CatalogIdentifiers;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreSubcategoryRequest extends FormRequest
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
        $categoryId = $this->input('category_id');

        return [
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'name_en' => [
                'required',
                'string',
                'max:255',
                CatalogIdentifiers::uniqueSubcategoryNameEn((int) $categoryId),
            ],
            'name_ar' => ['required', 'string', 'max:255'],
            'slug' => [
                'required',
                'string',
                'max:255',
                CatalogIdentifiers::uniqueSubcategorySlug((int) $categoryId),
            ],
            'description' => ['nullable', 'string'],
            'status' => ['nullable', 'string', 'max:50'],
        ];
    }
}
