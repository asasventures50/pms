@php
    $po = $po ?? null;
    $retentions = old('retentions', $po?->retentions ?? []);
    if ($retentions === []) {
        $retentions = [['retention_percent' => '', 'release_period' => '']];
    }
    $showRetention = filter_var(old('show_retention', $po?->show_retention ?? true), FILTER_VALIDATE_BOOLEAN);
    $showMaintenance = filter_var(old('show_maintenance', $po?->show_maintenance ?? true), FILTER_VALIDATE_BOOLEAN);
    $afterSaleService = old('after_sale_service_applicable', $po?->after_sale_service_applicable);
@endphp

<div class="md:col-span-2">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <label class="block text-xs font-medium uppercase tracking-wide text-slate-500">Retention by year</label>
        <label class="inline-flex items-center gap-2 text-sm text-slate-700">
            <input type="hidden" name="show_retention" value="0">
            <input type="checkbox" name="show_retention" id="show_retention" value="1" @checked($showRetention)>
            Show on printed P.O.
        </label>
    </div>
    <p class="mt-0.5 text-xs text-slate-500">Imported from the linked P.R. Uncheck “Show on printed P.O.” to hide this section on the PDF; the data is still saved.</p>
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
        <h4 class="text-xs font-medium uppercase tracking-wide text-slate-500">Maintenance <span class="font-normal normal-case text-slate-500">(internal)</span></h4>
        <label class="inline-flex items-center gap-2 text-sm text-slate-700">
            <input type="hidden" name="show_maintenance" value="0">
            <input type="checkbox" name="show_maintenance" id="show_maintenance" value="1" @checked($showMaintenance)>
            Show on printed P.O.
        </label>
    </div>
    <p class="mt-0.5 text-xs text-slate-500">Imported from the linked P.R. Uncheck “Show on printed P.O.” to hide this section on the PDF; the data is still saved.</p>
    <div class="mt-4">
        <p class="text-xs font-medium uppercase tracking-wide text-slate-500">After-sale service applicable</p>
        <div class="mt-2 flex flex-wrap gap-4">
            <label class="inline-flex items-center gap-2 text-sm">
                <input type="radio" name="after_sale_service_applicable" value="1" @checked($afterSaleService === true || $afterSaleService === '1')> Yes
            </label>
            <label class="inline-flex items-center gap-2 text-sm">
                <input type="radio" name="after_sale_service_applicable" value="0" @checked($afterSaleService === false || $afterSaleService === '0')> No
            </label>
        </div>
    </div>
    <div class="mt-4 grid gap-4 md:grid-cols-2">
        <div>
            <label for="warranty_years" class="block text-xs font-medium uppercase tracking-wide text-slate-500">Warranty &amp; guarantee period (years)</label>
            <input type="number" name="warranty_years" id="warranty_years" step="0.1" min="0"
                   value="{{ old('warranty_years', $po?->warranty_years ?? '') }}"
                   class="admin-filter-control mt-1 w-full max-w-xs">
        </div>
        <div>
            <label for="warranty_coverage" class="block text-xs font-medium uppercase tracking-wide text-slate-500">Coverage / scope</label>
            <input type="text" name="warranty_coverage" id="warranty_coverage"
                   value="{{ old('warranty_coverage', $po?->warranty_coverage ?? '') }}"
                   class="admin-filter-control mt-1 w-full">
        </div>
    </div>
</div>
