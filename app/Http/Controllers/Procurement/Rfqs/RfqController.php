<?php

namespace App\Http\Controllers\Procurement\Rfqs;

use App\Enums\Procurement\Rfqs\RfqStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Procurement\Rfqs\StoreRfqRequest;
use App\Http\Requests\Procurement\Rfqs\UpdateRfqRequest;
use App\Models\Procurement\Rfqs\Rfq;
use App\Models\Procurement\Vendors\Vendor;
use App\Services\Procurement\PurchaseOrders\VendorPurchaseOrderSnapshot;
use App\Services\Procurement\Rfqs\AvailableProcurementRequestItemsForRfqQuery;
use App\Services\Procurement\Rfqs\RfqCodeGenerator;
use App\Services\Procurement\Rfqs\RfqGeneralTermsService;
use App\Services\Procurement\Rfqs\RfqPayloadResolver;
use App\Services\Procurement\Rfqs\RfqPersistenceService;
use App\Support\Procurement\RfqTerms;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RfqController extends Controller
{
    public function __construct(
        private readonly RfqPersistenceService $persistence,
        private readonly RfqGeneralTermsService $termsService,
    ) {}

    public function index(Request $request): View
    {
        $perPage = max(1, min(100, (int) $request->query('per_page', 15)));

        $query = Rfq::query()
            ->with(['vendor', 'creator'])
            ->latest();

        if ($request->filled('q')) {
            $term = '%'.$request->string('q').'%';
            $query->where(function ($q) use ($term) {
                $q->where('rfq_number', 'like', $term)
                    ->orWhere('vendor_company_name', 'like', $term)
                    ->orWhereHas('creator', fn ($c) => $c->where('name', 'like', $term));
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }

        return view('procurement.rfqs.index', [
            'rfqs' => $query->paginate($perPage)->withQueryString(),
            'statuses' => RfqStatus::cases(),
        ]);
    }

    public function create(AvailableProcurementRequestItemsForRfqQuery $prItemsQuery): View
    {
        return view('procurement.rfqs.create', [
            'nextCode' => app(RfqCodeGenerator::class)->next(),
            'vendors' => Vendor::query()->orderBy('name')->get(['id', 'vendor_code', 'name']),
            'prItemOptions' => $prItemsQuery->optionsForForm(),
            'defaultItems' => [$this->emptyRfqLineRow()],
            'rfqTerms' => $this->termsForForm(),
            'scopeTermsMap' => $this->termsService->termsMapForRfqForm(),
        ]);
    }

    public function store(StoreRfqRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $items = RfqPersistenceService::normalizeItems($validated['items'] ?? []);
        unset($validated['items']);

        if ($items === []) {
            return back()->withInput()->withErrors(['items' => 'Select at least one procurement request item.']);
        }

        RfqPayloadResolver::finalizeForStore($validated);
        $validated['created_by'] = $request->user()->id;
        $validated['status'] ??= RfqStatus::Draft->value;
        $validated['issue_date'] ??= now()->toDateString();

        $rfq = $this->persistence->create($validated, $items);

        return redirect()
            ->route('rfqs.show', $rfq)
            ->with('success', 'RFQ created successfully.');
    }

    public function show(Rfq $rfq): View
    {
        $rfq->load(['vendor', 'creator', 'items']);

        return view('procurement.rfqs.show', [
            'rfq' => $rfq,
            'terms' => $this->resolvedTermsForRfq($rfq),
        ]);
    }

    public function edit(Rfq $rfq, AvailableProcurementRequestItemsForRfqQuery $prItemsQuery): View
    {
        $rfq->load(['items.procurementRequestItem.procurementRequest', 'creator']);

        $defaultItems = $rfq->items->map(fn ($row) => [
            'procurement_request_item_id' => $row->procurement_request_item_id ?? '',
            'item' => $row->item,
            'description' => $row->description,
            'quantity' => $row->quantity,
            'unit' => $row->unit,
            'request_lead_time' => $row->request_lead_time,
            'compliance' => $row->compliance,
            'unit_price' => $row->unit_price,
            'quote_lead_time' => $row->quote_lead_time,
            'warranty' => $row->warranty,
        ])->all();

        if ($defaultItems === []) {
            $defaultItems = [$this->emptyRfqLineRow()];
        }

        $prItemOptions = $prItemsQuery->optionsForForm($rfq->id);
        $optionIds = collect($prItemOptions)->pluck('id')->all();

        foreach ($rfq->items as $row) {
            if ($row->procurement_request_item_id && ! in_array($row->procurement_request_item_id, $optionIds, true)) {
                $prItem = $row->procurementRequestItem;
                if ($prItem) {
                    $prItemOptions[] = $prItemsQuery->toOption($prItem);
                }
            }
        }

        return view('procurement.rfqs.edit', [
            'rfq' => $rfq,
            'vendors' => Vendor::query()->orderBy('name')->get(['id', 'vendor_code', 'name']),
            'prItemOptions' => $prItemOptions,
            'defaultItems' => $defaultItems,
            'rfqTerms' => $this->termsForForm($rfq),
            'scopeTermsMap' => $this->termsService->termsMapForRfqForm(),
        ]);
    }

    public function update(UpdateRfqRequest $request, Rfq $rfq): RedirectResponse
    {
        $validated = $request->validated();
        $items = RfqPersistenceService::normalizeItems($validated['items'] ?? []);
        unset($validated['items']);

        if ($items === []) {
            return back()->withInput()->withErrors(['items' => 'Select at least one procurement request item.']);
        }

        RfqPayloadResolver::finalizeForUpdate($validated);

        $this->persistence->update($rfq, $validated, $items);

        return redirect()
            ->route('rfqs.show', $rfq)
            ->with('success', 'RFQ updated successfully.');
    }

    public function destroy(Rfq $rfq): RedirectResponse
    {
        $rfq->delete();

        return redirect()
            ->route('rfqs.index')
            ->with('success', 'RFQ deleted successfully.');
    }

    public function vendorSnapshot(Vendor $vendor): JsonResponse
    {
        $data = VendorPurchaseOrderSnapshot::fromVendor($vendor);
        unset($data['payment_terms']);

        return response()->json($data);
    }

    /**
     * @return array<string, mixed>
     */
    private function emptyRfqLineRow(): array
    {
        return [
            'procurement_request_item_id' => '',
            'item' => '',
            'description' => '',
            'quantity' => 1,
            'unit' => '',
            'request_lead_time' => '',
            'compliance' => '',
            'unit_price' => '',
            'quote_lead_time' => '',
            'warranty' => '',
        ];
    }

    /**
     * @return array{general: list<string>, custom: list<string>}
     */
    private function termsForForm(?Rfq $rfq = null): array
    {
        $customFromOld = old('terms_custom');
        if (is_array($customFromOld)) {
            return [
                'general' => [],
                'custom' => $this->termsService->normalizeTexts($customFromOld),
            ];
        }

        if ($rfq !== null) {
            $parsed = $this->termsService->parseStoredTerms($rfq->terms);

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
    private function resolvedTermsForRfq(Rfq $rfq): array
    {
        $parsed = $this->termsService->parseStoredTerms($rfq->terms);

        if ($parsed['all'] !== []) {
            return $parsed['all'];
        }

        return RfqTerms::legacyDefaults($rfq->terms_locale);
    }
}
