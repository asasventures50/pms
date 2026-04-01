<?php

namespace App\Http\Requests\Geo;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCityRequest extends FormRequest
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
        $city = $this->route('city');
        $cityId = is_object($city) ? ($city->id ?? null) : $city;

        return [
            'country_id' => ['required', 'integer', 'exists:countries,id'],
            'name_ar' => [
                'required',
                'string',
                'max:255',
                Rule::unique('cities', 'name_ar')
                    ->ignore($cityId)
                    ->where(fn ($q) => $q->where('country_id', $this->input('country_id'))->whereNull('deleted_at')),
            ],
            'name_en' => [
                'required',
                'string',
                'max:255',
                Rule::unique('cities', 'name_en')
                    ->ignore($cityId)
                    ->where(fn ($q) => $q->where('country_id', $this->input('country_id'))->whereNull('deleted_at')),
            ],
            'status' => ['required', 'string', Rule::in(['active', 'inactive'])],
        ];
    }
}
