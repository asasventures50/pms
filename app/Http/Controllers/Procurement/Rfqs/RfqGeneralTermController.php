<?php

namespace App\Http\Controllers\Procurement\Rfqs;

use App\Enums\Procurement\Rfqs\RfqTermsLocale;
use App\Http\Controllers\Controller;
use App\Http\Requests\Procurement\Rfqs\StoreRfqGeneralTermRequest;
use App\Http\Requests\Procurement\Rfqs\UpdateRfqGeneralTermRequest;
use App\Models\Procurement\Rfqs\RfqGeneralTerm;
use App\Services\Procurement\Rfqs\RfqGeneralTermsService;
use App\Support\Procurement\ProcurementScopeType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class RfqGeneralTermController extends Controller
{
    public function __construct(
        private readonly RfqGeneralTermsService $termsService,
    ) {}

    public function index(Request $request): View
    {
        $perPage = max(1, min(100, (int) $request->query('per_page', 25)));

        return view('procurement.rfq-terms.index', [
            'terms' => $this->filteredTermsQuery($request)->paginate($perPage)->withQueryString(),
            'scopeTypes' => ProcurementScopeType::options(),
        ]);
    }

    public function print(Request $request): View
    {
        $filters = $this->validatedPrintFilters($request);
        $terms = $this->filteredTermsQuery($request, $filters)->get();

        return view('procurement.rfq-terms.print', [
            'sections' => $this->termsService->sectionsForPrint($terms, $filters['scope_type']),
            'scopeTypes' => ProcurementScopeType::options(),
            'filters' => $filters,
            'filterSummary' => $this->printFilterSummary($filters),
        ]);
    }

    public function create(): View
    {
        return view('procurement.rfq-terms.create', [
            'term' => new RfqGeneralTerm([
                'scope_types' => null,
                'is_active' => true,
                'sort_order' => $this->termsService->nextSortOrder(null),
            ]),
            'scopeTypes' => ProcurementScopeType::options(),
        ]);
    }

    public function store(StoreRfqGeneralTermRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['sort_order'] ??= $this->termsService->nextSortOrder($data['scope_types'] ?? null);

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

    /**
     * @return Builder<RfqGeneralTerm>
     */
    private function filteredTermsQuery(Request $request, ?array $filters = null): Builder
    {
        $filters ??= [
            'q' => $request->string('q')->toString(),
            'scope_type' => $request->string('scope_type')->toString(),
            'is_active' => $request->query('is_active'),
            'locale' => $request->string('locale')->toString(),
        ];

        $query = RfqGeneralTerm::query()
            ->orderBy('sort_order')
            ->orderBy('id');

        $search = trim((string) ($filters['q'] ?? ''));
        if ($search !== '') {
            $term = '%'.$search.'%';
            $query->where(function ($q) use ($term) {
                $q->where('body_ar', 'like', $term)
                    ->orWhere('body_en', 'like', $term);
            });
        }

        $isActive = $filters['is_active'] ?? null;
        if ($isActive !== null && $isActive !== '') {
            $query->where('is_active', filter_var($isActive, FILTER_VALIDATE_BOOLEAN));
        }

        $scopeType = trim((string) ($filters['scope_type'] ?? ''));
        if ($scopeType !== '') {
            $this->termsService->applyScopeTypeFilter($query, $scopeType);
        }

        return $query;
    }

    /**
     * @return array{q: string, scope_type: string, is_active: string, locale: string}
     */
    private function validatedPrintFilters(Request $request): array
    {
        $validated = $request->validate([
            'q' => ['nullable', 'string', 'max:255'],
            'scope_type' => ['nullable', 'string', Rule::in(array_merge(
                ['', RfqGeneralTermsService::GLOBAL_SCOPE_KEY],
                ProcurementScopeType::values(),
            ))],
            'is_active' => ['nullable', Rule::in(['', '0', '1'])],
            'locale' => ['nullable', Rule::in(['', 'both', ...RfqTermsLocale::values()])],
        ]);

        $isActive = $validated['is_active'] ?? null;
        if ($isActive === null || $isActive === '') {
            $isActive = '1';
        }

        $locale = $validated['locale'] ?? null;
        if ($locale === null || $locale === '') {
            $locale = 'both';
        }

        return [
            'q' => trim((string) ($validated['q'] ?? '')),
            'scope_type' => (string) ($validated['scope_type'] ?? ''),
            'is_active' => (string) $isActive,
            'locale' => (string) $locale,
        ];
    }

    /**
     * @param  array{q: string, scope_type: string, is_active: string, locale: string}  $filters
     */
    private function printFilterSummary(array $filters): string
    {
        $parts = [
            $this->termsService->printScopeFilterLabel($filters['scope_type'] ?: null),
            $filters['is_active'] === '1' ? 'Active only' : ($filters['is_active'] === '0' ? 'Inactive only' : 'All statuses'),
            match ($filters['locale']) {
                RfqTermsLocale::Ar->value => 'Arabic only',
                RfqTermsLocale::En->value => 'English only',
                default => 'Arabic & English',
            },
        ];

        if ($filters['q'] !== '') {
            $parts[] = 'Search: "'.$filters['q'].'"';
        }

        return implode(' · ', $parts);
    }
}
