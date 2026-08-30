<?php

namespace App\Http\Resources\Api\V1\Procurement\ProcurementRequests;

use App\Models\Procurement\ProcurementRequests\ProcurementRequest;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin ProcurementRequest
 */
class ProcurementRequestSummaryResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $status = $this->status;

        return [
            'id' => $this->id,
            'request_number' => $this->request_number,
            'status' => $status instanceof \BackedEnum ? $status->value : $status,
            'requestor_name' => $this->requestor_name,
            'requestor_department' => $this->requestor_department,
            'created_by' => $this->created_by,
            'creator' => $this->whenLoaded('creator', fn () => [
                'id' => $this->creator?->id,
                'name' => $this->creator?->name,
            ]),
            'project' => $this->whenLoaded('project', fn () => $this->project ? [
                'id' => $this->project->id,
                'code' => $this->project->code,
                'name' => $this->project->name,
            ] : null),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
