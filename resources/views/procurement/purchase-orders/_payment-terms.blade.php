@php
    $po = $po ?? null;
    $canCreateInvoice = auth()->user()?->hasPermission('invoices.create') ?? false;
    $formRows = old('payment_term_rows');
    if (! is_array($formRows)) {
        $formRows = $po?->relationLoaded('paymentTermRows')
            ? $po->paymentTermRows->map(fn ($row) => [
                'id' => $row->id,
                'milestone' => $row->milestone,
                'percentage' => $row->percentage,
                'amount' => $row->amount,
                'currency_code' => $row->currency_code,
                'notes' => $row->notes,
                'invoice_id' => $row->invoice_id,
                'invoice' => $row->invoice
                    ? ['id' => $row->invoice->id, 'invoice_number' => $row->invoice->invoice_number]
                    : null,
            ])->all()
            : [];
    }
    if ($formRows === []) {
        $formRows = [['id' => '', 'milestone' => '', 'percentage' => '', 'amount' => '', 'currency_code' => '', 'notes' => '']];
    }
    $showPaymentTerms = filter_var(old('show_payment_terms', $po?->show_payment_terms ?? true), FILTER_VALIDATE_BOOLEAN);
@endphp

<div class="md:col-span-2" id="po-payment-terms-section">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <label class="block text-xs font-medium uppercase tracking-wide text-slate-500">Payment terms</label>
        <label class="inline-flex items-center gap-2 text-sm text-slate-700">
            <input type="hidden" name="show_payment_terms" value="0">
            <input type="checkbox" name="show_payment_terms" id="show_payment_terms" value="1" @checked($showPaymentTerms)>
            Show on printed P.O.
        </label>
    </div>
    <p class="mt-0.5 text-xs text-slate-500">
        Imported from the linked P.R. when you import lines. Each payment term name is required and must be unique in this table. Enter a percentage to fill the amount from the P.O. total, or enter an amount to fill the percentage. Uncheck “Show on printed P.O.” to hide this table on the PDF; the data is still saved.
    </p>

    <div class="mt-3 overflow-x-auto">
        <table class="min-w-full text-left text-sm">
            <thead class="text-xs font-semibold uppercase tracking-wide text-slate-500">
            <tr>
                <th class="px-2 py-2">Payment term <span class="text-red-600">*</span></th>
                <th class="px-2 py-2">Percentage (%)</th>
                <th class="px-2 py-2">Amount</th>
                <th class="px-2 py-2">Currency</th>
                <th class="px-2 py-2">Note</th>
                <th class="px-2 py-2 print:hidden">Invoice</th>
                <th class="px-2 py-2 print:hidden"></th>
            </tr>
            </thead>
            <tbody id="po-payment-terms-body" class="divide-y divide-slate-100">
            @foreach ($formRows as $index => $row)
                @include('procurement.purchase-orders._payment-term-row', [
                    'index' => $index,
                    'row' => $row,
                    'po' => $po,
                    'canCreateInvoice' => $canCreateInvoice,
                ])
            @endforeach
            </tbody>
            <tfoot>
            <tr class="border-t border-slate-200 text-sm">
                <td class="px-2 py-2 font-medium text-slate-700">Total</td>
                <td class="px-2 py-2 font-mono tabular-nums">
                    <span id="po-payment-terms-pct-total">0</span>%
                    <span id="po-payment-terms-pct-badge" class="ml-2 hidden rounded-full px-2 py-0.5 text-xs font-medium"></span>
                </td>
                <td class="px-2 py-2 font-mono tabular-nums" id="po-payment-terms-amt-total">0.00</td>
                <td class="px-2 py-2 print:hidden" colspan="4"></td>
            </tr>
            </tfoot>
        </table>
    </div>
    <p id="po-payment-term-name-error"
       class="mt-2 text-sm text-red-600 @if (! collect($errors->keys())->contains(fn ($key) => preg_match('/^payment_term_rows\.\d+\.milestone$/', $key))) hidden @endif">
        Each payment term is required and must be unique in this table.
    </p>

    <div class="mt-2 flex flex-wrap items-center gap-2">
        <button type="button" id="po-add-payment-term"
                class="rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-sm font-medium text-slate-800 hover:bg-slate-50">
            Add row
        </button>
    </div>

    <template id="po-payment-term-template">
        @include('procurement.purchase-orders._payment-term-row', [
            'index' => 0,
            'row' => ['id' => '', 'milestone' => '', 'percentage' => '', 'amount' => '', 'currency_code' => '', 'notes' => ''],
            'po' => $po,
            'canCreateInvoice' => $canCreateInvoice,
        ])
    </template>
</div>
