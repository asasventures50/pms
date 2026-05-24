<?php

namespace App\Http\Controllers\Procurement\Rfqs;

use App\Http\Controllers\Controller;
use App\Http\Requests\Procurement\Rfqs\StoreRfqGeneralTermRequest;
use App\Http\Requests\Procurement\Rfqs\UpdateRfqGeneralTermRequest;
use App\Models\Procurement\Rfqs\RfqGeneralTerm;
use App\Services\Procurement\Rfqs\RfqGeneralTermsService;
use App\Support\Procurement\ProcurementScopeType;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RfqGeneralTermController extends Controller
{
    public function __construct(
        private readonly RfqGeneralTermsService $termsService,
    ) {}

    public function index(Request $request): View
    {
        $perPage = max(1, min(100, (int) $request->query('per_page', 25)));

        $query = RfqGeneralTerm::query()
            ->orderByRaw('scope_type is null desc')
            ->orderBy('scope_type')
            ->orderBy('sort_order')
            ->orderBy('id');

        if ($request->filled('q')) {
            $term = '%'.$request->string('q').'%';
            $query->where(function ($q) use ($term) {
                $q->where('body_ar', 'like', $term)
                    ->orWhere('body_en', 'like', $term);
            });
        }

        if ($request->filled('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        if ($request->filled('scope_type')) {
            if ($request->string('scope_type')->toString() === RfqGeneralTermsService::GLOBAL_SCOPE_KEY) {
                $query->whereNull('scope_type');
            } else {
                $query->where('scope_type', $request->string('scope_type'));
            }
        }

        return view('procurement.rfq-terms.index', [
            'terms' => $query->paginate($perPage)->withQueryString(),
            'scopeTypes' => ProcurementScopeType::options(),
        ]);
    }

    public function create(): View
    {
        return view('procurement.rfq-terms.create', [
            'term' => new RfqGeneralTerm([
                'scope_type' => null,
                'is_active' => true,
                'sort_order' => $this->termsService->nextSortOrder(null),
            ]),
            'scopeTypes' => ProcurementScopeType::options(),
        ]);
    }

    public function store(StoreRfqGeneralTermRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['sort_order'] ??= $this->termsService->nextSortOrder($data['scope_type'] ?? null);

        RfqGeneralTerm::query()->create($data);

        return redirect()
            ->route('rfq-terms.index')
            ->with('success', 'General term created successfully.');
    }

    public function edit(RfqGeneralTerm $rfq_term): View
    {
        return view('procurement.rfq-terms.edit', [
            'term' => $rfq_term,
            'scopeTypes' => ProcurementScopeType::options(),
        ]);
    }

    public function update(UpdateRfqGeneralTermRequest $request, RfqGeneralTerm $rfq_term): RedirectResponse
    {
        $rfq_term->update($request->validated());

        return redirect()
            ->route('rfq-terms.index')
            ->with('success', 'General term updated successfully.');
    }

    public function destroy(RfqGeneralTerm $rfq_term): RedirectResponse
    {
        $rfq_term->delete();

        return redirect()
            ->route('rfq-terms.index')
            ->with('success', 'General term deleted successfully.');
    }
}
