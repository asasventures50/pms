@php
    $po = $po ?? null;
    $retentions = old('retentions', $po?->retentions ?? []);
    if ($retentions === []) {
        $retentions = [['retention_percent' => '', 'release_period' => '']];
    }
    $showRetention = filter_var(old('show_retention', $po?->show_retention ?? true), FILTER_VALIDATE_BOOLEAN);
    $showInsurance = filter_var(old('show_insurance', $po?->show_insurance ?? true), FILTER_VALIDATE_BOOLEAN);
    $primaryInsurance = old('primary_insurance_applicable', $po?->primary_insurance_applicable);
    $finalInsurance = old('final_insurance_applicable', $po?->final_insurance_applicable);
@endphp

<div class="md:col-span-2">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <label class="block text-xs font-medium uppercase tracking-wide text-slate-500">Retention by year</label>
        <label class="inline-flex items-center gap-2 text-sm text-slate-700">
            <input type="hidden" name="show_retention" value="0">
            <input type="checkbox" name="show_retention" id="show_retention" value="1" @checked($showRetention)>
            Include on purchase order
        </label>
    </div>
    <p class="mt-0.5 text-xs text-slate-500">Imported from the linked P.R. Uncheck to omit from the printed P.O.</p>
    <div class="mt-3 overflow-x-auto">
        <table class="min-w-full text-left text-sm">
            <thead class="text-xs font-semibold uppercase tracking-wide text-slate-500">
            <tr>
                <th class="px-2 py-2">Retention %</th>
                <th class="px-2 py-2">Release period</th>
                <th class="px-2 py-2 print:hidden"></th>
            </tr>
            </thead>
            <tbody id="po-retentions-body" class="divide-y divide-slate-100">
            @foreach ($retentions as $index => $row)
                @include('procurement.purchase-orders._retention-row', ['index' => $index, 'row' => $row])
            @endforeach
            </tbody>
        </table>
    </div>
    <button type="button" id="po-add-retention"
            class="mt-2 rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-sm font-medium text-slate-800 hover:bg-slate-50">
        Add row
    </button>
    <template id="po-retention-template">
        @include('procurement.purchase-orders._retention-row', [
            'index' => 0,
            'row' => ['retention_percent' => '', 'release_period' => ''],
        ])
    </template>
</div>

<div class="md:col-span-2">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <h4 class="text-xs font-medium uppercase tracking-wide text-slate-500">Insurance requirements</h4>
        <label class="inline-flex items-center gap-2 text-sm text-slate-700">
            <input type="hidden" name="show_insurance" value="0">
            <input type="checkbox" name="show_insurance" id="show_insurance" value="1" @checked($showInsurance)>
            Include on purchase order
        </label>
    </div>
    <p class="mt-0.5 text-xs text-slate-500">Imported from the linked P.R. Uncheck to omit from the printed P.O.</p>
    <div class="mt-4 grid gap-4 md:grid-cols-2">
        <div>
            <p class="text-xs font-medium uppercase tracking-wide text-slate-500">Primary insurance applicable</p>
            <div class="mt-2 flex flex-wrap gap-4">
                <label class="inline-flex items-center gap-2 text-sm">
                    <input type="radio" name="primary_insurance_applicable" value="1" @checked($primaryInsurance === true || $primaryInsurance === '1')> Yes
                </label>
                <label class="inline-flex items-center gap-2 text-sm">
                    <input type="radio" name="primary_insurance_applicable" value="0" @checked($primaryInsurance === false || $primaryInsurance === '0')> No
                </label>
            </div>
            <label for="primary_insurance_requirements" class="mt-3 block text-xs font-medium uppercase tracking-wide text-slate-500">Requirements</label>
            <textarea name="primary_insurance_requirements" id="primary_insurance_requirements" rows="3"
                      class="admin-form-textarea mt-1 w-full resize-y">{{ old('primary_insurance_requirements', $po?->primary_insurance_requirements ?? '') }}</textarea>
        </div>
        <div>
            <p class="text-xs font-medium uppercase tracking-wide text-slate-500">Final insurance requirements applicable</p>
            <div class="mt-2 flex flex-wrap gap-4">
                <label class="inline-flex items-center gap-2 text-sm">
                    <input type="radio" name="final_insurance_applicable" value="1" @checked($finalInsurance === true || $finalInsurance === '1')> Yes
                </label>
                <label class="inline-flex items-center gap-2 text-sm">
                    <input type="radio" name="final_insurance_applicable" value="0" @checked($finalInsurance === false || $finalInsurance === '0')> No
                </label>
            </div>
            <label for="final_insurance_requirements" class="mt-3 block text-xs font-medium uppercase tracking-wide text-slate-500">Requirements</label>
            <textarea name="final_insurance_requirements" id="final_insurance_requirements" rows="3"
                      class="admin-form-textarea mt-1 w-full resize-y">{{ old('final_insurance_requirements', $po?->final_insurance_requirements ?? '') }}</textarea>
        </div>
    </div>
</div>
