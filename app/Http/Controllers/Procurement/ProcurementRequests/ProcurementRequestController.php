<?php

namespace App\Http\Controllers\Procurement\ProcurementRequests;

use App\Enums\Procurement\ProcurementRequests\ProcurementRequestStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Procurement\ProcurementRequests\StoreProcurementRequestRequest;
use App\Http\Requests\Procurement\ProcurementRequests\UpdateProcurementRequestRequest;
use App\Models\Procurement\ProcurementRequests\ProcurementRequest;
use App\Models\Procurement\Projects\Project;
use App\Services\Procurement\ProcurementRequests\ProcurementRequestCodeGenerator;
use App\Services\Procurement\ProcurementRequests\ProcurementRequestPayloadResolver;
use App\Services\Procurement\ProcurementRequests\ProcurementRequestPersistenceService;
use App\Services\Procurement\ProcurementRequests\ProcurementRequestRequestorResolver;
use App\Services\Procurement\ProcurementRequests\ProcurementRequestSupportingDocumentStorage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
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
            ->with(['creator', 'items:id,procurement_request_id,required_delivery_date'])
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
            'defaultItems' => $this->emptyLineItems(1),
            'projects' => $this->activeProjects(),
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

        $procurementRequest = $this->persistence->create($validated, $items);
        $this->syncItemSupportingDocuments($request, $procurementRequest->fresh(['items']));

        return redirect()
            ->route('procurement-requests.show', $procurementRequest)
            ->with('success', 'Procurement request created successfully.');
    }

    public function show(ProcurementRequest $procurementRequest): View
    {
        $procurementRequest->load(['creator', 'items.project', 'items.zone', 'items.documents']);

        return view('procurement.procurement-requests.show', [
            'procurementRequest' => $procurementRequest,
        ]);
    }

    public function edit(ProcurementRequest $procurementRequest): View
    {
        $procurementRequest->load(['items.project', 'items.zone', 'items.documents', 'creator']);

        $defaultItems = $procurementRequest->items->map(fn ($row) => [
            'id' => $row->id,
            'line_number' => $row->line_number,
            'project_id' => $row->project_id,
            'zone_id' => $row->zone_id,
            'category' => $row->category,
            'subcategory' => $row->subcategory,
            'scope_type' => $row->scope_type,
            'description' => $row->description,
            'unit' => $row->unit,
            'quantity' => $row->quantity,
            'justification' => $row->justification,
            'scope_of_work' => $row->scope_of_work,
            'required_delivery_date' => $row->required_delivery_date?->format('Y-m-d'),
            'flexible_delivery_date' => $row->flexible_delivery_date,
            'delivery_location' => $row->delivery_location,
            'documents' => $row->documents,
        ])->all();

        if ($defaultItems === []) {
            $defaultItems = $this->emptyLineItems(1);
        }

        return view('procurement.procurement-requests.edit', [
            'procurementRequest' => $procurementRequest,
            'defaultItems' => $defaultItems,
            'projects' => $this->activeProjects(),
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

        $this->documents->removeByIds(
            $procurementRequest,
            $this->collectRemoveSupportingDocumentIds($request)
        );

        $this->persistence->update($procurementRequest, $validated, $items);
        $this->syncItemSupportingDocuments($request, $procurementRequest->fresh(['items']));

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

    private function syncItemSupportingDocuments(Request $request, ProcurementRequest $procurementRequest): void
    {
        foreach ($procurementRequest->items as $index => $item) {
            $files = $request->file("items.$index.supporting_documents");

            if (! is_array($files)) {
                continue;
            }

            $uploads = array_values(array_filter(
                $files,
                static fn ($file) => $file instanceof UploadedFile && $file->isValid()
            ));

            if ($uploads !== []) {
                $this->documents->append($item, $uploads);
            }

            $links = $request->input("items.$index.supporting_document_links", []);

            if (is_array($links) && $links !== []) {
                $this->documents->appendLinks($item, $links);
            }
        }
    }

    /**
     * @return list<int>
     */
    private function collectRemoveSupportingDocumentIds(Request $request): array
    {
        $ids = [];
        $items = $request->input('items', []);

        if (! is_array($items)) {
            return [];
        }

        foreach ($items as $row) {
            if (! is_array($row)) {
                continue;
            }

            $rowIds = $row['remove_supporting_document_ids'] ?? [];

            if (is_array($rowIds)) {
                foreach ($rowIds as $id) {
                    $ids[] = (int) $id;
                }
            }
        }

        return $ids;
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function emptyLineItems(int $count): array
    {
        return array_fill(0, $count, [
            'project_id' => '',
            'zone_id' => '',
            'category' => '',
            'subcategory' => '',
            'scope_type' => [],
            'description' => '',
            'unit' => '',
            'quantity' => 1,
            'justification' => '',
            'scope_of_work' => '',
            'required_delivery_date' => '',
            'flexible_delivery_date' => true,
            'delivery_location' => '',
        ]);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Collection<int, Project>
     */
    private function activeProjects()
    {
        return Project::query()
            ->active()
            ->with(['zones' => fn ($query) => $query->active()->orderBy('name')])
            ->orderBy('name')
            ->get();
    }
}
