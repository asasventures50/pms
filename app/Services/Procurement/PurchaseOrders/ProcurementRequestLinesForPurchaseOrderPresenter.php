<?php

namespace App\Services\Procurement\PurchaseOrders;

use App\Enums\Procurement\ProcurementRequests\ProcurementVendorType;
use App\Models\Procurement\ProcurementRequests\ProcurementRequest;
use App\Models\Procurement\ProcurementRequests\ProcurementRequestItem;
use App\Support\Procurement\ProcurementCheckboxGroup;
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
            'project:id,code,name',
            'category:id,name_en,name_ar',
            'subcategory:id,name_en,name_ar',
        ]);

        $items = $procurementRequest->items
            ->sortBy(['sort_order', 'id'])
            ->values();

        return [
            'request_number' => $procurementRequest->request_number ?? '',
            'context' => PurchaseOrderProcurementRequestContext::aggregateFromRequest($procurementRequest, $items),
            'scope_type_keys' => PurchaseOrderProcurementRequestContext::scopeTypeKeysFromRequest($procurementRequest, $items),
            'items' => $items->map(fn (ProcurementRequestItem $line) => $this->toLine($procurementRequest, $line))->all(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function toLine(ProcurementRequest $request, ProcurementRequestItem $line): array
    {
        $categoryLabel = $this->categoryLabel($request, $line);
        $project = $line->project ?? $request->project;
        $projectLabel = $project !== null
            ? trim(($project->code ? $project->code.' — ' : '').($project->name ?? ''))
            : '';

        $description = trim((string) ($line->description ?? ''));
        $shortDescription = $description !== '' ? Str::limit($description, 100) : 'Item';
        $scopeDisplay = $this->scopeDisplay($request, $line);

        return [
            'id' => $line->id,
            'item' => $line->line_number ?? '',
            'description' => $line->description ?? '',
            'quantity' => (float) $line->quantity,
            'unit_price' => (float) ($line->unit_price ?? 0),
            'project' => $projectLabel,
            'category' => $categoryLabel,
            'scope_type' => $scopeDisplay,
            'scope_type_keys' => ProcurementScopeType::selectedValues($line->scope_type ?: $scopeDisplay),
            'summary' => $shortDescription,
        ];
    }

    private function categoryLabel(ProcurementRequest $request, ProcurementRequestItem $line): string
    {
        if ($request->category) {
            $category = $request->category->name_en ?? $request->category->name_ar ?? '';
            $subcategory = $request->subcategory?->name_en ?? $request->subcategory?->name_ar ?? '';

            return match (true) {
                $category !== '' && $subcategory !== '' => $category.' / '.$subcategory,
                $category !== '' => $category,
                $subcategory !== '' => $subcategory,
                default => '',
            };
        }

        $category = trim((string) ($line->category ?? ''));
        $subcategory = trim((string) ($line->subcategory ?? ''));

        return match (true) {
            $category !== '' && $subcategory !== '' => $category.' / '.$subcategory,
            $category !== '' => $category,
            $subcategory !== '' => $subcategory,
            default => '',
        };
    }

    private function scopeDisplay(ProcurementRequest $request, ProcurementRequestItem $line): string
    {
        $fromLine = ProcurementScopeType::display($line->scope_type);
        if ($fromLine !== '') {
            return $fromLine;
        }

        $vendorTypes = ProcurementCheckboxGroup::selectedValues(
            $request->vendor_types,
            ProcurementVendorType::values()
        );

        if ($vendorTypes === []) {
            return '';
        }

        return implode(', ', array_map(
            static fn (string $value) => ProcurementVendorType::from($value)->legacyScopeLabel(),
            $vendorTypes
        ));
    }
}
