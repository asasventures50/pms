@php
    $defaults = $invoiceDefaults ?? [];
    $oldPoIds = collect(old('purchase_order_ids', $defaults['purchase_order_ids'] ?? []))->map(fn ($id) => (int) $id)->all();
    $oldItemIds = collect(old('purchase_order_item_ids', $defaults['purchase_order_item_ids'] ?? []))->map(fn ($id) => (int) $id)->all();
    $oldMergeGroups = old('merge_groups', $defaults['merge_groups'] ?? []);
    $oldNotes = collect(old('notes', $defaults['notes'] ?? []))->map(fn ($note) => (string) $note)->filter()->values()->all();
    if ($oldNotes === []) {
        $oldNotes = [''];
    }
    $currencyCode = strtoupper(trim((string) old('currency_code', $defaults['currency_code'] ?? 'USD')));
    if (strlen($currencyCode) !== 3) {
        $currencyCode = 'USD';
    }
@endphp

<div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm space-y-6">
    <section>
        <h2 class="text-lg font-semibold text-slate-900">Purchase orders</h2>
        <p class="mt-1 text-sm text-slate-500">Select one or more purchase orders. Line items from all selected P.O.s appear below.</p>
        <div class="mt-4 max-w-2xl space-y-2 rounded-lg border border-slate-200 p-4" id="invoice-po-list"
             data-items-url-template="{{ url('/purchase-orders/__ID__/invoice-items') }}">
            @foreach ($purchaseOrders as $po)
                <label class="flex items-start gap-3 text-sm text-slate-800">
                    <input type="checkbox" name="purchase_order_ids[]" value="{{ $po->id }}"
                           data-invoice-po-checkbox
                           class="mt-0.5 rounded border-slate-300"
                           @checked(in_array($po->id, $oldPoIds, true))>
                    <span>
                        <span class="font-mono font-medium">{{ $po->po_number }}</span>
                        @if ($po->vendor_company_name)
                            <span class="text-slate-600">— {{ $po->vendor_company_name }}</span>
                        @endif
                        @if ($po->ordered_at)
                            <span class="text-slate-500">({{ $po->ordered_at->format('Y-m-d') }})</span>
                        @endif
                    </span>
                </label>
            @endforeach
        </div>
        @error('purchase_order_ids')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
        <p id="invoice-po-summary" class="mt-2 hidden text-sm text-slate-600"></p>
    </section>

    <section>
        <h2 class="text-lg font-semibold text-slate-900">Recipient</h2>
        <p class="mt-1 text-sm text-slate-500">Shown at the top of the printed invoice (Arabic).</p>
        <div class="mt-4 grid max-w-xl gap-4 sm:grid-cols-2">
            <div>
                <label for="recipient_name" class="block text-xs font-medium uppercase tracking-wide text-slate-500">اسم المستلم</label>
                <input type="text" name="recipient_name" id="recipient_name"
                       value="{{ old('recipient_name', $defaults['recipient_name'] ?? '') }}"
                       placeholder="e.g. اسم الشخص / اسم الشركة"
                       class="admin-filter-control mt-1 w-full @error('recipient_name') border-red-500 @enderror">
                @error('recipient_name')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>

        </div>
    </section>

    <section id="invoice-lines-section" class="@unless(count($oldPoIds)) hidden @endunless">
        <div class="flex flex-wrap items-end justify-between gap-3">
            <h2 class="text-lg font-semibold text-slate-900">Line items</h2>
            <div class="flex flex-wrap items-end gap-3">
                <div class="w-28">
                    <label for="currency_code" class="block text-xs font-medium uppercase tracking-wide text-slate-500">Currency</label>
                    <input type="text" name="currency_code" id="currency_code" maxlength="3"
                           value="{{ $currencyCode }}"
                           placeholder="USD"
                           class="admin-filter-control mt-1 uppercase @error('currency_code') border-red-500 @enderror"
                           autocomplete="off">
                    @error('currency_code')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    <p id="invoice-currency-hint" class="mt-1 hidden text-xs text-slate-500"></p>
                </div>
                <label class="inline-flex items-center gap-2 pb-2 text-sm text-slate-700">
                    <input type="checkbox" id="invoice-select-all-lines" class="rounded border-slate-300">
                    Select all
                </label>
            </div>
        </div>
        <p class="mt-1 text-sm text-slate-500">
            P.R. line codes (e.g. PR-20062026-2-02.1) are not shown on the invoice — only numbered rows with descriptions.
        </p>

        <div class="mt-4 overflow-x-auto rounded-lg border border-slate-200">
            <table class="min-w-full text-left text-sm">
                <thead class="bg-slate-50 text-xs font-semibold uppercase tracking-wide text-slate-500">
                <tr>
                    <th class="px-3 py-2 w-10"></th>
                    <th class="px-3 py-2">P.O.</th>
                    <th class="px-3 py-2">المشروع / المنطقة</th>
                    <th class="px-3 py-2">Description</th>
                    <th class="px-3 py-2">Qty</th>
                    <th class="px-3 py-2">Unit</th>
                    <th class="px-3 py-2 text-right">
                        <span data-invoice-price-label data-invoice-price-label-base="Unit price">Unit price{{ $currencyCode ? ' ('.$currencyCode.')' : '' }}</span>
                    </th>
                    <th class="px-3 py-2 text-right">
                        <span data-invoice-price-label data-invoice-price-label-base="Total">Total{{ $currencyCode ? ' ('.$currencyCode.')' : '' }}</span>
                    </th>
                </tr>
                </thead>
                <tbody id="invoice-lines-body" class="divide-y divide-slate-100">
                </tbody>
            </table>
        </div>
        @error('purchase_order_item_ids')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
        @error('merge_groups')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror

        <div id="invoice-merge-toolbar" class="mt-4 hidden flex flex-wrap items-center gap-3">
            <button type="button" id="invoice-merge-selected-btn"
                    class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-800 hover:bg-slate-50">
                دمج المحدد في سطر واحد
            </button>
            <p class="text-sm text-slate-500">حدّد 2 بنود أو أكثر (غير مدمجة) ثم اضغط الدمج. يمكنك تكرار العملية لمجموعات مختلفة.</p>
        </div>

        <div id="invoice-merge-groups" class="mt-4 space-y-3"></div>
        <div id="invoice-merge-groups-inputs"></div>
        <div id="invoice-selected-item-ids"></div>
    </section>

    <section id="invoice-notes-section" class="@unless(count($oldPoIds)) hidden @endunless">
        <h2 class="text-lg font-semibold text-slate-900">ملاحظات</h2>
        <p class="mt-1 text-sm text-slate-500">اختياري — كل مربع = نقطة واحدة بالطباعة. يمكنك تكبير المربع من الزاوية، وكتابة أكثر من سطر داخل الملاحظة (Enter أو Shift+Enter).</p>
        <div id="invoice-notes-list" class="mt-4 max-w-2xl space-y-3">
            @foreach ($oldNotes as $index => $note)
                <div class="invoice-note-row flex items-start gap-2" data-invoice-note-row>
                    <textarea name="notes[]" rows="3" data-invoice-note-input
                              placeholder="ملاحظة {{ $index + 1 }}"
                              class="admin-filter-control invoice-note-textarea min-h-[4.5rem] resize-y flex-1 @error('notes.'.$index) border-red-500 @enderror">{{ $note }}</textarea>
                    <button type="button" data-invoice-remove-note
                            class="shrink-0 rounded-lg border border-slate-300 px-3 py-2 text-sm text-slate-600 hover:bg-slate-50 @if($loop->first && count($oldNotes) === 1) hidden @endif"
                            title="حذف الملاحظة">×</button>
                </div>
            @endforeach
        </div>
        <button type="button" id="invoice-add-note-btn"
                class="mt-3 rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-800 hover:bg-slate-50">
            + إضافة ملاحظة
        </button>
        @error('notes')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
        @error('notes.*')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
    </section>

    <section id="invoice-fees-section" class="@unless(count($oldPoIds)) hidden @endunless">
        <h2 class="text-lg font-semibold text-slate-900">Additional fees</h2>
        <p class="mt-1 text-sm text-slate-500">Optional amounts added before the grand total on the printed invoice.</p>
        <div class="mt-4 grid max-w-2xl gap-4 sm:grid-cols-2">
            @foreach ([
                'transport_fees' => 'أجور نقل و مواصلات',
                'supervision_fees' => 'أجور متابعة و اشراف',
                'administrative_fees' => 'مصاريف و اجور ادارية',
                'logistics_fees' => 'مصاريف و اجور لوجستية',
            ] as $field => $label)
                <div>
                    <label for="{{ $field }}" class="block text-xs font-medium text-slate-600">{{ $label }}</label>
                    <input type="number" name="{{ $field }}" id="{{ $field }}"
                           value="{{ old($field, $defaults[$field] ?? '0') }}"
                           min="0" step="0.01"
                           data-invoice-fee-input
                           class="admin-filter-control mt-1 w-full text-right @error($field) border-red-500 @enderror">
                    @error($field)<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>
            @endforeach
        </div>
        <div class="mt-4 max-w-xs rounded-lg border border-slate-200 bg-slate-50 px-4 py-3 text-sm">
            <div class="flex items-center justify-between gap-4">
                <span class="font-medium text-slate-700">المجموع الكلي (preview)</span>
                <span id="invoice-grand-total-preview" class="font-semibold text-slate-900 tabular-nums">0.00</span>
            </div>
            <p class="mt-1 text-xs text-slate-500">Line items subtotal + additional fees</p>
        </div>
    </section>
</div>

<script type="application/json" id="invoice-old-item-ids">@json($oldItemIds)</script>
<script type="application/json" id="invoice-old-po-ids">@json($oldPoIds)</script>
<script type="application/json" id="invoice-old-merge-groups">@json($oldMergeGroups)</script>
