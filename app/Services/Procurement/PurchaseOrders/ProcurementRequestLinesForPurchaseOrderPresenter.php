<?php

namespace App\Services\Procurement\PurchaseOrders;

use App\Models\Procurement\ProcurementRequests\ProcurementRequest;
use App\Models\Procurement\ProcurementRequests\ProcurementRequestItem;
use App\Support\Procurement\ProcurementScopeType;
use Illuminate\Support\Str;

class ProcurementRequestLinesForPurchaseOrderPresenter
{
    /**
     * @return array{request_number: string, items: list<array<string, mixed>>}
     */
    public function present(ProcurementRequest $procurementRequest): array
    {
        $procurementRequest->loadMissing([
            'items.project:id,code,name',
        ]);

        $items = $procurementRequest->items
            ->sortBy(['sort_order', 'id'])
            ->values();

        return [
            'request_number' => $procurementRequest->request_number ?? '',
            'context' => PurchaseOrderProcurementRequestContext::aggregateFromItems($items),
            'scope_type_keys' => PurchaseOrderProcurementRequestContext::scopeTypeKeys($items),
            'items' => $items->map(fn (ProcurementRequestItem $line) => $this->toLine($line))->all(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function toLine(ProcurementRequestItem $line): array
    {
        $category = trim((string) ($line->category ?? ''));
        $subcategory = trim((string) ($line->subcategory ?? ''));
        $categoryLabel = match (true) {
            $category !== '' && $subcategory !== '' => $category.' / '.$subcategory,
            $category !== '' => $category,
            $subcategory !== '' => $subcategory,
            default => '',
        };

        $project = $line->project;
        $projectLabel = $project !== null
            ? trim(($project->code ? $project->code.' — ' : '').($project->name ?? ''))
            : '';

        $description = trim((string) ($line->description ?? ''));
        $shortDescription = $description !== '' ? Str::limit($description, 100) : 'Item';

        return [
            'id' => $line->id,
            'item' => $line->line_number ?? '',
            'description' => $line->description ?? '',
            'quantity' => (float) $line->quantity,
            'unit_price' => 0,
            'project' => $projectLabel,
            'category' => $categoryLabel,
            'scope_type' => ProcurementScopeType::display($line->scope_type),
            'scope_type_keys' => ProcurementScopeType::selectedValues($line->scope_type),
            'summary' => $shortDescription,
        ];
    }
}
