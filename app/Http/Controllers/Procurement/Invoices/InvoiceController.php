<?php

namespace App\Http\Controllers\Procurement\Invoices;

use App\Http\Controllers\Controller;
use App\Http\Requests\Procurement\Invoices\StoreInvoiceRequest;
use App\Http\Requests\Procurement\Invoices\UpdateInvoiceRequest;
use App\Models\Procurement\Invoices\Invoice;
use App\Models\Procurement\PurchaseOrders\PurchaseOrder;
use App\Models\Procurement\PurchaseOrders\PurchaseOrderItem;
use App\Services\Procurement\Invoices\InvoiceCurrencyResolver;
use App\Services\Procurement\Invoices\InvoiceLineBuilder;
use App\Services\Procurement\Invoices\InvoicePersistenceService;
use App\Services\Procurement\Invoices\InvoiceProjectZoneResolver;
use App\Services\Procurement\PurchaseOrders\ProcurementRequestLineUnitLookup;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InvoiceController extends Controller
{
    public function __construct(
        private readonly InvoicePersistenceService $persistence,
        private readonly InvoiceLineBuilder $lineBuilder,
    ) {}

    public function index(Request $request): View
    {
        $perPage = max(1, min(100, (int) $request->query('per_page', 15)));

        $query = Invoice::query()
            ->with(['purchaseOrders', 'creator'])
            ->latest();

        if ($request->filled('q')) {
            $term = '%'.$request->string('q').'%';
            $query->where(function ($q) use ($term) {
                $q->where('invoice_number', 'like', $term)
                    ->orWhere('po_number', 'like', $term)
                    ->orWhere('recipient_name', 'like', $term)
                    ->orWhere('vendor_company_name', 'like', $term);
            });
        }

        $invoices = $query->paginate($perPage)->withQueryString();

        return view('procurement.invoices.index', [
            'invoices' => $invoices,
        ]);
    }

    public function create(): View
    {
        $purchaseOrders = PurchaseOrder::query()
            ->orderByDesc('id')
            ->get(['id', 'po_number', 'vendor_company_name', 'ordered_at']);

        return view('procurement.invoices.create', [
            'purchaseOrders' => $purchaseOrders,
        ]);
    }

    public function store(StoreInvoiceRequest $request): RedirectResponse
    {
        $payload = $this->resolveInvoicePayload($request);
        if ($payload instanceof RedirectResponse) {
            return $payload;
        }

        $invoice = $this->persistence->create($payload['header'], $payload['purchase_order_ids'], $payload['lines']);

        return redirect()
            ->route('invoices.print', $invoice)
            ->with('success', 'Invoice created successfully.');
    }

    public function edit(Invoice $invoice): View
    {
        $invoice->load(['purchaseOrders', 'items']);

        $purchaseOrders = PurchaseOrder::query()
            ->orderByDesc('id')
            ->get(['id', 'po_number', 'vendor_company_name', 'ordered_at']);

        return view('procurement.invoices.edit', [
            'invoice' => $invoice,
            'purchaseOrders' => $purchaseOrders,
            'invoiceDefaults' => $this->formDefaultsFromInvoice($invoice),
        ]);
    }

    public function update(UpdateInvoiceRequest $request, Invoice $invoice): RedirectResponse
    {
        $payload = $this->resolveInvoicePayload($request, $invoice);
        if ($payload instanceof RedirectResponse) {
            return $payload;
        }

        $invoice = $this->persistence->update($invoice, $payload['header'], $payload['purchase_order_ids'], $payload['lines']);

        return redirect()
            ->route('invoices.print', $invoice)
            ->with('success', 'Invoice updated successfully.');
    }

    public function destroy(Invoice $invoice): RedirectResponse
    {
        $invoiceNumber = $invoice->invoice_number;
        $invoice->delete();

        return redirect()
            ->route('invoices.index')
            ->with('success', "Invoice {$invoiceNumber} deleted successfully.");
    }

    public function print(Invoice $invoice): View
    {
        $invoice->load(['items', 'purchaseOrders.procurementRequest', 'creator']);

        $sourcePoItemIds = $invoice->items
            ->flatMap(fn ($item) => $item->source_purchase_order_item_ids ?? [])
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();

        $poItemsById = PurchaseOrderItem::query()
            ->whereIn('id', $sourcePoItemIds)
            ->with([
                'purchaseOrder.procurementRequest.items.project',
                'purchaseOrder.procurementRequest.items.zone',
                'purchaseOrder.procurementRequest.project',
                'purchaseOrder.procurementRequest.zone',
            ])
            ->get()
            ->keyBy('id');

        $projectZoneResolver = InvoiceProjectZoneResolver::fromPurchaseOrderItems($poItemsById->values());
        $unitsByLineCode = ProcurementRequestLineUnitLookup::unitsByLineCodeForPurchaseOrderItems($poItemsById->values());

        return view('procurement.invoices.print', [
            'invoice' => $invoice,
            'poItemsById' => $poItemsById,
            'projectZoneResolver' => $projectZoneResolver,
            'unitsByLineCode' => $unitsByLineCode,
        ]);
    }

    public function purchaseOrderItems(PurchaseOrder $purchaseOrder): JsonResponse
    {
        $purchaseOrder->load([
            'items',
            'vendor',
            'procurementRequest.items.project',
            'procurementRequest.items.zone',
            'procurementRequest.project',
            'procurementRequest.zone',
        ]);

        $vendorName = trim((string) ($purchaseOrder->vendor_company_name ?? $purchaseOrder->vendor?->name ?? ''));
        $currency = InvoiceCurrencyResolver::resolveWithSource($purchaseOrder);
        $projectZoneResolver = new InvoiceProjectZoneResolver($purchaseOrder->procurementRequest);
        $unitsByLineCode = ProcurementRequestLineUnitLookup::unitsByLineCode($purchaseOrder->procurementRequest);

        return response()->json([
            'id' => $purchaseOrder->id,
            'po_number' => $purchaseOrder->po_number,
            'vendor_company_name' => $vendorName,
            'ordered_at' => $purchaseOrder->ordered_at?->format('Y-m-d'),
            'currency_code' => $currency['code'],
            'currency_source' => $currency['source'],
            'items' => $purchaseOrder->items->map(fn ($item) => [
                'id' => $item->id,
                'purchase_order_id' => $purchaseOrder->id,
                'po_number' => $purchaseOrder->po_number,
                'line_code' => trim((string) ($item->item ?? '')),
                'project_zone' => $projectZoneResolver->forPoItem($item),
                'description' => $item->description,
                'quantity' => (float) $item->quantity,
                'unit' => ProcurementRequestLineUnitLookup::resolveForPurchaseOrderItem($item, $unitsByLineCode),
                'unit_price' => (float) $item->unit_price,
                'line_total' => (float) $item->line_total,
            ])->values()->all(),
        ]);
    }

    /**
     * @return array{
     *     header: array<string, mixed>,
     *     purchase_order_ids: list<int>,
     *     lines: list<array<string, mixed>>
     * }|RedirectResponse
     */
    private function resolveInvoicePayload(StoreInvoiceRequest $request, ?Invoice $invoice = null): array|RedirectResponse
    {
        $validated = $request->validated();

        $purchaseOrderIds = collect($validated['purchase_order_ids'])
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values()
            ->all();

        $purchaseOrders = PurchaseOrder::query()
            ->with(['items', 'vendor', 'procurementRequest'])
            ->whereIn('id', $purchaseOrderIds)
            ->get()
            ->sortBy(fn (PurchaseOrder $po) => array_search($po->id, $purchaseOrderIds, true))
            ->values();

        $selectedIds = collect($validated['purchase_order_item_ids'])
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();

        $selectedItems = PurchaseOrderItem::query()
            ->whereIn('id', $selectedIds)
            ->with([
                'purchaseOrder.procurementRequest.items.project',
                'purchaseOrder.procurementRequest.items.zone',
                'purchaseOrder.procurementRequest.project',
                'purchaseOrder.procurementRequest.zone',
            ])
            ->get()
            ->sortBy(fn (PurchaseOrderItem $item) => $selectedIds->search($item->id))
            ->values();

        $mergeGroups = collect($validated['merge_groups'] ?? [])
            ->map(function (array $group) {
                return [
                    'description' => trim((string) ($group['description'] ?? '')),
                    'item_ids' => collect($group['item_ids'] ?? [])
                        ->map(fn ($id) => (int) $id)
                        ->unique()
                        ->values()
                        ->all(),
                ];
            })
            ->filter(fn (array $group) => $group['description'] !== '' && count($group['item_ids']) >= 2)
            ->values()
            ->all();

        $lines = $this->lineBuilder->build($selectedItems, $mergeGroups);

        if ($lines === []) {
            return back()->withInput()->withErrors(['purchase_order_item_ids' => 'Select at least one line item.']);
        }

        $projectZoneResolver = InvoiceProjectZoneResolver::fromPurchaseOrderItems($selectedItems);
        $lines = array_map(function (array $line) use ($projectZoneResolver, $selectedItems) {
            $sourceIds = collect($line['source_purchase_order_item_ids'] ?? [])
                ->map(fn ($id) => (int) $id)
                ->all();
            $sourceItems = $selectedItems->whereIn('id', $sourceIds)->values();
            $line['project_zone'] = $projectZoneResolver->forPoItems($sourceItems);

            return $line;
        }, $lines);

        $primaryPurchaseOrder = $purchaseOrders->first();
        $currencyCode = InvoiceCurrencyResolver::resolveForStore(
            $validated['currency_code'] ?? null,
            $primaryPurchaseOrder,
        );

        $header = InvoicePersistenceService::headerFromPurchaseOrders(
            $purchaseOrders,
            trim($validated['recipient_name']),
            filled($validated['project_manager_name'] ?? null)
                ? trim((string) $validated['project_manager_name'])
                : null,
            (int) $request->user()->id,
            $mergeGroups !== [],
            $currencyCode,
            round((float) ($validated['transport_fees'] ?? 0), 2),
            round((float) ($validated['supervision_fees'] ?? 0), 2),
            round((float) ($validated['administrative_fees'] ?? 0), 2),
            round((float) ($validated['logistics_fees'] ?? 0), 2),
            $validated['notes'] ?? [],
            $validated['custom_fees'] ?? [],
        );

        if ($invoice !== null) {
            $header['created_by'] = $invoice->created_by;
            $header['invoiced_at'] = $invoice->invoiced_at?->toDateString() ?? now()->toDateString();
        }

        return [
            'header' => $header,
            'purchase_order_ids' => $purchaseOrderIds,
            'lines' => $lines,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function formDefaultsFromInvoice(Invoice $invoice): array
    {
        $mergeGroups = $invoice->items
            ->map(fn ($item) => [
                'description' => $item->description,
                'item_ids' => collect($item->source_purchase_order_item_ids ?? [])
                    ->map(fn ($id) => (int) $id)
                    ->values()
                    ->all(),
            ])
            ->filter(fn (array $group) => count($group['item_ids']) >= 2)
            ->values()
            ->all();

        $notes = $invoice->displayNotes();

        return [
            'purchase_order_ids' => $invoice->purchaseOrders->pluck('id')->all(),
            'purchase_order_item_ids' => $invoice->items
                ->flatMap(fn ($item) => $item->source_purchase_order_item_ids ?? [])
                ->map(fn ($id) => (int) $id)
                ->unique()
                ->values()
                ->all(),
            'merge_groups' => $mergeGroups,
            'recipient_name' => $invoice->recipient_name,
            'project_manager_name' => $invoice->project_manager_name,
            'notes' => $notes !== [] ? $notes : [''],
            'currency_code' => $invoice->currency_code ?? 'USD',
            'transport_fees' => (float) $invoice->transport_fees,
            'supervision_fees' => (float) $invoice->supervision_fees,
            'administrative_fees' => (float) $invoice->administrative_fees,
            'logistics_fees' => (float) $invoice->logistics_fees,
            'custom_fees' => $invoice->customFeesForDisplay(),
        ];
    }
}
