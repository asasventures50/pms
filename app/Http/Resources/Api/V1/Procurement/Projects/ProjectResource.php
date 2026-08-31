<?php

namespace App\Http\Resources\Api\V1\Procurement\Projects;

use App\Models\Procurement\Projects\Project;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Project
 */
class ProjectResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'name' => $this->name,
            'status' => $this->status,
            'zones_count' => $this->whenCounted('zones'),
            'zones' => ZoneResource::collection($this->whenLoaded('zones')),
        ];
    }
}
