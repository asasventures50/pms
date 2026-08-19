@php
    $row = $row ?? ['id' => '', 'milestone' => '', 'percentage' => '', 'amount' => '', 'currency_code' => '', 'notes' => ''];
    $invoice = $row['invoice'] ?? null;
    $invoiceId = $invoice['id'] ?? ($row['invoice_id'] ?? null);
    $invoiceNumber = $invoice['invoice_number'] ?? ($row['invoice_number'] ?? '');
    $locked = filled($invoiceId);
    $poExists = ($po ?? null)?->exists ?? false;
    $canCreateInvoice = $canCreateInvoice ?? false;
    $poCurrency = strtoupper(trim((string) old('currency_code', $po?->currency_code ?? auth()->user()?->defaultCurrencyCode() ?? '')));
    $rowCurrency = strtoupper(trim((string) ($row['currency_code'] ?? '')));
    $currencyValue = $rowCurrency !== '' ? $rowCurrency : $poCurrency;
@endphp

<tr class="po-payment-term-row" @if ($locked) data-invoiced="1" @endif>
    <td class="px-2 py-2">
        <input type="hidden" name="payment_term_rows[{{ $index }}][id]" value="{{ $row['id'] ?? '' }}" data-name="id">
        @if ($locked)
            <input type="hidden" name="payment_term_rows[{{ $index }}][milestone]" value="{{ $row['milestone'] ?? '' }}" data-name="milestone">
        @endif
        <input type="text" @unless($locked) name="payment_term_rows[{{ $index }}][milestone]" @endunless
               value="{{ $row['milestone'] ?? '' }}"
               data-name="milestone"
               class="po-bilingual-text po-payment-term-milestone admin-filter-control w-full min-w-[12rem] @error('payment_term_rows.'.$index.'.milestone') border-red-500 @enderror"
               @disabled($locked)>
        @error('payment_term_rows.'.$index.'.milestone')
            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
        @enderror
    </td>
    <td class="px-2 py-2">
        @if ($locked)
            <input type="hidden" name="payment_term_rows[{{ $index }}][percentage]" value="{{ $row['percentage'] ?? '' }}" data-name="percentage">
        @endif
        <input type="number" @unless($locked) name="payment_term_rows[{{ $index }}][percentage]" @endunless
               value="{{ $row['percentage'] ?? '' }}"
               data-name="percentage" min="0" max="100" step="0.01"
               class="po-payment-term-percentage admin-filter-control w-24 text-right font-mono"
               @disabled($locked)>
    </td>
    <td class="px-2 py-2">
        @if ($locked)
            <input type="hidden" name="payment_term_rows[{{ $index }}][amount]" value="{{ $row['amount'] ?? '' }}" data-name="amount">
        @endif
        <input type="number" @unless($locked) name="payment_term_rows[{{ $index }}][amount]" @endunless
               value="{{ $row['amount'] ?? '' }}"
               data-name="amount" min="0" step="0.01"
               class="po-payment-term-amount admin-filter-control w-32 text-right font-mono"
               @disabled($locked)>
    </td>
    <td class="px-2 py-2">
        @if ($locked)
            <input type="hidden" name="payment_term_rows[{{ $index }}][currency_code]" value="{{ $currencyValue }}" data-name="currency_code">
        @endif
        <input type="text" @unless($locked) name="payment_term_rows[{{ $index }}][currency_code]" @endunless
               value="{{ $currencyValue }}"
               data-name="currency_code" maxlength="3"
               class="po-payment-term-currency admin-filter-control w-20 uppercase text-center font-mono"
               autocomplete="off"
               @disabled($locked)>
    </td>
    <td class="px-2 py-2">
        @if ($locked)
            <input type="hidden" name="payment_term_rows[{{ $index }}][notes]" value="{{ $row['notes'] ?? '' }}" data-name="notes">
        @endif
        <input type="text" @unless($locked) name="payment_term_rows[{{ $index }}][notes]" @endunless
               value="{{ $row['notes'] ?? '' }}"
               data-name="notes"
               placeholder="Optional"
               class="po-bilingual-text po-payment-term-notes admin-filter-control w-full min-w-[8rem]"
               @disabled($locked)>
    </td>
    <td class="px-2 py-2 print:hidden whitespace-nowrap">
        @if ($locked && filled($invoiceId))
            <a href="{{ route('invoices.show', $invoiceId) }}"
               class="inline-flex items-center rounded-full bg-slate-100 px-2.5 py-1 text-xs font-medium text-slate-800 hover:bg-slate-200">
                View invoice{{ $invoiceNumber !== '' ? ' '.$invoiceNumber : '' }}
            </a>
        @elseif ($poExists && filled($row['id'] ?? '') && $canCreateInvoice)
            <a href="{{ route('invoices.create', ['source' => \App\Models\Procurement\Invoices\Invoice::SOURCE_PO_PAYMENT_TERM, 'po_id' => $po->id, 'milestone_ids' => [(int) $row['id']]]) }}"
               class="po-payment-term-create-invoice rounded-lg border border-slate-300 bg-white px-2.5 py-1 text-xs font-medium text-slate-800 hover:bg-slate-50">
                Create invoice
            </a>
        @elseif (! $poExists)
            <span class="text-xs text-slate-400">Save P.O. first</span>
        @endif
    </td>
    <td class="px-2 py-2 print:hidden">
        @if (! $locked)
            <button type="button" class="po-remove-payment-term text-sm text-red-600 hover:text-red-800">Remove</button>
        @endif
    </td>
</tr>
