<?php

namespace App\Http\Resources\Api\V1\Procurement\Projects;

use App\Models\Procurement\Projects\Zone;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Zone
 */
class ZoneResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'project_id' => $this->project_id,
            'code' => $this->code,
            'name' => $this->name,
            'status' => $this->status,
        ];
    }
}
