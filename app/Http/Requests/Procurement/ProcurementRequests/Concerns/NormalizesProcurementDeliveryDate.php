<?php

namespace App\Http\Requests\Procurement\ProcurementRequests\Concerns;

trait NormalizesProcurementDeliveryDate
{
    protected function prepareForValidation(): void
    {
        $items = (array) $this->input('items', []);

        foreach ($items as $index => $row) {
            if (! is_array($row)) {
                continue;
            }

            $flexible = filter_var($row['flexible_delivery_date'] ?? false, FILTER_VALIDATE_BOOLEAN);

            $raw = trim((string) ($row['required_delivery_date'] ?? ''));

            $items[$index]['flexible_delivery_date'] = $flexible;
            $items[$index]['required_delivery_date'] = $raw !== '' ? $raw : null;
        }

        $this->merge(['items' => $items]);
    }
}
