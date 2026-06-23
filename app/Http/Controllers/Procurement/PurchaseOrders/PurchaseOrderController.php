<?php



namespace App\Http\Controllers\Procurement\PurchaseOrders;



use App\Enums\Procurement\PurchaseOrders\PaymentStatus;

use App\Enums\Procurement\PurchaseOrders\PurchaseOrderStatus;

use App\Http\Controllers\Controller;

use App\Http\Requests\Procurement\PurchaseOrders\StorePurchaseOrderRequest;

use App\Http\Requests\Procurement\PurchaseOrders\UpdatePurchaseOrderRequest;

use App\Models\Procurement\PurchaseOrders\PurchaseOrder;
use App\Models\Procurement\ProcurementRequests\ProcurementRequest;
use App\Models\Procurement\Vendors\Vendor;
use App\Models\User;

use App\Services\Procurement\PurchaseOrders\ProcurementRequestCommercialTermsForPurchaseOrder;
use App\Services\Procurement\PurchaseOrders\ProcurementRequestLineUnitLookup;
use App\Services\Procurement\PurchaseOrders\ProcurementRequestLinesForPurchaseOrderPresenter;
use App\Services\Procurement\PurchaseOrders\ProcurementRequestOptionsForPurchaseOrderQuery;
use App\Services\Procurement\PurchaseOrders\PurchaseOrderPrintLabels;
use App\Services\Procurement\PurchaseOrders\PurchaseOrderProcurementRequestContext;
use App\Services\Procurement\Vendors\VendorSelectOptions;
use App\Services\Procurement\PurchaseOrders\PurchaseOrderCodeGenerator;

use App\Services\Procurement\PurchaseOrders\PurchaseOrderPayloadResolver;

use App\Services\Procurement\PurchaseOrders\PurchaseOrderPersistenceService;

use App\Services\Procurement\PurchaseOrders\PurchaseOrderVendorPayloadResolver;

use App\Services\Procurement\PurchaseOrders\VendorPurchaseOrderSnapshot;

use App\Services\Procurement\Rfqs\RfqGeneralTermsService;

use App\Enums\Procurement\BuyerCompany;

use App\Support\Procurement\RfqTerms;

use Illuminate\Http\JsonResponse;

use Illuminate\Http\RedirectResponse;

use Illuminate\Http\Request;

use Illuminate\View\View;



class PurchaseOrderController extends Controller

{

    public function __construct(

        private readonly PurchaseOrderPersistenceService $persistence,

        private readonly RfqGeneralTermsService $termsService,

    ) {}



    public function index(Request $request): View

    {

        $perPage = max(1, min(100, (int) $request->query('per_page', 15)));



        $query = PurchaseOrder::query()

            ->with(['vendor', 'creator'])

            ->latest();

        $user = $request->user();

        if ($user?->scopesPurchaseOrdersToOwn()) {
            $query->where('created_by', $user->id);
        }

        if ($request->filled('q')) {

            $term = '%'.$request->string('q').'%';

            $query->where(function ($q) use ($term) {

                $q->where('po_number', 'like', $term)

                    ->orWhere('vendor_company_name', 'like', $term)

                    ->orWhereHas('creator', fn ($c) => $c->where('name', 'like', $term));

            });

        }



        if ($request->filled('status')) {

            $query->where('status', $request->string('status'));

        }



        if ($request->filled('payment_status')) {

            $query->where('payment_status', $request->string('payment_status'));

        }



        $purchaseOrders = $query->paginate($perPage)->withQueryString();



        return view('procurement.purchase-orders.index', [

            'purchaseOrders' => $purchaseOrders,

            'statuses' => PurchaseOrderStatus::cases(),

            'paymentStatuses' => PaymentStatus::cases(),

        ]);

    }



    public function create(): View

    {

        $nextCode = app(PurchaseOrderCodeGenerator::class)->next();

        $procurementRequestOptions = app(ProcurementRequestOptionsForPurchaseOrderQuery::class)->options();

        return view('procurement.purchase-orders.create', [

            'nextCode' => $nextCode,

            'selectedVendor' => null,
            'vendorSelectOptions' => VendorSelectOptions::all(),
            'procurementRequestOptions' => $procurementRequestOptions,
            'prContext' => PurchaseOrderProcurementRequestContext::emptyAggregates(),
            'scopeTypeKeys' => [],

            'defaultItems' => [['item' => '', 'description' => '', 'quantity' => 1, 'unit' => '', 'unit_price' => 0]],

            'poTerms' => $this->termsForForm(),

            'scopeTermsMap' => $this->termsService->termsMapForRfqForm(),

        ]);

    }



    public function store(StorePurchaseOrderRequest $request): RedirectResponse

