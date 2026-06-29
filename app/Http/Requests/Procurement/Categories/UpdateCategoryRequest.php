<?php

namespace App\Http\Requests\Procurement\Categories;

use App\Models\Procurement\Vendors\Category;
use App\Models\Procurement\Vendors\Subcategory;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UpdateCategoryRequest extends FormRequest
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

            if ($nameEn === '' && $nameAr === '' && $slug === '') {
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
        /** @var Category $category */
        $category = $this->route('category');

        return [
            'name_en' => ['required', 'string', 'max:255'],
            'name_ar' => ['required', 'string', 'max:255'],
            'slug' => [
                'required',
                'string',
                'max:255',
                'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
                Rule::unique('categories', 'slug')->ignore($category->getKey()),
            ],
            'status' => ['required', 'string', Rule::in(['active', 'inactive'])],

            'subcategories' => ['nullable', 'array'],
            'subcategories.*.id' => ['nullable', 'integer', 'exists:subcategories,id'],
            'subcategories.*.target_category_id' => ['nullable', 'integer', 'exists:categories,id'],
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
            'subcategories.*.target_category_id' => 'parent category',
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
            /** @var Category $category */
            $category = $this->route('category');
            $currentCategoryId = (int) $category->getKey();
            $subs = (array) $this->input('subcategories', []);

            $slugs = [];
            $names = [];

            foreach ($subs as $index => $sub) {
                if (! is_array($sub)) {
                    continue;
                }

                $slug = Str::slug((string) ($sub['slug'] ?? ''));
                $nameEn = trim((string) ($sub['name_en'] ?? ''));
                $id = isset($sub['id']) ? (int) $sub['id'] : null;
                $targetCategoryId = isset($sub['target_category_id']) && $sub['target_category_id'] !== ''
                    ? (int) $sub['target_category_id']
                    : $currentCategoryId;
                $isMove = $targetCategoryId !== $currentCategoryId;

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

                if ($isMove && ! $id) {
                    $validator->errors()->add(
                        "subcategories.$index.target_category_id",
                        'Save the subcategory under this category before moving it to another parent.'
                    );

                    continue;
                }

                if ($id) {
                    $belongs = Subcategory::query()
                        ->whereKey($id)
                        ->where('category_id', $currentCategoryId)
                        ->exists();

                    if (! $belongs) {
                        $validator->errors()->add(
                            "subcategories.$index.id",
                            'The subcategory does not belong to this category.'
                        );
                    }
                }

                if ($isMove && $targetCategoryId === $currentCategoryId) {
                    continue;
                }

                if ($isMove) {
                    if ($slug === '') {
                        continue;
                    }

                    $slugQuery = Subcategory::query()
                        ->where('category_id', $targetCategoryId)
                        ->where('slug', $slug);

                    if ($id) {
                        $slugQuery->whereKeyNot($id);
                    }

                    if ($slugQuery->exists()) {
                        $validator->errors()->add(
                            "subcategories.$index.slug",
                            'This subcategory slug is already used in the target category.'
                        );
                    }

                    if ($nameEn === '') {
                        continue;
                    }

                    $nameQuery = Subcategory::query()
                        ->where('category_id', $targetCategoryId)
                        ->where('name_en', $nameEn);

                    if ($id) {
                        $nameQuery->whereKeyNot($id);
                    }

                    if ($nameQuery->exists()) {
                        $validator->errors()->add(
                            "subcategories.$index.name_en",
                            'This subcategory English name is already used in the target category.'
                        );
                    }

                    continue;
                }

                if ($slug === '') {
                    continue;
                }

                $slugQuery = Subcategory::query()
                    ->where('category_id', $currentCategoryId)
                    ->where('slug', $slug);

                if ($id) {
                    $slugQuery->whereKeyNot($id);
                }

                if ($slugQuery->exists()) {
                    $validator->errors()->add(
                        "subcategories.$index.slug",
                        'This subcategory slug is already used for this category.'
                    );
                }

                if ($nameEn === '') {
                    continue;
                }

                $nameQuery = Subcategory::query()
                    ->where('category_id', $currentCategoryId)
                    ->where('name_en', $nameEn);

                if ($id) {
                    $nameQuery->whereKeyNot($id);
                }

                if ($nameQuery->exists()) {
                    $validator->errors()->add(
                        "subcategories.$index.name_en",
                        'This subcategory English name is already used for this category.'
                    );
                }
            }
        });
    }
}
