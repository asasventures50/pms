<?php

namespace App\Http\Resources\Api\V1\Procurement\Categories;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\Procurement\Vendors\Category
 */
class CategoryResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name_en' => $this->name_en,
            'name_ar' => $this->name_ar,
            'slug' => $this->slug,
            'status' => $this->status,
            'subcategories_count' => $this->whenCounted('subcategories'),
            'vendors_count' => $this->when(isset($this->vendors_count), (int) $this->vendors_count),
            'category_only_vendor_count' => $this->when(
                isset($this->category_only_vendor_count),
                (int) $this->category_only_vendor_count
            ),
            'subcategories' => SubcategoryResource::collection($this->whenLoaded('subcategories')),
        ];
    }
}
