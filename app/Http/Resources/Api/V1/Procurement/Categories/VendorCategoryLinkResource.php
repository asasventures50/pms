<?php

namespace App\Http\Resources\Api\V1\Procurement\Categories;

use App\Models\Procurement\Vendors\VendorCategory;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin VendorCategory
 */
class VendorCategoryLinkResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $other = $this->other_links_in_category;

        return [
            'id' => $this->id,
            'vendor_id' => $this->vendor_id,
            'category_id' => $this->category_id,
            'subcategory_id' => $this->subcategory_id,
            'is_primary' => (bool) $this->is_primary,
            'matching_brochures_count' => (int) ($this->matching_brochures_count ?? 0),
            'vendor' => $this->whenLoaded('vendor', fn () => [
                'id' => $this->vendor->id,
                'name' => $this->vendor->name,
                'vendor_code' => $this->vendor->vendor_code,
            ]),
            'other_links_in_category' => $other === null ? [] : collect($other)->map(fn (VendorCategory $link) => [
                'id' => $link->id,
                'subcategory_id' => $link->subcategory_id,
                'subcategory' => $link->subcategory === null ? null : [
                    'id' => $link->subcategory->id,
                    'name_en' => $link->subcategory->name_en,
                    'name_ar' => $link->subcategory->name_ar,
                ],
            ])->values()->all(),
        ];
    }
}
