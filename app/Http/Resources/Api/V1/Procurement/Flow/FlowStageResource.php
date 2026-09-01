<?php

namespace App\Http\Resources\Api\V1\Procurement\Flow;

use App\Services\Procurement\Flow\FlowStage;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin FlowStage
 */
class FlowStageResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'key' => $this->key->value,
            'state' => $this->state->value,
            'label' => $this->label,
            'badge' => $this->badge,
            'badge_label' => $this->badgeLabel,
            'detail' => $this->detail,
        ];
    }
}
