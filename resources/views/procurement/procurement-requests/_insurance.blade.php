@php
    $primaryInsurance = old('primary_insurance_applicable', $formDefaults['primary_insurance_applicable'] ?? null);
    $finalInsurance = old('final_insurance_applicable', $formDefaults['final_insurance_applicable'] ?? null);
@endphp

<section class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
    <h3 class="text-sm font-semibold text-slate-900">Insurance requirements</h3>

    <div class="mt-4 grid gap-6 sm:grid-cols-2">
        <div>
            <p class="text-xs font-medium uppercase tracking-wide text-slate-500">Primary insurance applicable</p>
            <div class="mt-2 flex gap-4">
                <label class="inline-flex items-center gap-2 text-sm"><input type="radio" name="primary_insurance_applicable" value="1" @checked($primaryInsurance === true || $primaryInsurance === '1')> Yes</label>
                <label class="inline-flex items-center gap-2 text-sm"><input type="radio" name="primary_insurance_applicable" value="0" @checked($primaryInsurance === false || $primaryInsurance === '0')> No</label>
            </div>
        </div>
        <div>
            <p class="text-xs font-medium uppercase tracking-wide text-slate-500">Final insurance requirements applicable</p>
            <div class="mt-2 flex gap-4">
                <label class="inline-flex items-center gap-2 text-sm"><input type="radio" name="final_insurance_applicable" value="1" @checked($finalInsurance === true || $finalInsurance === '1')> Yes</label>
                <label class="inline-flex items-center gap-2 text-sm"><input type="radio" name="final_insurance_applicable" value="0" @checked($finalInsurance === false || $finalInsurance === '0')> No</label>
            </div>
        </div>
    </div>

    <div class="mt-6 grid gap-4 sm:grid-cols-2">
        <div>
            <label for="warranty_years" class="block text-xs font-medium uppercase tracking-wide text-slate-500">Warranty & guarantee period (years)</label>
            <input type="number" name="warranty_years" id="warranty_years" step="0.1" min="0"
                   value="{{ old('warranty_years', $formDefaults['warranty_years'] ?? '') }}"
                   class="admin-filter-control mt-1 w-full max-w-xs">
        </div>
        <div>
            <label for="warranty_coverage" class="block text-xs font-medium uppercase tracking-wide text-slate-500">Coverage / scope</label>
            <input type="text" name="warranty_coverage" id="warranty_coverage"
                   value="{{ old('warranty_coverage', $formDefaults['warranty_coverage'] ?? '') }}"
                   class="admin-filter-control mt-1 w-full">
        </div>
    </div>
</section>
