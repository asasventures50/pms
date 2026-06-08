<?php

namespace App\Http\Requests\Procurement\Rfqs\Concerns;

use App\Support\Procurement\ProcurementScopeType;

trait NormalizesRfqGeneralTermScopeTypes
{
    protected function prepareForValidation(): void
    {
        $scopeTypes = ProcurementScopeType::selectedValues($this->input('scope_types'));

        $bodyAr = $this->normalizeTermBodyForLocale('ar');
        $bodyEn = $this->normalizeTermBodyForLocale('en');

        $this->merge([
            'is_active' => $this->boolean('is_active'),
            'scope_types' => $scopeTypes === [] ? null : $scopeTypes,
            'body_ar' => $bodyAr,
            'body_en' => $bodyEn,
        ]);
    }

    private function normalizeTermBodyForLocale(string $locale): ?string
    {
        $key = trim((string) $this->input('key_'.$locale, ''));
        $valueInput = $this->input('value_'.$locale);

        if ($valueInput !== null || $key !== '') {
            $value = trim((string) ($valueInput ?? ''));
            if ($value === '') {
                return $key !== '' ? $key.':' : null;
            }

            return $key !== '' ? $key.': '.$value : $value;
        }

        return $this->filled('body_'.$locale) ? trim((string) $this->input('body_'.$locale)) : null;
    }
}