    {

        $validated = $request->validated();

        $items = PurchaseOrderPersistenceService::normalizeItems(
            $validated['items'] ?? [],
            isset($validated['procurement_request_id']) ? (int) $validated['procurement_request_id'] : null,
        );

        unset($validated['items']);



        if ($items === []) {

            return back()->withInput()->withErrors(['items' => 'Add at least one line item with a description.']);

        }



        PurchaseOrderPayloadResolver::finalizeForStore($validated);

        PurchaseOrderPayloadResolver::normalizeCurrency($validated, $request->user());

        ProcurementRequestCommercialTermsForPurchaseOrder::normalizeHeader($validated);

        $validated = PurchaseOrderVendorPayloadResolver::mergeMissingFromVendor($validated);

        $validated['created_by'] = $request->user()->id;

        $validated['status'] ??= PurchaseOrderStatus::Draft->value;

        $validated['payment_status'] ??= PaymentStatus::Unpaid->value;

        $validated['ordered_at'] ??= now()->toDateString();



        $purchaseOrder = $this->persistence->create($validated, $items);



        return redirect()

            ->route('purchase-orders.show', $purchaseOrder)

            ->with('success', 'Purchase order created successfully.');

    }



    public function show(Request $request, PurchaseOrder $purchaseOrder): View

    {
        $this->authorizePurchaseOrderView($request->user(), $purchaseOrder);

        $purchaseOrder->load([
            'vendor.primaryLocation',
            'vendor.businessTypes',
            'vendor.vendorCategories.category',
            'vendor.vendorCategories.subcategory',
            'creator',
            'items',
            'procurementRequest.headerDocuments',
            'procurementRequest.items.project',
            'procurementRequest.items.documents',
        ]);

        $prContext = PurchaseOrderProcurementRequestContext::resolve($purchaseOrder);

        return view('procurement.purchase-orders.show', [

            'purchaseOrder' => $purchaseOrder,

            'buyerCompany' => BuyerCompany::forDisplay($purchaseOrder),

            'terms' => $this->resolvedTermsForPurchaseOrder($purchaseOrder),

            'prContext' => $prContext,

        ]);

    }



    public function print(Request $request, PurchaseOrder $purchaseOrder): View

    {
        $this->authorizePurchaseOrderView($request->user(), $purchaseOrder);

        $printLabels = PurchaseOrderPrintLabels::resolve($request->query('locale'));

        $purchaseOrder->load([
            'vendor.primaryLocation',
            'vendor.businessTypes',
            'vendor.vendorCategories.category',
            'vendor.vendorCategories.subcategory',
            'creator',
            'items',
            'procurementRequest.headerDocuments',
            'procurementRequest.items.project',
            'procurementRequest.items.documents',
            'procurementRequest.category',
            'procurementRequest.subcategory',
        ]);

        return view('procurement.purchase-orders.print', [

            'purchaseOrder' => $purchaseOrder,

            'buyerCompany' => BuyerCompany::forDisplay($purchaseOrder),

            'terms' => $this->resolvedTermsForPurchaseOrder($purchaseOrder, $printLabels->locale()),

            'prContext' => PurchaseOrderProcurementRequestContext::resolve($purchaseOrder, $printLabels),

            'printLabels' => $printLabels,

        ]);

    }



    public function edit(PurchaseOrder $purchaseOrder): View

    {

        $purchaseOrder->load([
            'items',
            'creator',
            'procurementRequest.headerDocuments',
            'procurementRequest.items.project',
            'procurementRequest.items.documents',
        ]);

        $selectedVendor = $purchaseOrder->vendor_id
            ? Vendor::query()->find($purchaseOrder->vendor_id, ['id', 'vendor_code', 'name'])
            : null;
        $procurementRequestOptions = app(ProcurementRequestOptionsForPurchaseOrderQuery::class)
            ->options($purchaseOrder->procurement_request_id);

        $prContext = PurchaseOrderProcurementRequestContext::resolve($purchaseOrder);
        $scopeTypeKeys = PurchaseOrderProcurementRequestContext::scopeTypeKeys(
            collect($prContext['pr_items_by_line'])->values()
        );



        $unitsByLineCode = ProcurementRequestLineUnitLookup::unitsByLineCode($purchaseOrder->procurementRequest);

        $defaultItems = $purchaseOrder->items->map(fn ($row) => [

            'item' => $row->item,

            'description' => $row->description,

            'quantity' => $row->quantity,

            'unit' => ProcurementRequestLineUnitLookup::resolveForPurchaseOrderItem($row, $unitsByLineCode) ?? '',

            'unit_price' => $row->unit_price,

        ])->all();



        if ($defaultItems === []) {

            $defaultItems = [['item' => '', 'description' => '', 'quantity' => 1, 'unit' => '', 'unit_price' => 0]];

        }



        return view('procurement.purchase-orders.edit', [

            'purchaseOrder' => $purchaseOrder,

            'selectedVendor' => $selectedVendor,
            'vendorSelectOptions' => VendorSelectOptions::all(),
            'procurementRequestOptions' => $procurementRequestOptions,
            'prContext' => $prContext,
            'scopeTypeKeys' => $scopeTypeKeys,

            'defaultItems' => $defaultItems,

            'poTerms' => $this->termsForForm($purchaseOrder),

            'scopeTermsMap' => $this->termsService->termsMapForRfqForm(),

        ]);

    }



