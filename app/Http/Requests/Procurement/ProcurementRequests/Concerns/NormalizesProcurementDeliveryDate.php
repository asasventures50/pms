<?php

namespace App\Http\Requests\Procurement\ProcurementRequests\Concerns;

trait NormalizesProcurementDeliveryDate
{
    protected function prepareForValidation(): void
    {
        $raw = trim((string) $this->input('required_delivery_date', ''));

        $this->merge([
            'delivery_completed' => $this->boolean('delivery_completed'),
            'required_delivery_date' => $raw !== '' ? $raw : null,
        ]);
    }
}
