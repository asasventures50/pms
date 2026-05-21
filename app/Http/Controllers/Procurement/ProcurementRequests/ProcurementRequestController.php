<?php

namespace App\Http\Controllers\Procurement\ProcurementRequests;

use App\Enums\Procurement\ProcurementRequests\ProcurementRequestStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Procurement\ProcurementRequests\StoreProcurementRequestRequest;
use App\Http\Requests\Procurement\ProcurementRequests\UpdateProcurementRequestRequest;
use App\Models\Procurement\ProcurementRequests\ProcurementRequest;
use App\Services\Procurement\ProcurementRequests\ProcurementRequestCodeGenerator;
use App\Services\Procurement\ProcurementRequests\ProcurementRequestPayloadResolver;
use App\Services\Procurement\ProcurementRequests\ProcurementRequestPersistenceService;
use App\Services\Procurement\ProcurementRequests\ProcurementRequestRequestorResolver;
use App\Services\Procurement\ProcurementRequests\ProcurementRequestSupportingDocumentStorage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProcurementRequestController extends Controller
{
    public function __construct(
        private readonly ProcurementRequestPersistenceService $persistence,
        private readonly ProcurementRequestSupportingDocumentStorage $documents,
    ) {}

    public function index(Request $request): View
    {
        $perPage = max(1, min(100, (int) $request->query('per_page', 15)));

        $query = ProcurementRequest::query()
            ->with('creator')
            ->latest();

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
            'defaultItems' => $this->emptyLineItems(2),
        ]);
    }

    public function store(StoreProcurementRequestRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $items = ProcurementRequestPersistenceService::normalizeItems($validated['items'] ?? []);
        unset($validated['items']);

        if ($items === []) {
            return back()->withInput()->withErrors(['items' => 'Add at least one line with an item description.']);
        }

        ProcurementRequestRequestorResolver::applyForCreate($validated, $request->user());
        ProcurementRequestPayloadResolver::finalizeForStore($validated);
        $validated['created_by'] = $request->user()->id;
        $validated['status'] ??= ProcurementRequestStatus::Draft->value;

        $supportingDocuments = array_values($request->file('supporting_documents', []) ?? []);
        unset($validated['supporting_documents'], $validated['remove_supporting_document_ids']);

        $procurementRequest = $this->persistence->create($validated, $items);

        if ($supportingDocuments !== []) {
            $this->documents->append($procurementRequest, $supportingDocuments);
        }

        return redirect()
            ->route('procurement-requests.show', $procurementRequest)
            ->with('success', 'Procurement request created successfully.');
    }

    public function show(ProcurementRequest $procurementRequest): View
    {
        $procurementRequest->load(['creator', 'items', 'documents']);

        return view('procurement.procurement-requests.show', [
            'procurementRequest' => $procurementRequest,
        ]);
    }

    public function edit(ProcurementRequest $procurementRequest): View
    {
        $procurementRequest->load(['items', 'creator', 'documents']);

        $defaultItems = $procurementRequest->items->map(fn ($row) => [
            'line_number' => $row->line_number,
            'project' => $row->project,
            'zone' => $row->zone,
            'category' => $row->category,
            'subcategory' => $row->subcategory,
            'scope_type' => $row->scope_type,
            'description' => $row->description,
            'unit' => $row->unit,
            'quantity' => $row->quantity,
            'justification' => $row->justification,
        ])->all();

        if ($defaultItems === []) {
            $defaultItems = $this->emptyLineItems(2);
        }

        return view('procurement.procurement-requests.edit', [
            'procurementRequest' => $procurementRequest,
            'defaultItems' => $defaultItems,
        ]);
    }

    public function update(UpdateProcurementRequestRequest $request, ProcurementRequest $procurementRequest): RedirectResponse
    {
        $validated = $request->validated();
        $items = ProcurementRequestPersistenceService::normalizeItems($validated['items'] ?? []);
        unset($validated['items']);

        if ($items === []) {
            return back()->withInput()->withErrors(['items' => 'Add at least one line with an item description.']);
        }

        ProcurementRequestPayloadResolver::finalizeForUpdate($validated);

        $supportingDocuments = array_values($request->file('supporting_documents', []) ?? []);
        $removeDocumentIds = $request->input('remove_supporting_document_ids', []);
        unset($validated['supporting_documents'], $validated['remove_supporting_document_ids']);

        $this->persistence->update($procurementRequest, $validated, $items);

        $this->documents->removeByIds($procurementRequest, is_array($removeDocumentIds) ? $removeDocumentIds : []);
        if ($supportingDocuments !== []) {
            $this->documents->append($procurementRequest->fresh(), $supportingDocuments);
        }

        return redirect()
            ->route('procurement-requests.show', $procurementRequest)
            ->with('success', 'Procurement request updated successfully.');
    }

    public function destroy(ProcurementRequest $procurementRequest): RedirectResponse
    {
        $procurementRequest->delete();

        return redirect()
            ->route('procurement-requests.index')
            ->with('success', 'Procurement request deleted successfully.');
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function emptyLineItems(int $count): array
    {
        return array_fill(0, $count, [
            'project' => '',
            'zone' => '',
            'category' => '',
            'subcategory' => '',
            'scope_type' => '',
            'description' => '',
            'unit' => '',
            'quantity' => 1,
            'justification' => '',
        ]);
    }
}
