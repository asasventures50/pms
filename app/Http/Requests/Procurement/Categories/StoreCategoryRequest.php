<?php

namespace App\Http\Requests\Procurement\Categories;

use App\Support\CatalogIdentifiers;
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
            $status = isset($row['status']) ? trim((string) $row['status']) : '';

            if ($nameEn === '' && $nameAr === '' && $status === '') {
                continue;
            }

            $row['slug'] = $nameEn !== '' ? Str::slug($nameEn) : '';

            $filtered[] = $row;
        }

        $this->merge(['subcategories' => array_values($filtered)]);

        $nameEn = trim((string) $this->input('name_en', ''));
        $this->merge(['slug' => Str::slug($nameEn)]);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name_en' => ['required', 'string', 'max:255', CatalogIdentifiers::uniqueCategoryNameEn()],
            'name_ar' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/', CatalogIdentifiers::uniqueCategorySlug()],
            'status' => ['required', 'string', Rule::in(['active', 'inactive'])],

            'subcategories' => ['nullable', 'array'],
            'subcategories.*.name_en' => ['required', 'string', 'max:255'],
            'subcategories.*.name_ar' => ['required', 'string', 'max:255'],
            'subcategories.*.slug' => ['required', 'string', 'max:255', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/'],
            'subcategories.*.status' => ['required', 'string', Rule::in(['active', 'inactive'])],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'slug' => 'slug',
            'subcategories.*.slug' => 'subcategory slug',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'slug.regex' => 'The slug may only contain lowercase letters, numbers, and hyphens.',
            'subcategories.*.slug.regex' => 'Each subcategory slug may only contain lowercase letters, numbers, and hyphens.',
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
