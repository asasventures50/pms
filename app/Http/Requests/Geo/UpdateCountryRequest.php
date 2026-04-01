<?php

namespace App\Http\Requests\Geo;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCountryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $iso = $this->input('iso_code');
        $this->merge([
            'iso_code' => $iso !== null && trim((string) $iso) !== '' ? strtoupper(trim((string) $iso)) : null,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $country = $this->route('country');
        $countryId = is_object($country) ? ($country->id ?? null) : $country;

        return [
            'name_ar' => ['required', 'string', 'max:255'],
            'name_en' => ['required', 'string', 'max:255'],
            'iso_code' => ['nullable', 'string', 'max:8', Rule::unique('countries', 'iso_code')->ignore($countryId)],
            'flag_emoji' => ['nullable', 'string', 'max:16'],
            'status' => ['required', 'string', Rule::in(['active', 'inactive'])],
        ];
    }
}
