<?php

namespace App\Services\Procurement\PurchaseOrders;

use App\Enums\Procurement\ProcurementRequests\ProcurementType;
use App\Enums\Procurement\ProcurementRequests\ProcurementVendorType;
use App\Models\Procurement\ProcurementRequests\ProcurementRequest;
use App\Models\Procurement\ProcurementRequests\ProcurementRequestDocument;
use App\Models\Procurement\ProcurementRequests\ProcurementRequestItem;
use App\Models\Procurement\PurchaseOrders\PurchaseOrder;
use App\Support\Procurement\ProcurementCheckboxGroup;
use App\Support\Procurement\ProcurementScopeType;
use Illuminate\Support\Collection;

class PurchaseOrderProcurementRequestContext
{
    /**
     * @return array{
     *     category: string,
     *     scope_type: string,
     *     project: string,
     *     procurement_type: string,
     *     supporting_documents: list<array{file_name: string, document_type: ?string, file_description: ?string}>,
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
                'supporting_documents' => [],
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
            ...self::aggregateFromRequest($request, $items),
            'pr_items_by_line' => $byLine,
            'supporting_documents' => self::supportingDocumentsForRequest($request, $items),
        ];
    }

    /**
     * @param  Collection<int, ProcurementRequestItem>  $items
     * @return array{category: string, scope_type: string, project: string, procurement_type: string}
     */
    public static function aggregateFromRequest(ProcurementRequest $request, Collection $items): array
    {
        $fromItems = self::aggregateFromItems($items);

        if ($request->relationLoaded('project') || $request->project_id) {
            $request->loadMissing(['project', 'category', 'subcategory']);
        }

        $projectLabel = '';
        if ($request->project) {
            $projectLabel = trim(($request->project->code ? $request->project->code.' — ' : '').($request->project->name ?? ''));
        }

        $categoryLabel = '';
        if ($request->category) {
            $category = $request->category->name_en ?? $request->category->name_ar ?? '';
            $subcategory = $request->subcategory?->name_en ?? $request->subcategory?->name_ar ?? '';
            $categoryLabel = match (true) {
                $category !== '' && $subcategory !== '' => $category.' / '.$subcategory,
                $category !== '' => $category,
                $subcategory !== '' => $subcategory,
                default => '',
            };
        }

        $scopeType = self::scopeTypeDisplayFromRequest($request, $items);

        return [
            'category' => $categoryLabel !== '' ? $categoryLabel : $fromItems['category'],
            'scope_type' => $scopeType !== '' ? $scopeType : $fromItems['scope_type'],
            'project' => $projectLabel !== '' ? $projectLabel : $fromItems['project'],
            'procurement_type' => self::procurementTypeDisplayFromRequest($request),
        ];
    }

    public static function procurementTypeDisplayFromRequest(ProcurementRequest $request): string
    {
        return ProcurementCheckboxGroup::display(
            $request->procurement_types,
            ProcurementType::values(),
            fn (string $value) => ProcurementType::from($value)->label()
        );
    }

    /**
     * @param  Collection<int, ProcurementRequestItem>  $items
     * @return list<array{file_name: string, document_type: ?string, file_description: ?string}>
     */
    public static function supportingDocumentsForRequest(ProcurementRequest $request, Collection $items): array
    {
        $request->loadMissing('headerDocuments');
        $items->loadMissing('documents');

        $documents = $request->headerDocuments;
        foreach ($items as $item) {
            $documents = $documents->concat($item->documents);
        }

        return $documents
            ->unique('id')
            ->values()
            ->map(static fn (ProcurementRequestDocument $document) => [
                'file_name' => (string) ($document->file_name ?? ''),
                'document_type' => $document->document_type,
                'file_description' => $document->file_description,
            ])
            ->filter(static fn (array $document) => $document['file_name'] !== '')
            ->values()
            ->all();
    }

    /**
     * @param  Collection<int, ProcurementRequestItem>  $items
     * @return list<string>
     */
    public static function scopeTypeKeysFromRequest(ProcurementRequest $request, Collection $items): array
    {
        $fromItems = self::scopeTypeKeys($items);
        if ($fromItems !== []) {
            return $fromItems;
        }

        $vendorTypes = ProcurementCheckboxGroup::selectedValues(
            $request->vendor_types,
            ProcurementVendorType::values()
        );

        $map = [
            'contractor' => ProcurementScopeType::Contractor,
            'supplier' => ProcurementScopeType::Supplier,
            'studies' => ProcurementScopeType::Studies,
        ];

        $keys = [];
        foreach ($vendorTypes as $value) {
            if (isset($map[$value])) {
                $keys[] = $map[$value];
            }
        }

        return $keys;
    }

    /**
     * @param  Collection<int, ProcurementRequestItem>  $items
     */
    private static function scopeTypeDisplayFromRequest(ProcurementRequest $request, Collection $items): string
    {
        $fromItems = self::aggregateScopeTypes($items);
        if ($fromItems !== '') {
            return $fromItems;
        }

        $keys = self::scopeTypeKeysFromRequest($request, $items);

        return implode(', ', array_map(
            static fn (string $scope): string => ProcurementScopeType::label($scope),
            $keys
        ));
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
     * @return array{category: string, scope_type: string, project: string, procurement_type: string}
     */
    public static function emptyAggregates(): array
    {
        return [
            'category' => '',
            'scope_type' => '',
            'project' => '',
            'procurement_type' => '',
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
