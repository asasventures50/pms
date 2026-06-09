@php
    $paymentTerms = old('payment_terms', $formDefaults['payment_terms'] ?? [['milestone' => '', 'amount' => '', 'percentage' => '', 'due_upon' => '']]);
@endphp

<section class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
    <div class="flex items-center justify-between gap-3">
        <h3 class="text-sm font-semibold text-slate-900">Payment terms <span class="font-normal text-slate-500">(internal)</span></h3>
        <button type="button" id="pr-add-payment-term"
                class="rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-sm font-medium text-slate-800 hover:bg-slate-50 print:hidden">
            Add row
        </button>
    </div>

    <div class="mt-4 overflow-x-auto">
        <table class="min-w-full text-left text-sm">
            <thead class="text-xs font-semibold uppercase tracking-wide text-slate-500">
            <tr>
                <th class="px-2 py-2">Milestone</th>
                <th class="px-2 py-2">Amount / milestone no.</th>
                <th class="px-2 py-2">%</th>
                <th class="px-2 py-2">Due upon</th>
                <th class="px-2 py-2 print:hidden"></th>
            </tr>
            </thead>
            <tbody id="pr-payment-terms-body" class="divide-y divide-slate-100">
            @foreach ($paymentTerms as $index => $row)
                @include('procurement.procurement-requests._payment-term-row', ['index' => $index, 'row' => $row])
            @endforeach
            </tbody>
        </table>
    </div>

    <template id="pr-payment-term-template">
        @include('procurement.procurement-requests._payment-term-row', [
            'index' => 0,
            'row' => ['milestone' => '', 'amount' => '', 'percentage' => '', 'due_upon' => ''],
        ])
    </template>
</section>
