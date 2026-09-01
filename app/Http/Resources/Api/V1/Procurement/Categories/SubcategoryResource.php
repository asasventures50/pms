<?php

namespace App\Http\Resources\Api\V1\Procurement\Categories;

use App\Models\Procurement\Vendors\Subcategory;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Subcategory
 */
class SubcategoryResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'category_id' => $this->category_id,
            'name_en' => $this->name_en,
            'name_ar' => $this->name_ar,
            'slug' => $this->slug,
            'status' => $this->status,
            'vendors_count' => $this->whenCounted('vendors'),
        ];
    }
}
