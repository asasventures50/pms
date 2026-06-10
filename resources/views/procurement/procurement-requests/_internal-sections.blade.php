@php
    use App\Enums\Procurement\ProcurementRequests\CompliancePrequalificationLevel;
    use App\Support\Procurement\RfqTerms;

    $timeline = old('timeline', $formDefaults['timeline'] ?? []);
    $generalTerms = RfqTerms::defaults(app()->getLocale());
    $prequalRequired = old('compliance_prequalification_required', $formDefaults['compliance_prequalification_required'] ?? null);
    $prequalLevel = old('compliance_prequalification_level', $formDefaults['compliance_prequalification_level'] ?? '');
    $showPrequalLevel = $prequalRequired === true || $prequalRequired === '1';
@endphp

<section class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
    <h3 class="text-sm font-semibold text-slate-900">Procurement timeline <span class="font-normal text-slate-500">(internal)</span></h3>
    <div class="mt-4 overflow-x-auto">
        <table class="min-w-full text-left text-sm">
            <thead class="text-xs font-semibold uppercase tracking-wide text-slate-500">
            <tr>
                <th class="px-2 py-2">Activity</th>
                <th class="px-2 py-2">Duration (days)</th>
            </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
            @foreach ($timeline as $index => $row)
                <tr>
                    <td class="px-2 py-2 text-slate-800">
                        {{ $row['label'] ?? $row['activity'] ?? '' }}
                        <input type="hidden" name="timeline[{{ $index }}][activity]" value="{{ $row['activity'] ?? '' }}">
                    </td>
                    <td class="px-2 py-2">
                        <input type="number" name="timeline[{{ $index }}][duration_days]"
                               value="{{ old("timeline.$index.duration_days", $row['duration_days'] ?? '') }}"
                               min="0" max="9999" class="admin-filter-control w-32">
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</section>

<section class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
    <h3 class="text-sm font-semibold text-slate-900">Compliance requirements <span class="font-normal text-slate-500">(internal)</span></h3>

    <div class="mt-4 grid gap-6 sm:grid-cols-2">
        @include('procurement.procurement-requests.partials._yes-no-radio', [
            'name' => 'compliance_verification_required',
            'label' => 'Required verification',
            'value' => old('compliance_verification_required', $formDefaults['compliance_verification_required'] ?? null),
        ])
        <div>
            @include('procurement.procurement-requests.partials._yes-no-radio', [
                'name' => 'compliance_prequalification_required',
                'label' => 'Required prequalification',
                'value' => $prequalRequired,
            ])
            <div id="pr-prequal-level-wrap" class="mt-3 @unless($showPrequalLevel) hidden @endunless">
                <label for="compliance_prequalification_level" class="block text-xs font-medium uppercase tracking-wide text-slate-500">
                    Level <span class="text-red-600">*</span>
                </label>
                <select name="compliance_prequalification_level" id="compliance_prequalification_level"
                        class="admin-filter-control mt-1 w-full max-w-xs @error('compliance_prequalification_level') border-red-500 @enderror">
                    <option value="">—</option>
                    @foreach (CompliancePrequalificationLevel::cases() as $level)
                        <option value="{{ $level->value }}" @selected((string) $prequalLevel === $level->value)>
                            {{ $level->label() }}
                        </option>
                    @endforeach
                </select>
                @error('compliance_prequalification_level')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
        </div>
        @include('procurement.procurement-requests.partials._yes-no-radio', [
            'name' => 'nda_required',
            'label' => 'NDA required',
            'value' => old('nda_required', $formDefaults['nda_required'] ?? null),
        ])
        @include('procurement.procurement-requests.partials._yes-no-radio', [
            'name' => 'conflict_of_interest_required',
            'label' => 'Conflict of interest',
            'value' => old('conflict_of_interest_required', $formDefaults['conflict_of_interest_required'] ?? null),
        ])
        @include('procurement.procurement-requests.partials._yes-no-radio', [
            'name' => 'commitment_compliance_required',
            'label' => 'Declaration of commitment and compliance',
            'value' => old('commitment_compliance_required', $formDefaults['commitment_compliance_required'] ?? null),
        ])
    </div>

    <div class="mt-6 border-t border-slate-100 pt-6">
        <div class="flex items-center justify-between gap-3">
            <h4 class="text-xs font-semibold uppercase tracking-wide text-slate-600">General terms</h4>
            @if (auth()->user()->hasPermission('rfq-terms.view'))
                <a href="{{ route('rfq-terms.index') }}" target="_blank" rel="noopener"
                   class="text-xs font-medium text-slate-600 hover:text-slate-900">Manage terms</a>
            @endif
        </div>
        @if ($generalTerms === [])
            <p class="mt-2 text-sm text-slate-500">No general terms configured yet.</p>
        @else
            <ul class="mt-2 list-none space-y-1.5 text-sm text-slate-800">
                @foreach ($generalTerms as $term)
                    <li class="flex gap-2">
                        <span class="shrink-0">-</span>
                        <span class="min-w-0 flex-1">{{ $term }}</span>
                    </li>
                @endforeach
            </ul>
        @endif
    </div>
</section>
