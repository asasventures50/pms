<?php

namespace App\Http\Requests\Procurement\Catalog;

use App\Models\Procurement\Vendors\Subcategory;
use App\Support\CatalogIdentifiers;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateSubcategoryRequest extends FormRequest
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
        /** @var Subcategory|null $subcategory */
        $subcategory = $this->route('subcategory');
        $categoryId = (int) ($this->input('category_id') ?? $subcategory?->category_id ?? 0);

        return [
            'category_id' => ['sometimes', 'required', 'integer', 'exists:categories,id'],
            'name_en' => [
                'sometimes',
                'required',
                'string',
                'max:255',
                CatalogIdentifiers::uniqueSubcategoryNameEn($categoryId, $subcategory?->getKey()),
            ],
            'name_ar' => ['sometimes', 'required', 'string', 'max:255'],
            'slug' => [
                'sometimes',
                'required',
                'string',
                'max:255',
                CatalogIdentifiers::uniqueSubcategorySlug($categoryId, $subcategory?->getKey()),
            ],
            'description' => ['nullable', 'string'],
            'status' => ['nullable', 'string', 'max:50'],
        ];
    }
}
