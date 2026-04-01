<?php

namespace App\Http\Requests\Procurement\Vendors;

use App\Enums\Procurement\Vendors\CompanyType;
use App\Enums\Procurement\Vendors\CoverageType;
use App\Enums\Procurement\Vendors\LeadTimeRange;
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

class StoreVendorRequest extends FormRequest
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

        if ($this->has('business_types_sync')) {
            $this->merge([
                'business_types' => array_values(array_filter(
                    (array) $this->input('business_types', []),
                    static fn ($v) => $v !== null && $v !== ''
                )),
            ]);
        }

        $this->merge([
            'rfq_method' => RfqMethod::normalizeRequestInput((array) $this->input('rfq_method', [])),
        ]);

        $this->normalizeCategorySubcategoryIds();
        $this->applyPrimaryCategoryFlags();
        $this->applyPrimaryLocationFlags();
    }

    private function normalizeCategorySubcategoryIds(): void
    {
        $categories = (array) $this->input('categories', []);
        foreach (array_keys($categories) as $i) {
            $row = $categories[$i];
            if (! is_array($row)) {
                continue;
            }
            $subs = $row['subcategory_ids'] ?? [];
            if (! is_array($subs)) {
                $subs = [];
            }
            $categories[$i]['subcategory_ids'] = array_values(array_unique(array_filter(
                array_map(static fn ($v) => (int) $v, $subs),
                static fn (int $id) => $id > 0
            )));
        }
        $this->merge(['categories' => $categories]);
    }

    private function applyPrimaryCategoryFlags(): void
    {
        $categories = (array) $this->input('categories', []);
        foreach (array_keys($categories) as $i) {
            if (! is_array($categories[$i])) {
                continue;
            }
            $categories[$i]['is_primary'] = filter_var($categories[$i]['is_primary'] ?? false, FILTER_VALIDATE_BOOLEAN);
        }
        $this->merge(['categories' => $categories]);
    }

    private function applyPrimaryLocationFlags(): void
    {
        if ($this->filled('primary_location_index')) {
            $idx = $this->input('primary_location_index');
            $locations = (array) $this->input('locations', []);
            foreach (array_keys($locations) as $i) {
                if (! is_array($locations[$i])) {
                    continue;
                }
                $locations[$i]['is_primary'] = (string) $i === (string) $idx;
            }
            $this->merge(['locations' => $locations]);
        }

        $locations = (array) $this->input('locations', []);
        $filledIndexes = [];
        foreach ($locations as $i => $row) {
            if (is_array($row) && ! empty($row['country_id'])) {
                $filledIndexes[] = $i;
            }
        }
        if (count($filledIndexes) === 1) {
            $only = $filledIndexes[0];
            foreach (array_keys($locations) as $i) {
                if (is_array($locations[$i])) {
                    $locations[$i]['is_primary'] = (int) $i === (int) $only;
                }
            }
            $this->merge(['locations' => $locations]);
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'vendor_code' => ['nullable', 'string', 'max:100', Rule::unique('vendors', 'vendor_code')],
            'name' => ['required', 'string', 'max:255'],
            'language' => ['required', 'string', Rule::in(VendorLanguage::values())],
            'description' => ['nullable', 'string'],
            'notes' => ['nullable', 'string'],

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

            'rfq_method' => ['nullable', 'array'],
            'rfq_method.*' => ['string', Rule::in(RfqMethod::values())],
            'pricing_frequency' => ['nullable', 'string', Rule::in(PricingFrequency::values())],
            'delivery_lead_time' => ['nullable', 'string', Rule::in(LeadTimeRange::values())],
            'execution_lead_time' => ['nullable', 'string', Rule::in(LeadTimeRange::values())],

            'payment_method' => ['nullable', 'string', Rule::in(PaymentMethod::values())],
            'payment_terms' => ['nullable', 'string'],
            'commercial_terms' => ['nullable', 'string'],
            'technical_capabilities' => ['nullable', 'string'],

            'bulletin_price_validity_days' => ['nullable', 'integer', 'min:0'],
            'currency_code' => ['nullable', 'string', 'size:3'],

            'company_type' => ['nullable', 'string', Rule::in(CompanyType::values())],
            'status' => ['required', 'string', Rule::in(VendorStatus::values())],
            'coverage_type' => ['nullable', 'string', Rule::in(CoverageType::values())],

            'tax_number' => ['nullable', 'string', 'max:255'],
            'registration_number' => ['nullable', 'string', 'max:255'],
            'license_number' => ['nullable', 'string', 'max:255'],

            'rating' => ['nullable', 'integer', 'between:1,5'],

            'categories' => ['sometimes', 'array'],
            'categories.*.category_id' => ['nullable', 'integer', 'exists:categories,id'],
            'categories.*.subcategory_ids' => ['nullable', 'array'],
            'categories.*.subcategory_ids.*' => ['integer', 'exists:subcategories,id', 'distinct'],
            'categories.*.is_primary' => ['nullable', 'boolean'],

            'locations' => ['nullable', 'array'],
            'locations.*.country_id' => ['nullable', 'integer', 'exists:countries,id'],
            'locations.*.city_id' => ['nullable', 'integer', 'exists:cities,id'],
            'locations.*.address' => ['nullable', 'string'],
            'locations.*.phone' => ['nullable', 'string', 'max:50'],
            'locations.*.whatsapp' => ['nullable', 'string', 'max:50'],
            'locations.*.notes' => ['nullable', 'string'],
            'locations.*.is_primary' => ['boolean'],

            'primary_location_index' => ['nullable', 'integer', 'min:0'],

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
            $this->validateVendorLocations($validator);
            $this->validateCategoryAssignments($validator);
            $this->validateBrochureRows($validator);
        });
    }

    private function validateVendorLocations(Validator $validator): void
    {
        $rows = $this->input('locations', []);
        if (! is_array($rows)) {
            return;
        }

        $filledIndexes = [];
        foreach ($rows as $index => $row) {
            if (! is_array($row)) {
                continue;
            }

            $countryId = $row['country_id'] ?? null;
            if ($countryId === null || $countryId === '') {
                $hasAnyData = collect([
                    $row['city_id'] ?? null,
                    $row['address'] ?? null,
                    $row['phone'] ?? null,
                    $row['whatsapp'] ?? null,
                    $row['notes'] ?? null,
                ])->contains(static fn ($value) => $value !== null && trim((string) $value) !== '');

                if ($hasAnyData) {
                    $validator->errors()->add("locations.$index.country_id", 'Select a country for this branch location.');
                }

                continue;
            }

            $filledIndexes[] = $index;
            $countryId = (int) $countryId;
            $cityId = isset($row['city_id']) && $row['city_id'] !== '' && $row['city_id'] !== null
                ? (int) $row['city_id']
                : null;

            if ($cityId) {
                $belongs = City::query()
                    ->whereKey($cityId)
                    ->where('country_id', $countryId)
                    ->exists();

                if (! $belongs) {
                    $validator->errors()->add("locations.$index.city_id", 'The selected city does not belong to the selected country.');
                }
            }
        }

        if (count($filledIndexes) === 0) {
            return;
        }

        $primaryCount = 0;
        foreach ($filledIndexes as $index) {
            $row = $rows[$index] ?? [];
            if (! is_array($row)) {
                continue;
            }
            if (filter_var($row['is_primary'] ?? false, FILTER_VALIDATE_BOOLEAN)) {
                $primaryCount++;
            }
        }

        if ($primaryCount !== 1) {
            $validator->errors()->add('locations', 'Select exactly one primary location among branches that have a country.');
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
            $subIds = $assignment['subcategory_ids'] ?? [];
            if (! is_array($subIds)) {
                $subIds = [];
            }

            if ($categoryId === null || $categoryId === '') {
                foreach ($subIds as $subId) {
                    if ($subId !== null && $subId !== '' && (int) $subId > 0) {
                        $validator->errors()->add("categories.$index.category_id", 'Select a category before choosing subcategories.');

                        break;
                    }
                }

                continue;
            }

            if (isset($seen[$categoryId])) {
                $validator->errors()->add("categories.$index.category_id", 'Each category can only appear once.');

                continue;
            }
            $seen[$categoryId] = true;

            $filledRowIndexes[] = $index;

            foreach ($subIds as $subcategoryId) {
                if ($subcategoryId === null || $subcategoryId === '' || (int) $subcategoryId <= 0) {
                    continue;
                }

                $belongs = Subcategory::query()
                    ->whereKey((int) $subcategoryId)
                    ->where('category_id', (int) $categoryId)
                    ->exists();

                if (! $belongs) {
                    $validator->errors()->add("categories.$index.subcategory_ids", 'A selected subcategory does not belong to the selected category.');

                    break;
                }
            }
        }

        if (count($filledRowIndexes) === 0) {
            return;
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
