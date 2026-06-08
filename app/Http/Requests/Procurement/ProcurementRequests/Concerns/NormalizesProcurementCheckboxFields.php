<?php

namespace App\Http\Requests\Procurement\ProcurementRequests\Concerns;

use App\Enums\Procurement\ProcurementRequests\GeographicScope;

trait NormalizesProcurementCheckboxFields
{
    protected function normalizeProcurementCheckboxFields(): void
    {
        foreach (['procurement_types', 'geographic_scopes', 'vendor_types'] as $field) {
            $value = $this->input($field);

            if ($value === null) {
                if ($field === 'geographic_scopes') {
                    $this->merge([$field => []]);
                }

                continue;
            }

            if (! is_array($value)) {
                $this->merge([$field => []]);

                continue;
            }

            $normalized = [];

            foreach ($value as $item) {
                $item = strtolower(trim((string) $item));

                if ($item !== '') {
                    $normalized[] = $item;
                }
            }

            $this->merge([$field => $normalized]);
        }

        $this->merge([
            'geographic_scopes' => GeographicScope::selectedValues($this->input('geographic_scopes')),
        ]);
    }
}
