<?php

namespace App\Http\Requests\Procurement\Vendors;

use App\Enums\Procurement\Vendors\CompanyType;
use App\Enums\Procurement\Vendors\CoverageType;
use App\Enums\Procurement\Vendors\PaymentMethod;
use App\Enums\Procurement\Vendors\PricingFrequency;
use App\Enums\Procurement\Vendors\RfqMethod;
use App\Enums\Procurement\Vendors\VendorBusinessType;
use App\Enums\Procurement\Vendors\VendorLanguage;
use App\Enums\Procurement\Vendors\VendorStatus;
use App\Models\Geo\City;
use App\Models\Procurement\Vendors\Subcategory;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UpdateVendorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $vendorCode = $this->input('vendor_code');
        if ($vendorCode !== null && trim((string) $vendorCode) === '') {
            $this->merge(['vendor_code' => null]);
        }

        if ($this->has('is_brochure_available')) {
            $this->merge([
                'is_brochure_available' => $this->boolean('is_brochure_available'),
            ]);
        }

        if ($this->has('business_types_sync')) {
            $this->merge([
                'business_types' => array_values(array_filter(
                    (array) $this->input('business_types', []),
                    static fn ($v) => $v !== null && $v !== ''
                )),
            ]);
        }

        if ($this->has('rfq_method_sync')) {
            $this->merge([
                'rfq_method' => RfqMethod::normalizeRequestInput((array) $this->input('rfq_method', [])),
            ]);
        } elseif ($this->has('rfq_method')) {
            $raw = $this->input('rfq_method');
            if ($raw === null) {
                $this->merge(['rfq_method' => null]);
            } else {
                $this->merge([
                    'rfq_method' => RfqMethod::normalizeRequestInput((array) $raw),
                ]);
            }
        }

        if ($this->filled('primary_category_index')) {
            $idx = $this->input('primary_category_index');
            $categories = (array) $this->input('categories', []);
            foreach (array_keys($categories) as $i) {
                $categories[$i]['is_primary'] = (string) $i === (string) $idx;
            }
            $this->merge(['categories' => $categories]);
        }

        $categories = (array) $this->input('categories', []);
        $filledIndexes = [];
        foreach ($categories as $i => $row) {
            if (! empty($row['category_id'])) {
                $filledIndexes[] = $i;
            }
        }
        if (count($filledIndexes) === 1) {
            $only = $filledIndexes[0];
            foreach (array_keys($categories) as $i) {
                $categories[$i]['is_primary'] = (int) $i === (int) $only;
            }
            $this->merge(['categories' => $categories]);
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $vendor = $this->route('vendor');
        $vendorId = is_object($vendor) ? ($vendor->id ?? null) : $vendor;

        return [
            'vendor_code' => [
                'sometimes',
                'nullable',
                'string',
                'max:100',
                Rule::unique('vendors', 'vendor_code')->ignore($vendorId),
            ],
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'language' => ['sometimes', 'required', 'string', Rule::in(VendorLanguage::values())],
            'description' => ['nullable', 'string'],
            'notes' => ['nullable', 'string'],

            'country_id' => ['nullable', 'integer', 'exists:countries,id'],
            'city_id' => ['nullable', 'integer', 'exists:cities,id'],
            'address' => ['nullable', 'string'],

            'phone' => ['nullable', 'string', 'max:50'],
            'whatsapp' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email:rfc,dns', 'max:255'],
            'website' => ['nullable', 'string', 'max:255'],

            'primary_contact_name' => ['nullable', 'string', 'max:255'],
            'primary_contact_position' => ['nullable', 'string', 'max:255'],
            'primary_contact_phone' => ['nullable', 'string', 'max:50'],
            'primary_contact_email' => ['nullable', 'email:rfc,dns', 'max:255'],

            'secondary_contact_name' => ['nullable', 'string', 'max:255'],
            'secondary_contact_position' => ['nullable', 'string', 'max:255'],
            'secondary_contact_phone' => ['nullable', 'string', 'max:50'],
            'secondary_contact_email' => ['nullable', 'email:rfc,dns', 'max:255'],

            'rfq_method' => ['sometimes', 'nullable', 'array'],
            'rfq_method.*' => ['string', Rule::in(RfqMethod::values())],
            'pricing_frequency' => ['nullable', 'string', Rule::in(PricingFrequency::values())],
            'delivery_lead_time_days' => ['nullable', 'integer', 'min:0'],
            'execution_lead_time_days' => ['nullable', 'integer', 'min:0'],

            'payment_method' => ['nullable', 'string', Rule::in(PaymentMethod::values())],
            'payment_terms' => ['nullable', 'string'],
            'commercial_terms' => ['nullable', 'string'],
            'technical_capabilities' => ['nullable', 'string'],

            'bulletin_price_validity_days' => ['nullable', 'integer', 'min:0'],
            'currency_code' => ['nullable', 'string', 'size:3'],

            'company_type' => ['nullable', 'string', Rule::in(CompanyType::values())],
            'status' => ['sometimes', 'required', 'string', Rule::in(VendorStatus::values())],
            'coverage_type' => ['nullable', 'string', Rule::in(CoverageType::values())],

            'tax_number' => ['nullable', 'string', 'max:255'],
            'registration_number' => ['nullable', 'string', 'max:255'],
            'license_number' => ['nullable', 'string', 'max:255'],

            'is_brochure_available' => ['nullable', 'boolean'],
            'rating' => ['nullable', 'integer', 'between:1,5'],

            'categories' => ['sometimes', 'array'],
            'categories.*.category_id' => ['nullable', 'integer', 'exists:categories,id'],
            'categories.*.subcategory_id' => ['nullable', 'integer', 'exists:subcategories,id'],
            'categories.*.is_primary' => ['nullable', 'boolean'],

            'primary_category_index' => ['nullable', 'integer', 'min:0'],

            'business_types' => ['sometimes', 'array'],
            'business_types.*' => [
                'required_with:business_types',
                'string',
                Rule::in(VendorBusinessType::values()),
                'distinct',
            ],

            'brochure_rows' => ['nullable', 'array'],
            'brochure_rows.*.file' => ['nullable', 'file', 'max:20480', 'mimes:pdf,jpeg,jpg,png,gif,webp,doc,docx,xls,xlsx'],
            'brochure_rows.*.notes' => ['nullable', 'string'],
            'brochure_rows.*.category_id' => ['nullable', 'integer', 'exists:categories,id'],
            'brochure_rows.*.subcategory_id' => ['nullable', 'integer', 'exists:subcategories,id'],

            'brochures' => ['nullable', 'array'],
            'brochures.*' => ['file', 'max:20480', 'mimes:pdf,jpeg,jpg,png,gif,webp,doc,docx,xls,xlsx'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $this->validateLocation($validator);
            $this->validateCategoryAssignments($validator);
            $this->validateBrochureRows($validator);
        });
    }

    private function validateLocation(Validator $validator): void
    {
        $countryId = $this->input('country_id');
        $cityId = $this->input('city_id');

        if ($cityId && ! $countryId) {
            $validator->errors()->add('country_id', 'Select a country when a city is chosen.');
        }

        if ($cityId && $countryId) {
            $belongs = City::query()
                ->whereKey($cityId)
                ->where('country_id', $countryId)
                ->exists();

            if (! $belongs) {
                $validator->errors()->add('city_id', 'The selected city does not belong to the selected country.');
            }
        }
    }

    private function validateCategoryAssignments(Validator $validator): void
    {
        if (! $this->has('categories')) {
            return;
        }

        $assignments = $this->input('categories', []);
        $seen = [];
        $filledRowIndexes = [];

        foreach ($assignments as $index => $assignment) {
            $categoryId = $assignment['category_id'] ?? null;
            $subcategoryId = $assignment['subcategory_id'] ?? null;

            if ($categoryId === null || $categoryId === '') {
                if ($subcategoryId !== null && $subcategoryId !== '') {
                    $validator->errors()->add("categories.$index.subcategory_id", 'Select a category before choosing a subcategory.');
                }

                continue;
            }

            $filledRowIndexes[] = $index;

            $key = $categoryId.'|'.($subcategoryId ?? 'null');
            if (isset($seen[$key])) {
                $validator->errors()->add("categories.$index.category_id", 'Duplicate category assignment in payload.');

                continue;
            }
            $seen[$key] = true;

            if ($subcategoryId !== null && $subcategoryId !== '') {
                $belongs = Subcategory::query()
                    ->whereKey($subcategoryId)
                    ->where('category_id', $categoryId)
                    ->exists();

                if (! $belongs) {
                    $validator->errors()->add("categories.$index.subcategory_id", 'The selected subcategory does not belong to the selected category.');
                }
            }
        }

        if (count($filledRowIndexes) === 0) {
            return;
        }

        $primaryCount = 0;
        foreach ($filledRowIndexes as $index) {
            $assignment = $assignments[$index] ?? [];
            if (filter_var($assignment['is_primary'] ?? false, FILTER_VALIDATE_BOOLEAN)) {
                $primaryCount++;
            }
        }

        if ($primaryCount !== 1) {
            $validator->errors()->add('categories', 'Select exactly one primary category assignment.');
        }
    }

    private function validateBrochureRows(Validator $validator): void
    {
        $rows = $this->input('brochure_rows', []);
        if (! is_array($rows)) {
            return;
        }

        foreach ($rows as $index => $row) {
            if (! is_array($row)) {
                continue;
            }

            $categoryId = $row['category_id'] ?? null;
            $subcategoryId = $row['subcategory_id'] ?? null;

            if (($subcategoryId !== null && $subcategoryId !== '') && (! $categoryId || $categoryId === '')) {
                $validator->errors()->add("brochure_rows.$index.category_id", 'Select a category before choosing a subcategory for this brochure.');
            }

            if ($categoryId && $subcategoryId) {
                $belongs = Subcategory::query()
                    ->whereKey($subcategoryId)
                    ->where('category_id', $categoryId)
                    ->exists();

                if (! $belongs) {
                    $validator->errors()->add("brochure_rows.$index.subcategory_id", 'The selected subcategory does not belong to the selected category.');
                }
            }
        }
    }
}
