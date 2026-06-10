<?php

namespace App\Http\Requests\Procurement\Vendors;

use Illuminate\Support\Arr;

class StoreVendorRegistrationRequest extends StoreVendorRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return Arr::except(parent::rules(), [
            'vendor_code',
            'status',
            'rating',
            'notes',
        ]);
    }
}