    public function update(UpdatePurchaseOrderRequest $request, PurchaseOrder $purchaseOrder): RedirectResponse

    {

        $validated = $request->validated();

        $items = PurchaseOrderPersistenceService::normalizeItems(
            $validated['items'] ?? [],
            isset($validated['procurement_request_id']) ? (int) $validated['procurement_request_id'] : null,
        );

        unset($validated['items']);



        if ($items === []) {

            return back()->withInput()->withErrors(['items' => 'Add at least one line item with a description.']);

        }



        PurchaseOrderPayloadResolver::finalizeForUpdate($validated);

        PurchaseOrderPayloadResolver::normalizeCurrency($validated, $request->user());

        ProcurementRequestCommercialTermsForPurchaseOrder::normalizeHeader($validated);

        $validated = PurchaseOrderVendorPayloadResolver::mergeMissingFromVendor($validated);

        $this->persistence->update($purchaseOrder, $validated, $items);



        return redirect()

            ->route('purchase-orders.show', $purchaseOrder)

            ->with('success', 'Purchase order updated successfully.');

    }



    public function destroy(PurchaseOrder $purchaseOrder): RedirectResponse

    {

        $purchaseOrder->delete();



        return redirect()

            ->route('purchase-orders.index')

            ->with('success', 'Purchase order deleted successfully.');

    }



    public function vendorSnapshot(Vendor $vendor): JsonResponse

    {

        return response()->json(VendorPurchaseOrderSnapshot::fromVendor($vendor));

    }

    public function procurementRequestLines(
        ProcurementRequest $procurementRequest,
        ProcurementRequestLinesForPurchaseOrderPresenter $presenter,
    ): JsonResponse {
        return response()->json($presenter->present($procurementRequest));
    }



    private function authorizePurchaseOrderView(?User $user, PurchaseOrder $purchaseOrder): void
    {
        if ($user === null || ! $user->canViewPurchaseOrder($purchaseOrder)) {
            abort(403, 'You do not have permission to view this purchase order.');
        }
    }

    /**
     * @return array{general: list<string>, custom_rows: list<array{key: string, value: string}>}
     */
    private function termsForForm(?PurchaseOrder $purchaseOrder = null): array
    {
        $locale = old('terms_locale', $purchaseOrder?->terms_locale);

        $customFromOld = old('terms_custom');
        if (is_array($customFromOld)) {
            return [
                'general' => [],
                'custom_rows' => $this->customRowsFromOldInput($customFromOld),
            ];
        }

        if ($purchaseOrder !== null) {
            return [
                'general' => [],
                'custom_rows' => $this->termsService->customTermRowsForForm($purchaseOrder->terms, $locale),
            ];
        }

        return ['general' => [], 'custom_rows' => []];
    }

    /**
     * @param  array<int, mixed>  $raw
     * @return list<array{key: string, value: string}>
     */
    private function customRowsFromOldInput(array $raw): array
    {
        $rows = [];
        foreach ($raw as $row) {
            if (is_array($row)) {
                $value = trim((string) ($row['value'] ?? ''));
                if ($value === '') {
                    continue;
                }
                $rows[] = [
                    'key' => trim((string) ($row['key'] ?? '')),
                    'value' => $value,
                ];
            } else {
                $split = $this->termsService->splitKeyValueText((string) $row);
                if ($split['value'] !== '') {
                    $rows[] = $split;
                }
            }
        }

        return $rows;
    }



    /**

     * @return list<string>

     */

    private function resolvedTermsForPurchaseOrder(PurchaseOrder $purchaseOrder, ?string $termsLocale = null): array
    {
        $locale = $termsLocale ?? $purchaseOrder->terms_locale;

        $resolved = $this->termsService->resolveStoredTermsForLocale(
            $purchaseOrder->terms,
            $locale,
        );
        if ($resolved !== []) {
            return $resolved;
        }

        return RfqTerms::legacyDefaults($locale);
    }

}

