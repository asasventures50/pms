<?php

namespace App\Http\Requests\Procurement\ProcurementRequests\Concerns;

trait NormalizesProcurementScopeType
{
    protected function normalizeProcurementScopeTypeFields(): void
    {
        $items = (array) $this->input('items', []);

        foreach ($items as $index => $row) {
            if (! is_array($row)) {
                continue;
            }

            $scopeType = $row['scope_type'] ?? null;

            if (is_array($scopeType)) {
                $items[$index]['scope_type'] = array_values(array_filter(
                    $scopeType,
                    static fn ($value) => trim((string) $value) !== ''
                ));
            } elseif (is_string($scopeType) && trim($scopeType) !== '') {
                $items[$index]['scope_type'] = [trim($scopeType)];
            } else {
                $items[$index]['scope_type'] = [];
            }
        }

        $this->merge(['items' => $items]);
    }
}
