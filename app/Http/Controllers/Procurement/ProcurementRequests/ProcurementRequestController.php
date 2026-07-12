<?php

namespace App\Http\Controllers\Procurement\ProcurementRequests;

use App\Enums\Procurement\PrCompany;
use App\Enums\Procurement\ProcurementRequests\ProcurementApprovalRole;
use App\Enums\Procurement\ProcurementRequests\ProcurementRequestStatus;
use App\Enums\Procurement\ProcurementRequests\ProcurementTimelineActivity;
use App\Http\Controllers\Controller;
use App\Http\Requests\Procurement\ProcurementRequests\StoreProcurementRequestRequest;
use App\Http\Requests\Procurement\ProcurementRequests\UpdateProcurementRequestRequest;
use App\Models\Procurement\ProcurementRequests\ProcurementRequest;
use App\Models\Procurement\Projects\Project;
use App\Models\Procurement\Vendors\Category;
use App\Models\User;
use App\Services\Procurement\ProcurementRequests\ProcurementRequestCodeGenerator;
use App\Services\Procurement\ProcurementRequests\ProcurementRequestFormDataResolver;
use App\Services\Procurement\ProcurementRequests\ProcurementRequestPayloadResolver;
use App\Services\Procurement\ProcurementRequests\ProcurementRequestPersistenceService;
use App\Services\Procurement\ProcurementRequests\ProcurementRequestPrintLabels;
use App\Services\Procurement\ProcurementRequests\ProcurementRequestRequestorResolver;
use App\Services\Procurement\ProcurementRequests\ProcurementRequestSupportingDocumentStorage;
use App\Services\Procurement\Rfqs\RelatedRfqsForProcurementRequestQuery;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ProcurementRequestController extends Controller
{
    public function __construct(
        private readonly ProcurementRequestPersistenceService $persistence,
        private readonly ProcurementRequestSupportingDocumentStorage $documents,
        private readonly ProcurementRequestFormDataResolver $formData,
    ) {}

    public function index(Request $request): View
    {
        $perPage = max(1, min(100, (int) $request->query('per_page', 15)));

        $query = ProcurementRequest::query()
            ->with(['creator', 'items:id,procurement_request_id,required_delivery_date', 'project:id,code,name'])
            ->latest();

        $user = $request->user();

        if ($user?->scopesProcurementRequestsToOwn()) {
            $query->where('created_by', $user->id);
        }

        if ($request->filled('q')) {
            $term = '%'.$request->string('q').'%';
            $query->where(function ($q) use ($term) {
                $q->where('request_number', 'like', $term)
                    ->orWhere('requestor_name', 'like', $term)
                    ->orWhere('requestor_department', 'like', $term)
                    ->orWhereHas('creator', fn ($c) => $c->where('name', 'like', $term));
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }

        return view('procurement.procurement-requests.index', [
            'procurementRequests' => $query->paginate($perPage)->withQueryString(),
            'statuses' => ProcurementRequestStatus::cases(),
        ]);
    }

    public function create(): View
    {
        return view('procurement.procurement-requests.create', [
            'nextCode' => app(ProcurementRequestCodeGenerator::class)->next(),
            'formDefaults' => $this->defaultFormData(),
            'projects' => $this->activeProjects(),
            'categories' => $this->activeCategories(),
        ]);
    }

    public function store(StoreProcurementRequestRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $items = ProcurementRequestPersistenceService::normalizeItems($validated['items'] ?? []);

        if ($items === []) {
            return back()->withInput()->withErrors(['items' => 'Add at least one BOQ line with a description.']);
        }

        $header = ProcurementRequestPersistenceService::normalizeHeader($validated);
        ProcurementRequestRequestorResolver::applyForCreate($header, $request->user());
        ProcurementRequestPayloadResolver::finalizeForStore($header);
        $header['created_by'] = $request->user()->id;
        $header['status'] ??= ProcurementRequestStatus::Draft->value;

        $procurementRequest = $this->persistence->create(
            $header,
            $items,
            ProcurementRequestPersistenceService::normalizePaymentTerms($validated['payment_terms'] ?? []),
            ProcurementRequestPersistenceService::normalizeRetentions($validated['retentions'] ?? []),
            $validated['timeline'] ?? [],
            $validated['approvals'] ?? [],
        );

        $this->syncHeaderSupportingDocuments($request, $procurementRequest->fresh());

        return redirect()
            ->route('procurement-requests.show', $procurementRequest)
            ->with('success', 'Procurement request created successfully.');
    }

    public function show(Request $request, ProcurementRequest $procurementRequest): View
    {
        $this->authorizeProcurementRequestView($request->user(), $procurementRequest);

        $procurementRequest->load([
            'creator',
            'project',
            'zone',
            'category',
            'subcategory',
            'items.project',
            'items.zone',
            'items.documents',
            'headerDocuments',
            'paymentTerms',
            'retentions',
            'timelineEntries',
            'approvals',
        ]);

        return view('procurement.procurement-requests.show', [
            'procurementRequest' => $procurementRequest,
            'formData' => $this->formData->resolve($procurementRequest),
            'relatedRfqs' => app(RelatedRfqsForProcurementRequestQuery::class)->forProcurementRequest($procurementRequest),
        ]);
    }

    public function print(Request $request, ProcurementRequest $procurementRequest): View
    {
        $this->authorizeProcurementRequestView($request->user(), $procurementRequest);

        $printLabels = ProcurementRequestPrintLabels::resolve($request->query('locale'));

        $procurementRequest->load([
            'creator',
            'project',
            'zone',
            'category',
            'subcategory',
            'items.project',
            'items.zone',
            'items.documents',
            'headerDocuments',
            'paymentTerms',
            'retentions',
            'approvals',
        ]);

        return view('procurement.procurement-requests.print', [
            'procurementRequest' => $procurementRequest,
            'buyerCompany' => PrCompany::forDisplay($procurementRequest->company_key),
            'prCompany' => PrCompany::resolve($procurementRequest->company_key),
            'formData' => $this->formData->resolve($procurementRequest),
            'printLabels' => $printLabels,
        ]);
    }

    public function edit(ProcurementRequest $procurementRequest): View
    {
        $procurementRequest->load([
            'items.documents',
            'creator',
            'headerDocuments',
            'paymentTerms',
            'retentions',
            'timelineEntries',
            'approvals',
            'project',
            'zone',
            'category',
            'subcategory',
        ]);

        return view('procurement.procurement-requests.edit', [
            'procurementRequest' => $procurementRequest,
            'formDefaults' => $this->formData->resolve($procurementRequest),
            'projects' => $this->activeProjects(),
            'categories' => $this->activeCategories(),
        ]);
    }

    public function update(UpdateProcurementRequestRequest $request, ProcurementRequest $procurementRequest): RedirectResponse
    {
        $validated = $request->validated();
        $items = ProcurementRequestPersistenceService::normalizeItems($validated['items'] ?? []);

        if ($items === []) {
            return back()->withInput()->withErrors(['items' => 'Add at least one BOQ line with a description.']);
        }

        $header = ProcurementRequestPersistenceService::normalizeHeader($validated);
        ProcurementRequestPayloadResolver::finalizeForUpdate($header);

        $this->documents->removeByIds(
            $procurementRequest,
            array_map('intval', $validated['remove_supporting_document_ids'] ?? [])
        );

        $this->persistence->update(
            $procurementRequest,
            $header,
            $items,
            ProcurementRequestPersistenceService::normalizePaymentTerms($validated['payment_terms'] ?? []),
            ProcurementRequestPersistenceService::normalizeRetentions($validated['retentions'] ?? []),
            $validated['timeline'] ?? [],
            $validated['approvals'] ?? [],
        );

        $this->syncHeaderSupportingDocuments($request, $procurementRequest->fresh());

        return redirect()
            ->route('procurement-requests.show', $procurementRequest)
            ->with('success', 'Procurement request updated successfully.');
    }

    public function destroy(ProcurementRequest $procurementRequest): RedirectResponse
    {
        DB::transaction(function () use ($procurementRequest) {
            $this->documents->purgeStoredFilesForRequest($procurementRequest);
            $procurementRequest->forceDelete();
        });

        return redirect()
            ->route('procurement-requests.index')
            ->with('success', 'Procurement request deleted permanently.');
    }

    private function authorizeProcurementRequestView(?User $user, ProcurementRequest $procurementRequest): void
    {
        if ($user === null || ! $user->canViewProcurementRequest($procurementRequest)) {
            abort(403, 'You do not have permission to view this procurement request.');
        }
    }

    private function syncHeaderSupportingDocuments(Request $request, ProcurementRequest $procurementRequest): void
    {
        $rows = $request->input('supporting_document_rows', []);

        if (! is_array($rows)) {
            return;
        }

        $links = [];

        foreach (array_values($rows) as $index => $row) {
            if (! is_array($row)) {
                continue;
            }

            $file = $request->file("supporting_document_rows.$index.file");

            if ($file instanceof UploadedFile && $file->isValid()) {
                $this->documents->appendToRequest(
                    $procurementRequest,
                    [$file],
                    isset($row['document_type']) ? trim((string) $row['document_type']) : null,
                    isset($row['file_description']) ? trim((string) $row['file_description']) : null,
                );
            }

            $url = trim((string) ($row['url'] ?? ''));

            if ($url !== '') {
                $links[] = [
                    'url' => $url,
                    'name' => isset($row['name']) ? trim((string) $row['name']) : null,
                    'document_type' => isset($row['document_type']) ? trim((string) $row['document_type']) : null,
                    'file_description' => isset($row['file_description']) ? trim((string) $row['file_description']) : null,
                ];
            }
        }

        if ($links !== []) {
            $this->documents->appendLinksToRequest($procurementRequest, $links);
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function defaultFormData(): array
    {
        return [
            'company_key' => PrCompany::AsasVentures->value,
            'project_id' => '',
            'category_id' => '',
            'subcategory_id' => '',
            'procurement_types' => [],
            'geographic_scopes' => [],
            'vendor_types' => [],
            'justification' => '',
            'delivery_lead_time_days' => '',
            'delivery_location' => '',
            'flexible_delivery_date' => true,
            'currency_code' => '',
            'samples_required' => null,
            'scope_of_work' => '',
            'nda_required' => null,
            'after_sale_service_applicable' => null,
            'compliance_verification_required' => null,
            'compliance_prequalification_required' => null,
            'compliance_prequalification_level' => '',
            'conflict_of_interest_required' => null,
            'commitment_compliance_required' => null,
            'warranty_years' => '',
            'warranty_coverage' => '',
            'items' => $this->emptyBoqLines(1),
            'payment_terms' => [['milestone' => '', 'amount' => '', 'percentage' => '', 'due_upon' => '']],
            'retentions' => [['retention_percent' => '', 'release_period' => '']],
            'timeline' => array_map(
                static fn (ProcurementTimelineActivity $activity) => [
                    'activity' => $activity->value,
                    'label' => $activity->label(),
                    'duration_days' => '',
                ],
                ProcurementTimelineActivity::cases()
            ),
            'approvals' => array_map(
                static fn (ProcurementApprovalRole $role) => [
                    'role' => $role->value,
                    'label' => $role->label(),
                    'name' => '',
                    'signature' => '',
                    'signed_at' => '',
                ],
                ProcurementApprovalRole::cases()
            ),
            'header_documents' => collect(),
            'legacy_item_documents' => collect(),
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function emptyBoqLines(int $count): array
    {
        return array_fill(0, $count, [
            'item_name' => '',
            'zone_id' => '',
            'description' => '',
            'unit' => '',
            'quantity' => 1,
            'unit_price' => 0,
            'total_price' => 0,
        ]);
    }

    /**
     * @return Collection<int, Project>
     */
    private function activeProjects()
    {
        return Project::query()
            ->active()
            ->with(['zones' => fn ($query) => $query->active()->orderBy('name')])
            ->orderBy('name')
            ->get();
    }

    /**
     * @return Collection<int, Category>
     */
    private function activeCategories()
    {
        return Category::query()
            ->with(['subcategories' => fn ($q) => $q->orderBy('name_en')])
            ->orderBy('name_en')
            ->get();
    }
}
