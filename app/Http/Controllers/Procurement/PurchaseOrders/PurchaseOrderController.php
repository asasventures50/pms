<?php



namespace App\Http\Controllers\Procurement\PurchaseOrders;



use App\Enums\Procurement\PurchaseOrders\PaymentStatus;

use App\Enums\Procurement\PurchaseOrders\PurchaseOrderStatus;

use App\Http\Controllers\Controller;

use App\Http\Requests\Procurement\PurchaseOrders\StorePurchaseOrderRequest;

use App\Http\Requests\Procurement\PurchaseOrders\UpdatePurchaseOrderRequest;

use App\Models\Procurement\PurchaseOrders\PurchaseOrder;

use App\Models\Procurement\Vendors\Vendor;

use App\Services\Procurement\PurchaseOrders\PurchaseOrderCodeGenerator;

use App\Services\Procurement\PurchaseOrders\PurchaseOrderPayloadResolver;

use App\Services\Procurement\PurchaseOrders\PurchaseOrderPersistenceService;

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

        $vendors = Vendor::query()->orderBy('name')->get(['id', 'vendor_code', 'name']);

        return view('procurement.purchase-orders.create', [

            'nextCode' => $nextCode,

            'vendors' => $vendors,

            'defaultItems' => [['item' => '', 'description' => '', 'quantity' => 1, 'unit_price' => 0]],

            'poTerms' => $this->termsForForm(),

            'scopeTermsMap' => $this->termsService->termsMapForRfqForm(),

        ]);

    }



    public function store(StorePurchaseOrderRequest $request): RedirectResponse

    {

        $validated = $request->validated();

        $items = PurchaseOrderPersistenceService::normalizeItems($validated['items'] ?? []);

        unset($validated['items']);



        if ($items === []) {

            return back()->withInput()->withErrors(['items' => 'Add at least one line item with a description.']);

        }



        PurchaseOrderPayloadResolver::finalizeForStore($validated);

        PurchaseOrderPayloadResolver::normalizeCurrency($validated, $request->user());

        $validated['created_by'] = $request->user()->id;

        $validated['status'] ??= PurchaseOrderStatus::Draft->value;

        $validated['payment_status'] ??= PaymentStatus::Unpaid->value;

        $validated['ordered_at'] ??= now()->toDateString();



        $purchaseOrder = $this->persistence->create($validated, $items);



        return redirect()

            ->route('purchase-orders.show', $purchaseOrder)

            ->with('success', 'Purchase order created successfully.');

    }



    public function show(PurchaseOrder $purchaseOrder): View

    {

        $purchaseOrder->load(['vendor', 'creator', 'items']);



        return view('procurement.purchase-orders.show', [

            'purchaseOrder' => $purchaseOrder,

            'buyerCompany' => BuyerCompany::forDisplay($purchaseOrder),

            'terms' => $this->resolvedTermsForPurchaseOrder($purchaseOrder),

        ]);

    }



    public function edit(PurchaseOrder $purchaseOrder): View

    {

        $purchaseOrder->load(['items', 'creator']);

        $vendors = Vendor::query()->orderBy('name')->get(['id', 'vendor_code', 'name']);



        $defaultItems = $purchaseOrder->items->map(fn ($row) => [

            'item' => $row->item,

            'description' => $row->description,

            'quantity' => $row->quantity,

            'unit_price' => $row->unit_price,

        ])->all();



        if ($defaultItems === []) {

            $defaultItems = [['item' => '', 'description' => '', 'quantity' => 1, 'unit_price' => 0]];

        }



        return view('procurement.purchase-orders.edit', [

            'purchaseOrder' => $purchaseOrder,

            'vendors' => $vendors,

            'defaultItems' => $defaultItems,

            'poTerms' => $this->termsForForm($purchaseOrder),

            'scopeTermsMap' => $this->termsService->termsMapForRfqForm(),

        ]);

    }



    public function update(UpdatePurchaseOrderRequest $request, PurchaseOrder $purchaseOrder): RedirectResponse

    {

        $validated = $request->validated();

        $items = PurchaseOrderPersistenceService::normalizeItems($validated['items'] ?? []);

        unset($validated['items']);



        if ($items === []) {

            return back()->withInput()->withErrors(['items' => 'Add at least one line item with a description.']);

        }



        PurchaseOrderPayloadResolver::finalizeForUpdate($validated);

        PurchaseOrderPayloadResolver::normalizeCurrency($validated, $request->user());



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



    /**

     * @return array{general: list<string>, custom: list<string>}

     */

    private function termsForForm(?PurchaseOrder $purchaseOrder = null): array

    {

        $customFromOld = old('terms_custom');

        if (is_array($customFromOld)) {

            return [

                'general' => [],

                'custom' => $this->termsService->normalizeTexts($customFromOld),

            ];

        }



        if ($purchaseOrder !== null) {

            $parsed = $this->termsService->parseStoredTerms($purchaseOrder->terms);



            return [

                'general' => [],

                'custom' => $parsed['custom'],

            ];

        }



        return ['general' => [], 'custom' => []];

    }



    /**

     * @return list<string>

     */

    private function resolvedTermsForPurchaseOrder(PurchaseOrder $purchaseOrder): array

    {

        $parsed = $this->termsService->parseStoredTerms($purchaseOrder->terms);



        if ($parsed['all'] !== []) {

            return $parsed['all'];

        }



        return RfqTerms::legacyDefaults($purchaseOrder->terms_locale);

    }

}

