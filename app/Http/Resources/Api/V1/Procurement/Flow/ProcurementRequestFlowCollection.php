<?php

namespace App\Http\Resources\Api\V1\Procurement\Flow;

use App\Models\Procurement\ProcurementRequests\ProcurementRequest;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class ProcurementRequestFlowCollection extends ResourceCollection
{
    /**
     * @param  array<int, \App\Services\Procurement\Flow\ProcurementRequestFlowView>  $flows
     */
    public function __construct($resource, private readonly array $flows, private readonly bool $viewAll)
    {
        parent::__construct($resource);
    }

    /**
     * Keep paginator items as PR models. Default naming would mapInto
     * ProcurementRequestFlowResource($model, $index) and break the constructor.
     */
    protected function collects(): ?string
    {
        return null;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function toArray(Request $request): array
    {
        return $this->collection
            ->map(function (ProcurementRequest $pr) use ($request) {
                return (new ProcurementRequestFlowResource(
                    $pr,
                    $this->flows[(int) $pr->id] ?? null,
                    $this->viewAll,
                ))->resolve($request);
            })
            ->values()
            ->all();
    }
}
