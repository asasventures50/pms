@php
    $docNumber = $docNumber ?? $procurementRequest?->request_number ?? ($nextCode ?? '');
@endphp
<table class="w-full border-2 border-slate-900 border-collapse text-slate-900">
    <tr>
        <td class="w-[22%] min-h-[5rem] border border-slate-900 p-2 align-middle" aria-hidden="true">&nbsp;</td>
        <td class="w-[56%] border border-slate-900 p-4 text-center align-middle">
            <p class="text-xl font-bold tracking-tight sm:text-2xl">Procurement Request</p>
        </td>
        <td class="w-[22%] border border-slate-900 p-2 text-center align-middle">
            <p class="text-xs font-medium uppercase tracking-wide text-slate-500">Doc No.</p>
            <p id="pr-doc-number-preview"
               data-preview="{{ $nextCode ?? '' }}"
               class="mt-1 font-mono text-sm font-semibold text-slate-900">{{ $docNumber ?: '—' }}</p>
        </td>
    </tr>
</table>
