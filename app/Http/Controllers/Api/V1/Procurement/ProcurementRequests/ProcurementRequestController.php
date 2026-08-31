<?php

namespace App\Http\Controllers\Api\V1\Procurement\ProcurementRequests;

use App\Enums\Procurement\ProcurementRequests\ProcurementRequestStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Procurement\ProcurementRequests\StoreProcurementRequestRequest;
use App\Http\Requests\Procurement\ProcurementRequests\UpdateProcurementRequestRequest;
use App\Http\Resources\Api\V1\Procurement\ProcurementRequests\ProcurementRequestResource;
use App\Http\Resources\Api\V1\Procurement\ProcurementRequests\ProcurementRequestSummaryResource;
use App\Models\Procurement\ProcurementRequests\ProcurementRequest;
use App\Models\User;
use App\Services\Procurement\ProcurementRequests\ProcurementRequestFormDataResolver;
use App\Services\Procurement\ProcurementRequests\ProcurementRequestPayloadResolver;
use App\Services\Procurement\ProcurementRequests\ProcurementRequestPersistenceService;
use App\Services\Procurement\ProcurementRequests\ProcurementRequestRequestorResolver;
use App\Services\Procurement\ProcurementRequests\ProcurementRequestSupportingDocumentStorage;
use App\Services\Procurement\Rfqs\RelatedRfqsForProcurementRequestQuery;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ProcurementRequestController extends Controller
{
    public function __construct(
        private readonly ProcurementRequestPersistenceService $persistence,
        private readonly ProcurementRequestSupportingDocumentStorage $documents,
        private readonly ProcurementRequestFormDataResolver $formData,
    ) {}

    public function index(Request $request): AnonymousResourceCollection
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

        if ($request->filled('request_number')) {
            $query->where('request_number', 'like', '%'.$request->string('request_number').'%');
        }

        if ($request->filled('requestor')) {
            $term = '%'.$request->string('requestor').'%';
            $query->where(function ($q) use ($term) {
                $q->where('requestor_name', 'like', $term)
                    ->orWhereHas('creator', fn ($c) => $c->where('name', 'like', $term));
            });
        }

        if ($request->filled('department')) {
            $query->where('requestor_department', 'like', '%'.$request->string('department').'%');
        }

        if ($request->filled('requested_at')) {
            $query->whereDate('requested_at', $request->string('requested_at'));
        }

        if ($request->filled('delivery_date')) {
            $deliveryDate = $request->string('delivery_date');
            $query->whereHas('items', function ($q) use ($deliveryDate) {
                $q->whereDate('required_delivery_date', $deliveryDate);
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }

        $paginator = $query->paginate($perPage)->withQueryString();

        return ProcurementRequestSummaryResource::collection($paginator)->additional([
            'statuses' => ProcurementRequestStatus::values(),
        ]);
    }

    public function store(StoreProcurementRequestRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $items = ProcurementRequestPersistenceService::normalizeItems($validated['items'] ?? []);

        if ($items === []) {
            throw ValidationException::withMessages([
                'items' => 'Add at least one BOQ line with a description.',
            ]);
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

        return response()->json([
            'message' => 'Procurement request created successfully.',
            'procurement_request' => $this->showPayload($procurementRequest->fresh()),
        ], 201);
    }

    public function show(Request $request, ProcurementRequest $procurement_request): JsonResponse
    {
        $this->authorizeProcurementRequestView($request->user(), $procurement_request);

        return response()->json([
            'procurement_request' => $this->showPayload($procurement_request),
        ]);
    }

    public function update(UpdateProcurementRequestRequest $request, ProcurementRequest $procurement_request): JsonResponse
    {
        $validated = $request->validated();
        $items = ProcurementRequestPersistenceService::normalizeItems($validated['items'] ?? []);

        if ($items === []) {
            throw ValidationException::withMessages([
                'items' => 'Add at least one BOQ line with a description.',
            ]);
        }

        $header = ProcurementRequestPersistenceService::normalizeHeader($validated);
        ProcurementRequestPayloadResolver::finalizeForUpdate($header);

        $this->documents->removeByIds(
            $procurement_request,
            array_map('intval', $validated['remove_supporting_document_ids'] ?? [])
        );

        $this->persistence->update(
            $procurement_request,
            $header,
            $items,
            ProcurementRequestPersistenceService::normalizePaymentTerms($validated['payment_terms'] ?? []),
            ProcurementRequestPersistenceService::normalizeRetentions($validated['retentions'] ?? []),
            $validated['timeline'] ?? [],
            $validated['approvals'] ?? [],
        );

        $this->syncHeaderSupportingDocuments($request, $procurement_request->fresh());

        return response()->json([
            'message' => 'Procurement request updated successfully.',
            'procurement_request' => $this->showPayload($procurement_request->fresh()),
        ]);
    }

    public function destroy(ProcurementRequest $procurement_request): JsonResponse
    {
        DB::transaction(function () use ($procurement_request) {
            $this->documents->purgeStoredFilesForRequest($procurement_request);
            $procurement_request->forceDelete();
        });

        return response()->json([
            'message' => 'Procurement request deleted permanently.',
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function showPayload(ProcurementRequest $procurementRequest): array
    {
        $procurementRequest->load([
            'creator',
            'project',
            'zone',
            'items.project',
            'items.zone',
            'items.catalogCategory',
            'items.catalogSubcategory',
            'items.documents',
            'headerDocuments',
            'paymentTerms',
            'retentions',
            'timelineEntries',
            'approvals',
        ]);

        $relatedRfqs = app(RelatedRfqsForProcurementRequestQuery::class)
            ->forProcurementRequest($procurementRequest);

        return [
            ...(new ProcurementRequestResource($procurementRequest))->resolve(),
            'form' => $this->formData->resolve($procurementRequest),
            'related_rfqs' => $relatedRfqs->map(fn ($rfq) => [
                'id' => $rfq->id,
                'rfq_number' => $rfq->rfq_number,
                'status' => $rfq->status instanceof \BackedEnum ? $rfq->status->value : $rfq->status,
                'vendor_quotations_count' => $rfq->vendor_quotations_count,
                'selected_vendor_quotation' => $rfq->selectedVendorQuotation,
                'selected_by' => $rfq->selectedBy,
            ])->values()->all(),
        ];
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
}
