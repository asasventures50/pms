<?php

namespace App\Services\Procurement\PurchaseOrders;

use App\Models\Procurement\ProcurementRequests\ProcurementRequestItem;
use App\Models\Procurement\PurchaseOrders\PurchaseOrder;
use App\Support\Procurement\ProcurementScopeType;
use Illuminate\Support\Collection;

class PurchaseOrderProcurementRequestContext
{
    /**
     * @return array{
     *     category: string,
     *     scope_type: string,
     *     project: string,
     *     pr_items_by_line: array<string, ProcurementRequestItem>
     * }
     */
    public static function resolve(PurchaseOrder $purchaseOrder): array
    {
        $request = $purchaseOrder->procurementRequest;

        if ($request === null) {
            return [
                ...self::emptyAggregates(),
                'pr_items_by_line' => [],
            ];
        }

        $items = $request->relationLoaded('items')
            ? $request->items
            : $request->items()->with('project')->orderBy('sort_order')->orderBy('id')->get();

        if (! $items->every(fn (ProcurementRequestItem $item) => $item->relationLoaded('project'))) {
            $items->load('project');
        }

        $poLineNumbers = self::purchaseOrderLineNumbers($purchaseOrder);
        if ($poLineNumbers !== []) {
            $items = $items->filter(function (ProcurementRequestItem $item) use ($poLineNumbers) {
                $lineNumber = trim((string) ($item->line_number ?? ''));

                return $lineNumber !== '' && in_array($lineNumber, $poLineNumbers, true);
            })->values();
        } else {
            $items = collect();
        }

        /** @var array<string, ProcurementRequestItem> $byLine */
        $byLine = [];
        foreach ($items as $item) {
            $lineNumber = trim((string) ($item->line_number ?? ''));
            if ($lineNumber !== '') {
                $byLine[$lineNumber] = $item;
            }
        }

        return [
            ...self::aggregateFromItems($items),
            'pr_items_by_line' => $byLine,
        ];
    }

    /**
     * @param  Collection<int, ProcurementRequestItem>  $items
     * @return array{category: string, scope_type: string, project: string}
     */
    public static function aggregateFromItems(Collection $items): array
    {
        return [
            'category' => self::aggregateCategories($items),
            'scope_type' => self::aggregateScopeTypes($items),
            'project' => self::aggregateProjects($items),
        ];
    }

    /**
     * @param  Collection<int, ProcurementRequestItem>  $items
     */
    private static function aggregateCategories(Collection $items): string
    {
        $labels = [];

        foreach ($items as $item) {
            $category = trim((string) ($item->category ?? ''));
            $subcategory = trim((string) ($item->subcategory ?? ''));

            $label = match (true) {
                $category !== '' && $subcategory !== '' => $category.' / '.$subcategory,
                $category !== '' => $category,
                $subcategory !== '' => $subcategory,
                default => '',
            };

            if ($label !== '') {
                $labels[$label] = true;
            }
        }

        return implode('; ', array_keys($labels));
    }

    /**
     * @param  Collection<int, ProcurementRequestItem>  $items
     */
    private static function aggregateScopeTypes(Collection $items): string
    {
        $found = [];

        foreach ($items as $item) {
            foreach (ProcurementScopeType::selectedValues($item->scope_type) as $scope) {
                $found[$scope] = true;
            }
        }

        $ordered = [];
        foreach (ProcurementScopeType::options() as $option) {
            if (isset($found[$option])) {
                $ordered[] = $option;
            }
        }

        return implode(', ', array_map(
            static fn (string $scope): string => ProcurementScopeType::label($scope),
            $ordered
        ));
    }

    /**
     * @param  Collection<int, ProcurementRequestItem>  $items
     * @return list<string>
     */
    public static function scopeTypeKeys(Collection $items): array
    {
        $found = [];

        foreach ($items as $item) {
            foreach (ProcurementScopeType::selectedValues($item->scope_type) as $scope) {
                $found[$scope] = true;
            }
        }

        $ordered = [];
        foreach (ProcurementScopeType::options() as $option) {
            if (isset($found[$option])) {
                $ordered[] = $option;
            }
        }

        return $ordered;
    }

    /**
     * @param  Collection<int, ProcurementRequestItem>  $items
     */
    private static function aggregateProjects(Collection $items): string
    {
        $labels = [];

        foreach ($items as $item) {
            $project = $item->project;
            if ($project === null) {
                continue;
            }

            $label = trim(($project->code ? $project->code.' — ' : '').($project->name ?? ''));
            if ($label !== '') {
                $labels[$label] = true;
            }
        }

        return implode('; ', array_keys($labels));
    }

    /**
     * @return array{category: string, scope_type: string, project: string}
     */
    public static function emptyAggregates(): array
    {
        return [
            'category' => '',
            'scope_type' => '',
            'project' => '',
        ];
    }

    /**
     * @return list<string>
     */
    private static function purchaseOrderLineNumbers(PurchaseOrder $purchaseOrder): array
    {
        $poItems = $purchaseOrder->relationLoaded('items')
            ? $purchaseOrder->items
            : $purchaseOrder->items()->get(['item']);

        $numbers = [];
        foreach ($poItems as $poItem) {
            $lineNumber = trim((string) ($poItem->item ?? ''));
            if ($lineNumber !== '') {
                $numbers[] = $lineNumber;
            }
        }

        return array_values(array_unique($numbers));
    }
}
