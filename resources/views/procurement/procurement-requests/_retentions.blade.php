@php
    $retentions = old('retentions', $formDefaults['retentions'] ?? [['retention_percent' => '', 'release_period' => '']]);
@endphp

<section class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
    <div class="flex items-center justify-between gap-3">
        <h3 class="text-sm font-semibold text-slate-900">Retention by year</h3>
        <button type="button" id="pr-add-retention"
                class="rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-sm font-medium text-slate-800 hover:bg-slate-50 print:hidden">
            Add row
        </button>
    </div>

    <div class="mt-4 overflow-x-auto">
        <table class="min-w-full text-left text-sm">
            <thead class="text-xs font-semibold uppercase tracking-wide text-slate-500">
            <tr>
                <th class="px-2 py-2">Retention %</th>
                <th class="px-2 py-2">Release period</th>
                <th class="px-2 py-2 print:hidden"></th>
            </tr>
            </thead>
            <tbody id="pr-retentions-body" class="divide-y divide-slate-100">
            @foreach ($retentions as $index => $row)
                @include('procurement.procurement-requests._retention-row', ['index' => $index, 'row' => $row])
            @endforeach
            </tbody>
        </table>
    </div>

    <template id="pr-retention-template">
        @include('procurement.procurement-requests._retention-row', [
            'index' => 0,
            'row' => ['retention_percent' => '', 'release_period' => ''],
        ])
    </template>
</section>
