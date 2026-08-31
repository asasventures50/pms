<?php

namespace App\Http\Resources\Api\V1\Geo;

use App\Models\Geo\City;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin City
 */
class CityResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'country_id' => $this->country_id,
            'name_ar' => $this->name_ar,
            'name_en' => $this->name_en,
            'name' => $this->name,
            'status' => $this->status,
            'used_in_vendor_locations' => (bool) ($this->vendor_locations_exists ?? false),
            'country' => $this->whenLoaded('country', fn () => [
                'id' => $this->country->id,
                'name_ar' => $this->country->name_ar,
                'name_en' => $this->country->name_en,
                'iso_code' => $this->country->iso_code,
            ]),
        ];
    }
}
