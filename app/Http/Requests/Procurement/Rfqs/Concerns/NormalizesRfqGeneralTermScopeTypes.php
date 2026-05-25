<?php

namespace App\Http\Requests\Procurement\Rfqs\Concerns;

use App\Support\Procurement\ProcurementScopeType;

trait NormalizesRfqGeneralTermScopeTypes
{
    protected function prepareForValidation(): void
    {
        $scopeTypes = ProcurementScopeType::selectedValues($this->input('scope_types'));

        $this->merge([
            'is_active' => $this->boolean('is_active'),
            'scope_types' => $scopeTypes === [] ? null : $scopeTypes,
            'body_ar' => $this->filled('body_ar') ? trim((string) $this->input('body_ar')) : null,
            'body_en' => $this->filled('body_en') ? trim((string) $this->input('body_en')) : null,
        ]);
    }
}
