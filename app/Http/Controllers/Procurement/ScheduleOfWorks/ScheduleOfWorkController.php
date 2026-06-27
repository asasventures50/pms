<?php

namespace App\Http\Controllers\Procurement\ScheduleOfWorks;

use App\Enums\Procurement\Rfqs\RfqTermsLocale;
use App\Enums\Procurement\ScheduleOfWorks\ScheduleOfWorkScope;
use App\Http\Controllers\Controller;
use App\Http\Requests\Procurement\ScheduleOfWorks\StoreScheduleOfWorkRequest;
use App\Http\Requests\Procurement\ScheduleOfWorks\UpdateScheduleOfWorkRequest;
use App\Models\Procurement\ProcurementRequests\ProcurementRequest;
use App\Models\Procurement\ScheduleOfWorks\ScheduleOfWork;
use App\Models\Procurement\Vendors\Vendor;
use App\Models\User;
use App\Services\Procurement\PurchaseOrders\ProcurementRequestOptionsForPurchaseOrderQuery;
use App\Services\Procurement\ScheduleOfWorks\ScheduleOfWorkPersistenceService;
use App\Services\Procurement\ScheduleOfWorks\ScheduleOfWorkPrintLabels;
use App\Services\Procurement\ScheduleOfWorks\ScheduleOfWorkPrItemsPresenter;
use App\Services\Procurement\ScheduleOfWorks\ScheduleOfWorkTermsResolver;
use App\Services\Procurement\Vendors\VendorSelectOptions;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ScheduleOfWorkController extends Controller
{
    public function __construct(
        private readonly ScheduleOfWorkPersistenceService $persistence,
        private readonly ScheduleOfWorkTermsResolver $termsResolver,
        private readonly ScheduleOfWorkPrItemsPresenter $prItemsPresenter,
        private readonly ProcurementRequestOptionsForPurchaseOrderQuery $procurementRequestOptions,
    ) {}

    public function index(Request $request): View
    {
        $perPage = max(1, min(100, (int) $request->query('per_page', 15)));

        $query = ScheduleOfWork::query()
            ->with('creator')
            ->latest();

        if ($request->filled('q')) {
            $term = '%'.$request->string('q').'%';
            $query->where(function ($q) use ($term) {
                $q->where('document_number', 'like', $term)
                    ->orWhere('recipient_name', 'like', $term)
                    ->orWhere('vendor_company_name', 'like', $term);
            });
        }

        return view('procurement.schedule-of-works.index', [
            'schedules' => $query->paginate($perPage)->withQueryString(),
        ]);
    }

    public function create(): View
    {
        $linkedPrId = filled(old('procurement_request_id')) ? (int) old('procurement_request_id') : null;

        return view('procurement.schedule-of-works.create', [
            'scopeOptions' => ScheduleOfWorkScope::cases(),
            'printLocales' => RfqTermsLocale::cases(),
            'vendorSelectOptions' => VendorSelectOptions::all(),
            'procurementRequestOptions' => $this->procurementRequestOptions->options($linkedPrId),
            'selectedVendor' => old('vendor_id')
                ? Vendor::query()->find((int) old('vendor_id'), ['id', 'vendor_code', 'name'])
                : null,
        ]);
    }

    public function store(StoreScheduleOfWorkRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $lines = ScheduleOfWorkPersistenceService::normalizeLines($validated['items'] ?? []);

        if ($lines === []) {
            return back()->withInput()->withErrors(['items' => 'Add at least one line item with description, quantity, and price.']);
        }

        $header = ScheduleOfWorkPersistenceService::headerFromValidated($validated, (int) $request->user()->id);
        $schedule = $this->persistence->create($header, $lines);

        return redirect()
            ->route('schedule-of-works.print', ['schedule_of_work' => $schedule, 'locale' => $schedule->print_locale])
            ->with('success', 'Schedule of works created successfully.');
    }

    public function edit(ScheduleOfWork $scheduleOfWork): View
    {
        $scheduleOfWork->load('items');

        $selectedVendor = $scheduleOfWork->vendor_id
            ? Vendor::query()->find($scheduleOfWork->vendor_id, ['id', 'vendor_code', 'name'])
            : null;

        $linkedPrId = filled(old('procurement_request_id'))
            ? (int) old('procurement_request_id')
            : $scheduleOfWork->procurement_request_id;

        return view('procurement.schedule-of-works.edit', [
            'schedule' => $scheduleOfWork,
            'formDefaults' => $this->formDefaultsFromSchedule($scheduleOfWork),
            'scopeOptions' => ScheduleOfWorkScope::cases(),
            'printLocales' => RfqTermsLocale::cases(),
            'vendorSelectOptions' => VendorSelectOptions::all(),
            'procurementRequestOptions' => $this->procurementRequestOptions->options($linkedPrId),
            'selectedVendor' => $selectedVendor,
        ]);
    }

    public function update(UpdateScheduleOfWorkRequest $request, ScheduleOfWork $scheduleOfWork): RedirectResponse
    {
        $validated = $request->validated();
        $lines = ScheduleOfWorkPersistenceService::normalizeLines($validated['items'] ?? []);

        if ($lines === []) {
            return back()->withInput()->withErrors(['items' => 'Add at least one line item with description, quantity, and price.']);
        }

        $header = ScheduleOfWorkPersistenceService::headerFromValidated($validated, (int) $request->user()->id);
        $header['created_by'] = $scheduleOfWork->created_by;
        $header['documented_at'] = $scheduleOfWork->documented_at?->toDateString() ?? now()->toDateString();

        $schedule = $this->persistence->update($scheduleOfWork, $header, $lines);

        return redirect()
            ->route('schedule-of-works.print', ['schedule_of_work' => $schedule, 'locale' => $schedule->print_locale])
            ->with('success', 'Schedule of works updated successfully.');
    }

    public function destroy(ScheduleOfWork $scheduleOfWork): RedirectResponse
    {
        $documentNumber = $scheduleOfWork->document_number;
        $scheduleOfWork->delete();

        return redirect()
            ->route('schedule-of-works.index')
            ->with('success', "Schedule {$documentNumber} deleted successfully.");
    }

    public function print(Request $request, ScheduleOfWork $scheduleOfWork): View
    {
        $scheduleOfWork->load(['items', 'creator', 'vendor']);

        $locale = $request->query('locale', $scheduleOfWork->print_locale);
        $printLabels = ScheduleOfWorkPrintLabels::resolve($locale);

        return view('procurement.schedule-of-works.print', [
            'schedule' => $scheduleOfWork,
            'printLabels' => $printLabels,
            'terms' => $this->termsResolver->resolve($scheduleOfWork, $printLabels->locale()),
        ]);
    }

    public function procurementRequestItems(Request $request, ProcurementRequest $procurementRequest): JsonResponse
    {
        $this->authorizeProcurementRequestView($request->user(), $procurementRequest);

        return response()->json($this->prItemsPresenter->present($procurementRequest));
    }

    /**
     * @return array<string, mixed>
     */
    private function formDefaultsFromSchedule(ScheduleOfWork $schedule): array
    {
        $notes = $schedule->displayNotes();

        return [
            'recipient_name' => $schedule->recipient_name,
            'project_manager_name' => $schedule->project_manager_name,
            'vendor_id' => $schedule->vendor_id,
            'vendor_company_name' => $schedule->vendor_company_name,
            'procurement_request_id' => $schedule->procurement_request_id,
            'currency_code' => $schedule->currency_code ?? 'USD',
            'print_locale' => $schedule->print_locale ?? RfqTermsLocale::En->value,
            'scope_types' => ScheduleOfWorkScope::selectedValues($schedule->scope_types ?? []),
            'items' => $schedule->items->map(fn ($item) => [
                'project_zone' => $item->project_zone,
                'description' => $item->description,
                'quantity' => $item->quantity,
                'unit' => $item->unit,
                'unit_price' => $item->unit_price,
            ])->values()->all(),
            'notes' => $notes !== [] ? $notes : [''],
        ];
    }

    private function authorizeProcurementRequestView(?User $user, ProcurementRequest $procurementRequest): void
    {
        if ($user === null || ! $user->canViewProcurementRequest($procurementRequest)) {
            abort(403, 'You do not have permission to view this procurement request.');
        }
    }
}
