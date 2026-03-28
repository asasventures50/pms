<?php

namespace App\Http\Requests\Procurement\Categories;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $subs = (array) $this->input('subcategories', []);
        $filtered = [];

        foreach ($subs as $row) {
            if (! is_array($row)) {
                continue;
            }

            $nameEn = isset($row['name_en']) ? trim((string) $row['name_en']) : '';
            $nameAr = isset($row['name_ar']) ? trim((string) $row['name_ar']) : '';
            $slug = isset($row['slug']) ? trim((string) $row['slug']) : '';
            $status = isset($row['status']) ? trim((string) $row['status']) : '';

            if ($nameEn === '' && $nameAr === '' && $slug === '' && $status === '') {
                continue;
            }

            if (isset($row['slug']) && is_string($row['slug'])) {
                $row['slug'] = Str::slug($row['slug']);
            }

            $filtered[] = $row;
        }

        $this->merge(['subcategories' => array_values($filtered)]);

        $slug = $this->input('slug');
        if (is_string($slug)) {
            $this->merge(['slug' => Str::slug($slug)]);
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name_en' => ['required', 'string', 'max:255'],
            'name_ar' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', Rule::unique('categories', 'slug')],
            'status' => ['required', 'string', Rule::in(['active', 'inactive'])],

            'subcategories' => ['nullable', 'array'],
            'subcategories.*.name_en' => ['required', 'string', 'max:255'],
            'subcategories.*.name_ar' => ['required', 'string', 'max:255'],
            'subcategories.*.slug' => ['required', 'string', 'max:255'],
            'subcategories.*.status' => ['required', 'string', Rule::in(['active', 'inactive'])],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $subs = (array) $this->input('subcategories', []);
            $slugs = [];
            $names = [];

            foreach ($subs as $index => $sub) {
                if (! is_array($sub)) {
                    continue;
                }

                $slug = Str::slug((string) ($sub['slug'] ?? ''));
                $nameEn = trim((string) ($sub['name_en'] ?? ''));

                if ($slug !== '') {
                    if (isset($slugs[$slug])) {
                        $validator->errors()->add(
                            "subcategories.$index.slug",
                            'Duplicate subcategory slug in this form.'
                        );
                    }
                    $slugs[$slug] = true;
                }

                if ($nameEn !== '') {
                    if (isset($names[$nameEn])) {
                        $validator->errors()->add(
                            "subcategories.$index.name_en",
                            'Duplicate subcategory English name in this form.'
                        );
                    }
                    $names[$nameEn] = true;
                }
            }
        });
    }
}
