<?php

namespace App\Http\Resources\Api\V1\Geo;

use App\Models\Geo\Country;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Country
 */
class CountryResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name_ar' => $this->name_ar,
            'name_en' => $this->name_en,
            'name' => $this->name,
            'iso_code' => $this->iso_code,
            'flag_emoji' => $this->flag_emoji,
            'status' => $this->status,
            'cities_count' => $this->whenCounted('cities'),
            'used_in_vendor_locations' => (bool) ($this->vendor_locations_exists ?? false),
            'cities' => CityResource::collection($this->whenLoaded('cities')),
        ];
    }
}
