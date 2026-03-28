<?php

namespace App\Http\Requests\Procurement\Categories;

use Illuminate\Foundation\Http\FormRequest;

class ImportCategoriesRequest extends FormRequest
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
            'file' => ['required', 'file', 'mimes:xlsx,xls,csv', 'max:20480'],
        ];
    }
}
