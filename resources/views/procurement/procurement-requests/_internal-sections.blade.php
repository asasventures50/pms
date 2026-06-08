@php
    $timeline = old('timeline', $formDefaults['timeline'] ?? []);
    $approvals = old('approvals', $formDefaults['approvals'] ?? []);
    $ndaRequired = old('nda_required', $formDefaults['nda_required'] ?? null);
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
    <p class="mt-3 text-xs font-medium uppercase tracking-wide text-slate-500">NDA required</p>
    <div class="mt-2 flex gap-4">
        <label class="inline-flex items-center gap-2 text-sm"><input type="radio" name="nda_required" value="1" @checked($ndaRequired === true || $ndaRequired === '1')> Yes</label>
        <label class="inline-flex items-center gap-2 text-sm"><input type="radio" name="nda_required" value="0" @checked($ndaRequired === false || $ndaRequired === '0')> No</label>
    </div>
</section>

<section class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
    <h3 class="text-sm font-semibold text-slate-900">Approvals <span class="font-normal text-slate-500">(internal)</span></h3>
    <div class="mt-4 overflow-x-auto">
        <table class="min-w-full text-left text-sm">
            <thead class="text-xs font-semibold uppercase tracking-wide text-slate-500">
            <tr>
                <th class="px-2 py-2">Role</th>
                <th class="px-2 py-2">Name</th>
                <th class="px-2 py-2">Signature</th>
                <th class="px-2 py-2">Date</th>
            </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
            @foreach ($approvals as $index => $row)
                <tr>
                    <td class="px-2 py-2 font-medium text-slate-800">
                        {{ $row['label'] ?? $row['role'] ?? '' }}
                        <input type="hidden" name="approvals[{{ $index }}][role]" value="{{ $row['role'] ?? '' }}">
                    </td>
                    <td class="px-2 py-2"><input type="text" name="approvals[{{ $index }}][name]" value="{{ old("approvals.$index.name", $row['name'] ?? '') }}" class="admin-filter-control w-full min-w-[8rem]"></td>
                    <td class="px-2 py-2"><input type="text" name="approvals[{{ $index }}][signature]" value="{{ old("approvals.$index.signature", $row['signature'] ?? '') }}" class="admin-filter-control w-full min-w-[8rem]"></td>
                    <td class="px-2 py-2"><input type="date" name="approvals[{{ $index }}][signed_at]" value="{{ old("approvals.$index.signed_at", $row['signed_at'] ?? '') }}" class="admin-filter-control w-full max-w-[10rem]"></td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</section>
