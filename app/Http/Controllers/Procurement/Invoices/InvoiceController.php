<?php

namespace App\Http\Controllers\Procurement\Invoices;

use App\Exports\Procurement\InvoiceExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\Procurement\Invoices\StoreInvoiceRequest;
use App\Http\Requests\Procurement\Invoices\StoreInvoiceSignedDocumentRequest;
use App\Http\Requests\Procurement\Invoices\UpdateInvoiceRequest;
use App\Models\Procurement\Invoices\Invoice;
use App\Models\Procurement\PurchaseOrders\PurchaseOrder;
use App\Models\Procurement\PurchaseOrders\PurchaseOrderItem;
use App\Models\Procurement\PurchaseOrders\PurchaseOrderPaymentTerm;
use App\Services\Procurement\Invoices\InvoiceCurrencyResolver;
use App\Services\Procurement\Invoices\InvoiceLineBuilder;
use App\Services\Procurement\Invoices\InvoiceManualPoNumberGenerator;
use App\Services\Procurement\Invoices\InvoicePersistenceService;
use App\Services\Procurement\Invoices\InvoiceProjectZoneResolver;
use App\Services\Procurement\Invoices\InvoiceSignedDocumentStorage;
use App\Services\Procurement\PurchaseOrders\ProcurementRequestLineUnitLookup;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class InvoiceController extends Controller
{
    public function __construct(
        private readonly InvoicePersistenceService $persistence,
        private readonly InvoiceLineBuilder $lineBuilder,
        private readonly InvoiceManualPoNumberGenerator $manualPoNumberGenerator,
        private readonly InvoiceSignedDocumentStorage $signedDocuments,
    ) {}

    public function index(Request $request): View
    {
        $perPage = max(1, min(100, (int) $request->query('per_page', 15)));

        $invoices = $this->listQuery($request)
            ->with(['purchaseOrders', 'creator'])
            ->paginate($perPage)
            ->withQueryString();

        return view('procurement.invoices.index', [
            'invoices' => $invoices,
        ]);
    }

    /**
     * Read-only Excel download matching the print document. Does not mutate invoice data.
     */
    public function export(Invoice $invoice): BinaryFileResponse
    {
        $context = $this->printContext($invoice);
        $safeNumber = Str::of($invoice->invoice_number)->replaceMatches('/[^\w\-.]+/u', '_');
        $filename = 'invoice-'.$safeNumber.'-'.now()->format('Y-m-d_His').'.xlsx';

        return Excel::download(
            new InvoiceExport(
                $context['invoice'],
                $context['poItemsById'],
                $context['projectZoneResolver'],
                $context['unitsByLineCode'],
            ),
            $filename
        );
    }

    public function create(Request $request): View
    {
        $purchaseOrders = PurchaseOrder::query()
            ->orderByDesc('id')
            ->get(['id', 'po_number', 'vendor_company_name', 'ordered_at']);

        $existingInvoices = Invoice::query()
            ->orderByDesc('id')
            ->limit(250)
            ->get(['id', 'invoice_number', 'po_number', 'recipient_name', 'vendor_company_name', 'invoiced_at', 'source']);

        $invoiceDefaults = [];
        $duplicateFrom = null;
        $paymentTermPreview = [];

        if ($request->filled('duplicate_from')) {
            $duplicateFrom = Invoice::query()
                ->with(['purchaseOrders', 'items'])
                ->findOrFail((int) $request->query('duplicate_from'));

            $invoiceDefaults = $this->formDefaultsFromInvoice($duplicateFrom);

            if ($duplicateFrom->isManual()) {
                unset($invoiceDefaults['manual_po_number']);
            }
        } elseif ($request->filled('po_id')) {
            $fromPo = $this->defaultsFromPaymentTerms($request);
            $invoiceDefaults = $fromPo['defaults'];
            $paymentTermPreview = $fromPo['preview'];
        }

        return view('procurement.invoices.create', [
            'purchaseOrders' => $purchaseOrders,
            'existingInvoices' => $existingInvoices,
            'duplicateFrom' => $duplicateFrom,
            'invoiceDefaults' => $invoiceDefaults,
            'paymentTermPreview' => $paymentTermPreview,
            'suggestedManualPoNumber' => $this->manualPoNumberGenerator->next(),
            'allowDuplicate' => true,
        ]);
    }

    public function store(StoreInvoiceRequest $request): RedirectResponse
    {
        $payload = $this->resolveInvoicePayload($request);
        if ($payload instanceof RedirectResponse) {
            return $payload;
        }

        $invoice = $this->persistence->create(
            $payload['header'],
            $payload['purchase_order_ids'],
            $payload['lines'],
            $payload['payment_term_ids'] ?? [],
        );

        return redirect()
            ->route('invoices.show', $invoice)
            ->with('success', 'Invoice created successfully.');
    }

    public function show(Invoice $invoice): View
    {
        $invoice->load(['items', 'purchaseOrders.procurementRequest', 'purchaseOrders.paymentTermRows', 'creator']);

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

        return view('procurement.invoices.show', [
            'invoice' => $invoice,
            'poItemsById' => $poItemsById,
            'projectZoneResolver' => $projectZoneResolver,
        ]);
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
            ->route('invoices.edit', $invoice)
            ->with('success', 'Invoice updated successfully.');
    }

    public function storeSignedDocument(StoreInvoiceSignedDocumentRequest $request, Invoice $invoice): RedirectResponse
    {
        try {
            $this->signedDocuments->store($invoice, $request->file('document'));
        } catch (\RuntimeException $exception) {
            return back()->withErrors(['document' => $exception->getMessage()]);
        }

        return back()->with('success', 'Signed invoice document uploaded.');
    }

    public function destroy(Invoice $invoice): RedirectResponse
    {
        $invoiceNumber = $invoice->invoice_number;
        $this->signedDocuments->purge($invoice);
        $invoice->delete();

        return redirect()
            ->route('invoices.index')
            ->with('success', "Invoice {$invoiceNumber} deleted successfully.");
    }

    public function print(Invoice $invoice): View
    {
        return view('procurement.invoices.print', $this->printContext($invoice));
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
                'project' => $projectZoneResolver->projectForPoItem($item),
                'zone' => $projectZoneResolver->zoneForPoItem($item),
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
     * Shared read-only context for print view and Excel export.
     *
     * @return array{
     *     invoice: Invoice,
     *     poItemsById: Collection<int|string, PurchaseOrderItem>,
     *     projectZoneResolver: InvoiceProjectZoneResolver,
     *     unitsByLineCode: array<string, string>
     * }
     */
    private function printContext(Invoice $invoice): array
    {
        $invoice->load(['items', 'purchaseOrders.procurementRequest', 'purchaseOrders.paymentTermRows', 'creator']);

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

        return [
            'invoice' => $invoice,
            'poItemsById' => $poItemsById,
            'projectZoneResolver' => InvoiceProjectZoneResolver::fromPurchaseOrderItems($poItemsById->values()),
            'unitsByLineCode' => ProcurementRequestLineUnitLookup::unitsByLineCodeForPurchaseOrderItems($poItemsById->values()),
        ];
    }

    /**
     * @return array{defaults: array<string, mixed>, preview: list<array{id: int, milestone: string, amount: float|null, percentage: float|null}>}
     */
    private function defaultsFromPaymentTerms(Request $request): array
    {
        $poId = (int) $request->query('po_id');
        $termIds = collect($request->query('milestone_ids', []))
            ->map(fn ($id) => (int) $id)
            ->filter()
            ->unique()
            ->values()
            ->take(1);

        if ($poId < 1 || $termIds->isEmpty()) {
            return ['defaults' => [], 'preview' => []];
        }

        $terms = PurchaseOrderPaymentTerm::query()
            ->with('invoice')
            ->where('purchase_order_id', $poId)
            ->whereIn('id', $termIds)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        $available = $terms->filter(fn (PurchaseOrderPaymentTerm $term) => $term->invoice_id === null)->values();

        if ($available->isEmpty()) {
            return ['defaults' => [], 'preview' => []];
        }

        $purchaseOrder = PurchaseOrder::query()->find($poId);
        $poCurrency = $purchaseOrder?->currency_code ?: 'USD';
        $termCurrencies = $available
            ->map(fn (PurchaseOrderPaymentTerm $term) => $term->displayCurrency($poCurrency))
            ->filter()
            ->unique()
            ->values();

        $preview = $available->map(fn (PurchaseOrderPaymentTerm $term) => [
            'id' => $term->id,
            'milestone' => (string) $term->milestone,
            'amount' => $term->amount !== null ? (float) $term->amount : null,
            'percentage' => $term->percentage !== null ? (float) $term->percentage : null,
        ])->all();

        return [
            'defaults' => [
                'source' => Invoice::SOURCE_PO_PAYMENT_TERM,
                'purchase_order_ids' => [$poId],
                'po_payment_term_ids' => $available->pluck('id')->all(),
                'currency_code' => $termCurrencies->count() === 1 ? $termCurrencies->first() : $poCurrency,
            ],
            'preview' => $preview,
        ];
    }

    /**
     * @return Builder<Invoice>
     */
    private function listQuery(Request $request): Builder
    {
        $query = Invoice::query()->latest('id');

        if ($request->filled('q')) {
            $term = '%'.$request->string('q').'%';
            $query->where(function ($q) use ($term) {
                $q->where('invoice_number', 'like', $term)
                    ->orWhere('po_number', 'like', $term)
                    ->orWhere('recipient_name', 'like', $term)
                    ->orWhere('vendor_company_name', 'like', $term);
            });
        }

        return $query;
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

        if ($request->isManualSource()) {
            return $this->resolveManualInvoicePayload($request, $validated, $invoice);
        }

        if ($request->isPaymentTermSource()) {
            return $this->resolvePaymentTermInvoicePayload($request, $validated, $invoice);
        }

        return $this->resolvePurchaseOrderInvoicePayload($request, $validated, $invoice);
    }

    /**
     * @param  array<string, mixed>  $validated
     * @return array{
     *     header: array<string, mixed>,
     *     purchase_order_ids: list<int>,
     *     lines: list<array<string, mixed>>
     * }|RedirectResponse
     */
    private function resolveManualInvoicePayload(
        StoreInvoiceRequest $request,
        array $validated,
        ?Invoice $invoice = null,
    ): array|RedirectResponse {
        $lines = $this->buildManualLines(
            $validated['manual_lines'] ?? [],
            trim((string) ($validated['manual_project_name'] ?? '')),
        );

        if ($lines === []) {
            return back()->withInput()->withErrors(['manual_lines' => 'Add at least one line item.']);
        }

        $currencyCode = InvoiceCurrencyResolver::normalizeCode($validated['currency_code'] ?? null)
            ?? InvoiceCurrencyResolver::DEFAULT;

        $manualPoNumber = trim((string) ($validated['manual_po_number'] ?? ''));
        if ($manualPoNumber === '') {
            $manualPoNumber = $invoice !== null && filled($invoice->po_number)
                ? trim($invoice->po_number)
                : $this->manualPoNumberGenerator->next();
        }

        $header = InvoicePersistenceService::headerFromManual(
            trim($validated['recipient_name']),
            filled($validated['project_manager_name'] ?? null)
                ? trim((string) $validated['project_manager_name'])
                : null,
            (int) $request->user()->id,
            $currencyCode,
            $manualPoNumber,
            filled($validated['manual_vendor_name'] ?? null)
                ? trim((string) $validated['manual_vendor_name'])
                : null,
            $validated['notes'] ?? [],
            [],
        );

        if ($invoice !== null) {
            $header['created_by'] = $invoice->created_by;
            $header['invoiced_at'] = $invoice->invoiced_at?->toDateString() ?? now()->toDateString();
        }

        return [
            'header' => $header,
            'purchase_order_ids' => [],
            'lines' => $lines,
            'payment_term_ids' => [],
        ];
    }

    /**
     * @param  array<string, mixed>  $validated
     * @return array{
     *     header: array<string, mixed>,
     *     purchase_order_ids: list<int>,
     *     lines: list<array<string, mixed>>,
     *     payment_term_ids: list<int>
     * }|RedirectResponse
     */
    private function resolvePaymentTermInvoicePayload(
        StoreInvoiceRequest $request,
        array $validated,
        ?Invoice $invoice = null,
    ): array|RedirectResponse {
        $purchaseOrderIds = collect($validated['purchase_order_ids'])
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values()
            ->all();

        $purchaseOrders = PurchaseOrder::query()
            ->with(['vendor', 'procurementRequest', 'items'])
            ->whereIn('id', $purchaseOrderIds)
            ->get()
            ->sortBy(fn (PurchaseOrder $po) => array_search($po->id, $purchaseOrderIds, true))
            ->values();

        $termIds = collect($validated['po_payment_term_ids'] ?? [])
            ->map(fn ($id) => (int) $id)
            ->filter()
            ->unique()
            ->values();

        $terms = PurchaseOrderPaymentTerm::query()
            ->whereIn('id', $termIds)
            ->where(function ($query) use ($invoice) {
                $query->whereNull('invoice_id');
                if ($invoice !== null) {
                    $query->orWhere('invoice_id', $invoice->id);
                }
            })
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        if ($terms->isEmpty()) {
            return back()->withInput()->withErrors(['po_payment_term_ids' => 'Selected payment terms could not be invoiced.']);
        }

        $primaryPurchaseOrder = $purchaseOrders->first();
        $poTotal = round((float) ($primaryPurchaseOrder?->total_price ?? 0), 2);
        $installmentTotal = round((float) $terms->sum(fn (PurchaseOrderPaymentTerm $term) => $term->resolvedAmount($poTotal)), 2);

        $selectedItems = PurchaseOrderItem::query()
            ->whereIn('purchase_order_id', $purchaseOrderIds)
            ->with([
                'purchaseOrder.procurementRequest.items.project',
                'purchaseOrder.procurementRequest.items.zone',
                'purchaseOrder.procurementRequest.project',
                'purchaseOrder.procurementRequest.zone',
            ])
            ->orderBy('purchase_order_id')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        $lines = $this->lineBuilder->build($selectedItems, []);
        if ($lines === []) {
            return back()->withInput()->withErrors(['purchase_order_item_ids' => 'This purchase order has no line items to show on the invoice.']);
        }

        $projectZoneResolver = InvoiceProjectZoneResolver::fromPurchaseOrderItems($selectedItems);
        $lines = array_map(function (array $line) use ($projectZoneResolver, $selectedItems) {
            $sourceIds = collect($line['source_purchase_order_item_ids'] ?? [])
                ->map(fn ($id) => (int) $id)
                ->all();
            $sourceItems = $selectedItems->whereIn('id', $sourceIds)->values();
            $line['project_zone'] = self::buildStoredProjectZone($sourceItems, [], $projectZoneResolver);

            return $line;
        }, $lines);

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
            false,
            $currencyCode,
            $validated['notes'] ?? [],
            [],
        );
        $header['source'] = Invoice::SOURCE_PO_PAYMENT_TERM;
        $header['forced_total_price'] = $installmentTotal;

        if ($invoice !== null) {
            $header['created_by'] = $invoice->created_by;
            $header['invoiced_at'] = $invoice->invoiced_at?->toDateString() ?? now()->toDateString();
        }

        return [
            'header' => $header,
            'purchase_order_ids' => $purchaseOrderIds,
            'lines' => $lines,
            'payment_term_ids' => $termIds->all(),
        ];
    }

    /**
     * @param  array<string, mixed>  $validated
     * @return array{
     *     header: array<string, mixed>,
     *     purchase_order_ids: list<int>,
     *     lines: list<array<string, mixed>>
     * }|RedirectResponse
     */
    private function resolvePurchaseOrderInvoicePayload(
        StoreInvoiceRequest $request,
        array $validated,
        ?Invoice $invoice = null,
    ): array|RedirectResponse {
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

        $selectedIds = collect($validated['purchase_order_item_ids'] ?? [])
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

        $margins = collect($validated['purchase_order_item_margins'] ?? [])
            ->mapWithKeys(fn ($margin, $id) => [(int) $id => (float) ($margin ?? 0)])
            ->all();
        $lines = $this->applyMarginsToPoLines($lines, $margins, $selectedItems);

        $projectZoneResolver = InvoiceProjectZoneResolver::fromPurchaseOrderItems($selectedItems);
        $zoneOverrides = collect($validated['purchase_order_item_zones'] ?? [])
            ->mapWithKeys(fn ($zone, $id) => [(int) $id => trim((string) $zone)])
            ->all();
        $lines = array_map(function (array $line) use ($projectZoneResolver, $selectedItems, $zoneOverrides) {
            $sourceIds = collect($line['source_purchase_order_item_ids'] ?? [])
                ->map(fn ($id) => (int) $id)
                ->all();
            $sourceItems = $selectedItems->whereIn('id', $sourceIds)->values();
            $line['project_zone'] = self::buildStoredProjectZone($sourceItems, $zoneOverrides, $projectZoneResolver);

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
            'payment_term_ids' => [],
        ];
    }

    /**
     * @param  list<array<string, mixed>>  $manualLines
     * @return list<array<string, mixed>>
     */
    private function buildManualLines(array $manualLines, string $projectName = ''): array
    {
        $lines = [];
        $lineNumber = 1;
        $projectName = trim($projectName);

        foreach ($manualLines as $row) {
            $description = trim((string) ($row['description'] ?? ''));
            $quantity = round((float) ($row['quantity'] ?? 0), 3);
            $unitPrice = round((float) ($row['unit_price'] ?? 0), 2);
            $unit = trim((string) ($row['unit'] ?? ''));
            $zone = trim((string) ($row['zone'] ?? ''));

            if ($zone === '') {
                $stored = trim((string) ($row['project_zone'] ?? ''));
                if ($stored !== '') {
                    $parts = self::manualLineParts($stored);
                    $zone = $parts['zone'] !== '' ? $parts['zone'] : $parts['project'];
                }
            }

            if ($description === '' || $quantity <= 0) {
                continue;
            }

            $marginPercentage = round((float) ($row['margin_percentage'] ?? 0), 2);

            $lines[] = [
                'line_number' => $lineNumber++,
                'description' => $description,
                'project_zone' => self::combineProjectZone($projectName, $zone),
                'quantity' => $quantity,
                'unit' => $unit !== '' ? $unit : null,
                'unit_price' => $unitPrice,
                'margin_percentage' => $marginPercentage,
                'line_total' => self::calculateLineTotal($quantity, $unitPrice, $marginPercentage),
                'source_purchase_order_item_ids' => null,
            ];
        }

        return $lines;
    }

    /**
     * @return array{project: string, zone: string}
     */
    private static function manualLineParts(?string $stored): array
    {
        $stored = trim((string) $stored);

        if ($stored === '') {
            return ['project' => '', 'zone' => ''];
        }

        if (str_contains($stored, '/')) {
            return InvoiceProjectZoneResolver::splitStoredLabel($stored);
        }

        return ['project' => '', 'zone' => $stored];
    }

    private static function combineProjectZone(string $project, string $zone): ?string
    {
        if ($project !== '' && $zone !== '') {
            return "{$project}/{$zone}";
        }

        if ($project !== '') {
            return $project;
        }

        return $zone !== '' ? $zone : null;
    }

    /**
     * @return array<string, mixed>
     */
    private function formDefaultsFromInvoice(Invoice $invoice): array
    {
        if ($invoice->isManual()) {
            return [
                'source' => Invoice::SOURCE_MANUAL,
                'manual_po_number' => $invoice->po_number,
                'manual_vendor_name' => $invoice->vendor_company_name,
                'manual_project_name' => $this->manualProjectNameFromInvoice($invoice),
                'manual_lines' => $invoice->items->map(function ($item) {
                    $parts = self::manualLineParts($item->project_zone);

                    return [
                        'zone' => $parts['zone'],
                        'description' => $item->description,
                        'quantity' => $item->quantity,
                        'unit' => $item->unit ?? '',
                        'unit_price' => $item->unit_price,
                        'margin_percentage' => $item->margin_percentage ?? 0,
                    ];
                })->values()->all(),
                'recipient_name' => $invoice->recipient_name,
                'project_manager_name' => $invoice->project_manager_name,
                'notes' => ($notes = $invoice->displayNotes()) !== [] ? $notes : [''],
                'currency_code' => $invoice->currency_code ?? 'USD',
            ];
        }

        if ($invoice->isFromPaymentTerm()) {
            $invoice->loadMissing('purchaseOrderPaymentTerms');
            $notes = $invoice->displayNotes();

            return [
                'source' => Invoice::SOURCE_PO_PAYMENT_TERM,
                'purchase_order_ids' => $invoice->purchaseOrders->pluck('id')->all(),
                'po_payment_term_ids' => $invoice->purchaseOrderPaymentTerms->pluck('id')->all(),
                'recipient_name' => $invoice->recipient_name,
                'project_manager_name' => $invoice->project_manager_name,
                'notes' => $notes !== [] ? $notes : [''],
                'currency_code' => $invoice->currency_code ?? 'USD',
            ];
        }

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
            'source' => Invoice::SOURCE_PURCHASE_ORDER,
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
            'custom_fees' => $invoice->feeRowsForEdit(),
            'purchase_order_item_zones' => $this->purchaseOrderItemZonesFromInvoice($invoice),
            'purchase_order_item_margins' => $this->purchaseOrderItemMarginsFromInvoice($invoice),
        ];
    }

    private function manualProjectNameFromInvoice(Invoice $invoice): string
    {
        $projects = [];

        foreach ($invoice->items as $item) {
            $project = self::manualLineParts($item->project_zone)['project'];

            if ($project !== '') {
                $projects[$project] = true;
            }
        }

        return implode(' / ', array_keys($projects));
    }

    /**
     * @return array<int, string>
     */
    private function purchaseOrderItemZonesFromInvoice(Invoice $invoice): array
    {
        $allSourceIds = $invoice->items
            ->flatMap(fn ($item) => $item->source_purchase_order_item_ids ?? [])
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->values();

        if ($allSourceIds->isEmpty()) {
            return [];
        }

        $poItemsById = PurchaseOrderItem::query()
            ->whereIn('id', $allSourceIds)
            ->with([
                'purchaseOrder.procurementRequest.items.project',
                'purchaseOrder.procurementRequest.items.zone',
                'purchaseOrder.procurementRequest.project',
                'purchaseOrder.procurementRequest.zone',
            ])
            ->get()
            ->keyBy('id');

        $resolver = InvoiceProjectZoneResolver::fromPurchaseOrderItems($poItemsById->values());
        $zones = [];

        foreach ($invoice->items as $item) {
            $zone = $resolver->zoneForInvoiceItem($item, $poItemsById);

            if ($zone === null || $zone === '') {
                continue;
            }

            foreach ($item->source_purchase_order_item_ids ?? [] as $sourceId) {
                $zones[(int) $sourceId] = $zone;
            }
        }

        return $zones;
    }

    /**
     * @return array<int, float>
     */
    private function purchaseOrderItemMarginsFromInvoice(Invoice $invoice): array
    {
        $margins = [];

        foreach ($invoice->items as $item) {
            $sourceIds = collect($item->source_purchase_order_item_ids ?? [])
                ->map(fn ($id) => (int) $id)
                ->values();

            if ($sourceIds->count() === 1) {
                $margins[$sourceIds->first()] = (float) ($item->margin_percentage ?? 0);
            }
        }

        return $margins;
    }

    /**
     * @param  list<array<string, mixed>>  $lines
     * @param  array<int, float>  $margins
     * @param  \Illuminate\Support\Collection<int, PurchaseOrderItem>  $selectedItems
     * @return list<array<string, mixed>>
     */
    private function applyMarginsToPoLines(array $lines, array $margins, \Illuminate\Support\Collection $selectedItems): array
    {
        return array_map(function (array $line) use ($margins, $selectedItems) {
            $sourceIds = collect($line['source_purchase_order_item_ids'] ?? [])
                ->map(fn ($id) => (int) $id)
                ->values()
                ->all();

            if (count($sourceIds) === 1) {
                $margin = round((float) ($margins[$sourceIds[0]] ?? 0), 2);
                $line['margin_percentage'] = $margin;
                $line['line_total'] = self::calculateLineTotal(
                    (float) $line['quantity'],
                    (float) $line['unit_price'],
                    $margin,
                );

                return $line;
            }

            $adjustedTotal = 0.0;

            foreach ($sourceIds as $itemId) {
                $poItem = $selectedItems->firstWhere('id', $itemId);

                if ($poItem === null) {
                    continue;
                }

                $margin = round((float) ($margins[$itemId] ?? 0), 2);
                $adjustedTotal += self::calculateLineTotal(
                    (float) $poItem->quantity,
                    (float) $poItem->unit_price,
                    $margin,
                );
            }

            $adjustedTotal = round($adjustedTotal, 2);
            $line['line_total'] = $adjustedTotal;
            $line['unit_price'] = $adjustedTotal;
            $line['margin_percentage'] = 0;

            return $line;
        }, $lines);
    }

    private static function calculateLineTotal(float $quantity, float $unitPrice, float $marginPercentage): float
    {
        return round($quantity * $unitPrice * (1 + $marginPercentage / 100), 2);
    }

    /**
     * @param  \Illuminate\Support\Collection<int, PurchaseOrderItem>  $sourceItems
     * @param  array<int, string>  $zoneOverrides
     */
    private static function buildStoredProjectZone(
        \Illuminate\Support\Collection $sourceItems,
        array $zoneOverrides,
        InvoiceProjectZoneResolver $resolver,
    ): ?string {
        $projects = [];
        $zones = [];

        foreach ($sourceItems as $item) {
            $itemId = (int) $item->id;
            $project = trim((string) ($resolver->projectForPoItem($item) ?? ''));
            $defaultZone = trim((string) ($resolver->zoneForPoItem($item) ?? ''));
            $zone = trim((string) ($zoneOverrides[$itemId] ?? $defaultZone));

            if ($project !== '') {
                $projects[$project] = true;
            }

            if ($zone !== '') {
                $zones[$zone] = true;
            }
        }

        $projectLabel = implode('; ', array_keys($projects));
        $zoneLabel = implode('; ', array_keys($zones));

        if ($projectLabel !== '' && $zoneLabel !== '') {
            return "{$projectLabel}/{$zoneLabel}";
        }

        if ($projectLabel !== '') {
            return $projectLabel;
        }

        return $zoneLabel !== '' ? $zoneLabel : null;
    }
}
