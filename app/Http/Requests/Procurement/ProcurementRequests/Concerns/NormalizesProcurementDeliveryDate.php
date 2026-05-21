<?php

namespace App\Http\Requests\Procurement\ProcurementRequests\Concerns;

trait NormalizesProcurementDeliveryDate
{
    protected function prepareForValidation(): void
    {
        $flexible = $this->boolean('flexible_delivery_date');

        $raw = trim((string) $this->input('required_delivery_date', ''));

        $this->merge([
            'flexible_delivery_date' => $flexible,
            'required_delivery_date' => $raw !== '' ? $raw : null,
        ]);
    }
}
