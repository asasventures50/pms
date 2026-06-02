<?php

namespace App\Services\Procurement\PurchaseOrders;

use App\Models\Procurement\ProcurementRequests\ProcurementRequest;

class ProcurementRequestOptionsForPurchaseOrderQuery
{
    /**
     * @return list<array{id: int, label: string}>
     */
    public function options(?int $ensureRequestId = null): array
    {
        $requests = $this->baseQuery()->get();

        $options = $requests
            ->map(fn (ProcurementRequest $request) => [
                'id' => $request->id,
                'label' => $this->formatLabel($request),
            ])
            ->values()
            ->all();

        if ($ensureRequestId !== null && ! $this->containsId($options, $ensureRequestId)) {
            $linked = $this->baseQuery()->find($ensureRequestId);
            if ($linked !== null) {
                array_unshift($options, [
                    'id' => $linked->id,
                    'label' => $this->formatLabel($linked),
                ]);
            }
        }

        return $options;
    }

    public function formatLabel(ProcurementRequest $request): string
    {
        $number = trim((string) $request->request_number);
        $parts = [$number !== '' ? $number : 'PR #'.$request->id];

        $firstItem = $request->items
            ->sortBy(fn ($item) => [$item->sort_order ?? 0, $item->id])
            ->first();

        $project = $firstItem?->project;
        if ($project !== null) {
            $projectLabel = trim(($project->code ? $project->code.' — ' : '').($project->name ?? ''));
            if ($projectLabel !== '') {
                $parts[] = $projectLabel;
            }
        }

        $requestor = trim((string) ($request->requestor_name ?? $request->creator?->name ?? ''));
        if ($requestor !== '') {
            $parts[] = $requestor;
        }

        return implode(' — ', $parts);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Builder<ProcurementRequest>
     */
    private function baseQuery()
    {
        return ProcurementRequest::query()
            ->with([
                'creator:id,name',
                'items.project:id,code,name',
            ])
            ->orderByDesc('id');
    }

    /**
     * @param  list<array{id: int, label: string}>  $options
     */
    private function containsId(array $options, int $id): bool
    {
        foreach ($options as $option) {
            if ((int) $option['id'] === $id) {
                return true;
            }
        }

        return false;
    }
}
