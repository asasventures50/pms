@php
    $defaults = $invoiceDefaults ?? [];
    $source = old('source', $defaults['source'] ?? \App\Models\Procurement\Invoices\Invoice::SOURCE_PURCHASE_ORDER);
    $isManual = $source === \App\Models\Procurement\Invoices\Invoice::SOURCE_MANUAL;
    $oldPoIds = collect(old('purchase_order_ids', $defaults['purchase_order_ids'] ?? []))->map(fn ($id) => (int) $id)->all();
    $oldItemIds = collect(old('purchase_order_item_ids', $defaults['purchase_order_item_ids'] ?? []))->map(fn ($id) => (int) $id)->all();
    $oldMergeGroups = old('merge_groups', $defaults['merge_groups'] ?? []);
    $oldManualLines = collect(old('manual_lines', $defaults['manual_lines'] ?? []))
        ->map(fn ($line) => [
            'project_zone' => trim((string) ($line['project_zone'] ?? '')),
            'description' => trim((string) ($line['description'] ?? '')),
            'quantity' => $line['quantity'] ?? '',
            'unit' => trim((string) ($line['unit'] ?? '')),
            'unit_price' => $line['unit_price'] ?? '',
        ])
        ->values()
        ->all();
    if ($oldManualLines === [] && $isManual) {
        $oldManualLines = [['project_zone' => '', 'description' => '', 'quantity' => '', 'unit' => '', 'unit_price' => '']];
    }
    $showContentSections = $isManual || count($oldPoIds) > 0;
    $oldNotes = collect(old('notes', $defaults['notes'] ?? []))->map(fn ($note) => (string) $note)->filter()->values()->all();
    if ($oldNotes === []) {
        $oldNotes = [''];
    }
    $oldCustomFees = collect(old('custom_fees', $defaults['custom_fees'] ?? []))
        ->map(fn ($fee) => [
            'project_zone' => trim((string) ($fee['project_zone'] ?? '')),
            'description' => trim((string) ($fee['description'] ?? $fee['label'] ?? '')),
            'quantity' => $fee['quantity'] ?? '',
            'unit' => trim((string) ($fee['unit'] ?? '')),
            'unit_price' => $fee['unit_price'] ?? ($fee['amount'] ?? ''),
        ])
        ->values()
        ->all();
    $suggestedManualPoNumber = $suggestedManualPoNumber ?? null;
    $manualPoNumberValue = old('manual_po_number', $defaults['manual_po_number'] ?? $suggestedManualPoNumber ?? '');
    $currencyCode = strtoupper(trim((string) old('currency_code', $defaults['currency_code'] ?? 'USD')));
    if (strlen($currencyCode) !== 3) {
        $currencyCode = 'USD';
    }
@endphp

<div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm space-y-6">
    <section>
        <h2 class="text-lg font-semibold text-slate-900">مصدر الفاتورة</h2>
        <p class="mt-1 text-sm text-slate-500">اختر استيراد البنود من أمر شراء، أو إدخال كل البيانات يدوياً.</p>
        <div class="mt-4 flex flex-wrap gap-6">
            <label class="inline-flex items-center gap-2 text-sm text-slate-800">
                <input type="radio" name="source" value="{{ \App\Models\Procurement\Invoices\Invoice::SOURCE_PURCHASE_ORDER }}"
                       data-invoice-source-radio
                       class="border-slate-300"
                       @checked(!$isManual)>
                <span>من أمر شراء (P.O.)</span>
            </label>
            <label class="inline-flex items-center gap-2 text-sm text-slate-800">
                <input type="radio" name="source" value="{{ \App\Models\Procurement\Invoices\Invoice::SOURCE_MANUAL }}"
                       data-invoice-source-radio
                       class="border-slate-300"
                       @checked($isManual)>
                <span>إدخال يدوي</span>
            </label>
        </div>
        @error('source')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
    </section>

    <section id="invoice-po-section" class="@if($isManual) hidden @endif">
        <h2 class="text-lg font-semibold text-slate-900">Purchase orders</h2>
        <p class="mt-1 text-sm text-slate-500">Select one or more purchase orders. Line items from all selected P.O.s appear below.</p>
        <div class="mt-4 max-w-2xl space-y-2 rounded-lg border border-slate-200 p-4" id="invoice-po-list"
             data-items-url-template="{{ url('/purchase-orders/__ID__/invoice-items') }}">
            @foreach ($purchaseOrders as $po)
                <label class="flex items-start gap-3 text-sm text-slate-800">
                    <input type="checkbox" name="purchase_order_ids[]" value="{{ $po->id }}"
                           data-invoice-po-checkbox
                           data-invoice-po-field
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

    <section id="invoice-manual-header-section" class="@unless($isManual) hidden @endunless">
        <h2 class="text-lg font-semibold text-slate-900">بيانات أمر الشراء (يدوي)</h2>
        <p class="mt-1 text-sm text-slate-500">يُولَّد رقم أمر الشراء تلقائياً — يمكنك تعديله إذا لزم. اسم المورد يُدخل يدوياً.</p>
        <div class="mt-4 grid max-w-2xl gap-4 sm:grid-cols-2">
            <div>
                <label for="manual_po_number" class="block text-xs font-medium uppercase tracking-wide text-slate-500">رقم أمر الشراء</label>
                <input type="text" name="manual_po_number" id="manual_po_number"
                       value="{{ $manualPoNumberValue }}"
                       placeholder="PO-0001"
                       data-invoice-manual-field
                       data-invoice-manual-po-number
                       class="admin-filter-control mt-1 w-full font-mono @error('manual_po_number') border-red-500 @enderror">
                @error('manual_po_number')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="manual_vendor_name" class="block text-xs font-medium uppercase tracking-wide text-slate-500">اسم المورد / الشركة</label>
                <input type="text" name="manual_vendor_name" id="manual_vendor_name"
                       value="{{ old('manual_vendor_name', $defaults['manual_vendor_name'] ?? '') }}"
                       placeholder="e.g. اسم الشركة"
                       data-invoice-manual-field
                       class="admin-filter-control mt-1 w-full @error('manual_vendor_name') border-red-500 @enderror">
                @error('manual_vendor_name')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
        </div>
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

    <section id="invoice-lines-section" class="@unless($showContentSections && !$isManual) hidden @endunless">
        <div class="flex flex-wrap items-end justify-between gap-3">
            <h2 class="text-lg font-semibold text-slate-900">Line items</h2>
            <div class="flex flex-wrap items-end gap-3">
                <div class="w-28">
                    <label for="currency_code" class="block text-xs font-medium uppercase tracking-wide text-slate-500">Currency</label>
                    <input type="text" name="currency_code" id="currency_code" maxlength="3"
                           value="{{ $currencyCode }}"
                           placeholder="USD"
                           data-invoice-po-field
                           data-invoice-currency-input
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

    <section id="invoice-manual-lines-section" class="@unless($isManual) hidden @endunless">
        <div class="flex flex-wrap items-end justify-between gap-3">
            <h2 class="text-lg font-semibold text-slate-900">بنود الفاتورة (يدوي)</h2>
            <div class="w-28">
                <label for="currency_code_manual" class="block text-xs font-medium uppercase tracking-wide text-slate-500">Currency</label>
                <input type="text" name="currency_code" id="currency_code_manual" maxlength="3"
                       value="{{ $currencyCode }}"
                       placeholder="USD"
                       data-invoice-manual-field
                       data-invoice-currency-input
                       class="admin-filter-control mt-1 uppercase @error('currency_code') border-red-500 @enderror"
                       autocomplete="off">
            </div>
        </div>
        <p class="mt-1 text-sm text-slate-500">أضف بنود الفاتورة يدوياً — نفس الأعمدة التي تظهر بالطباعة.</p>

        <div class="mt-4 overflow-x-auto rounded-lg border border-slate-200">
            <table class="min-w-full text-left text-sm">
                <thead class="bg-slate-50 text-xs font-semibold uppercase tracking-wide text-slate-500">
                <tr>
                    <th class="px-3 py-2 w-10">م</th>
                    <th class="px-3 py-2">المشروع / المنطقة</th>
                    <th class="px-3 py-2">البيان</th>
                    <th class="px-3 py-2">الكمية</th>
                    <th class="px-3 py-2">الوحدة</th>
                    <th class="px-3 py-2 text-right">
                        <span data-invoice-manual-price-label data-invoice-price-label-base="سعر الوحدة">سعر الوحدة{{ $currencyCode ? ' ('.$currencyCode.')' : '' }}</span>
                    </th>
                    <th class="px-3 py-2 text-right">
                        <span data-invoice-manual-total-label data-invoice-price-label-base="المجموع">المجموع{{ $currencyCode ? ' ('.$currencyCode.')' : '' }}</span>
                    </th>
                    <th class="px-3 py-2 w-10"></th>
                </tr>
                </thead>
                <tbody id="invoice-manual-lines-body" class="divide-y divide-slate-100">
                @foreach ($oldManualLines as $index => $manualLine)
                    <tr data-invoice-manual-line-row>
                        <td class="px-3 py-2 text-center text-slate-500" data-invoice-manual-line-num>{{ $index + 1 }}</td>
                        <td class="px-3 py-2">
                            <input type="text" name="manual_lines[{{ $index }}][project_zone]"
                                   value="{{ $manualLine['project_zone'] }}"
                                   placeholder="مثال: قاسيون"
                                   data-invoice-manual-field
                                   data-invoice-manual-line-project-zone
                                   class="admin-filter-control w-full min-w-[8rem]">
                        </td>
                        <td class="px-3 py-2">
                            <input type="text" name="manual_lines[{{ $index }}][description]"
                                   value="{{ $manualLine['description'] }}"
                                   placeholder="وصف البند"
                                   data-invoice-manual-field
                                   data-invoice-manual-line-description
                                   class="admin-filter-control w-full min-w-[10rem] @error('manual_lines.'.$index.'.description') border-red-500 @enderror">
                            @error('manual_lines.'.$index.'.description')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                        </td>
                        <td class="px-3 py-2">
                            <input type="number" name="manual_lines[{{ $index }}][quantity]"
                                   value="{{ $manualLine['quantity'] }}"
                                   min="0.001" step="0.001"
                                   data-invoice-manual-field
                                   data-invoice-manual-line-quantity
                                   class="admin-filter-control w-24 text-right @error('manual_lines.'.$index.'.quantity') border-red-500 @enderror">
                            @error('manual_lines.'.$index.'.quantity')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                        </td>
                        <td class="px-3 py-2">
                            <input type="text" name="manual_lines[{{ $index }}][unit]"
                                   value="{{ $manualLine['unit'] }}"
                                   placeholder="مثال: قطعة"
                                   data-invoice-manual-field
                                   data-invoice-manual-line-unit
                                   class="admin-filter-control w-24">
                        </td>
                        <td class="px-3 py-2">
                            <input type="number" name="manual_lines[{{ $index }}][unit_price]"
                                   value="{{ $manualLine['unit_price'] }}"
                                   min="0" step="0.01"
                                   data-invoice-manual-field
                                   data-invoice-manual-line-unit-price
                                   class="admin-filter-control w-28 text-right @error('manual_lines.'.$index.'.unit_price') border-red-500 @enderror">
                            @error('manual_lines.'.$index.'.unit_price')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                        </td>
                        <td class="px-3 py-2 text-right font-medium text-slate-900 tabular-nums" data-invoice-manual-line-total>0.00</td>
                        <td class="px-3 py-2 text-center">
                            <button type="button" data-invoice-remove-manual-line
                                    class="rounded-lg border border-slate-300 px-2 py-1 text-sm text-slate-600 hover:bg-slate-50"
                                    title="حذف البند">×</button>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
        <button type="button" id="invoice-add-manual-line-btn"
                class="mt-3 rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-800 hover:bg-slate-50">
            + إضافة بند
        </button>
        @error('manual_lines')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
        <div class="mt-4 max-w-xs rounded-lg border border-slate-200 bg-slate-50 px-4 py-3 text-sm">
            <div class="flex items-center justify-between gap-4">
                <span class="font-medium text-slate-700">المجموع الكلي (preview)</span>
                <span id="invoice-grand-total-preview" class="font-semibold text-slate-900 tabular-nums">0.00</span>
            </div>
        </div>
    </section>

    <section id="invoice-notes-section" class="@unless($showContentSections) hidden @endunless">
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

    <section id="invoice-fees-section" class="@unless($showContentSections && !$isManual) hidden @endunless">
        <h2 class="text-lg font-semibold text-slate-900">رسوم إضافية</h2>
        <p class="mt-1 text-sm text-slate-500">اختياري — أضف بنوداً يدوياً بنفس أعمدة جدول الفاتورة. تظهر بالطباعة مرقّمة بعد بنود P.O. وتُجمع في المجموع الكلي.</p>
        <div class="mt-4 overflow-x-auto rounded-lg border border-slate-200">
            <table class="min-w-full text-left text-sm">
                <thead class="bg-slate-50 text-xs font-semibold uppercase tracking-wide text-slate-500">
                <tr>
                    <th class="px-3 py-2 w-10">م</th>
                    <th class="px-3 py-2">المشروع / المنطقة</th>
                    <th class="px-3 py-2">البيان</th>
                    <th class="px-3 py-2">الكمية</th>
                    <th class="px-3 py-2">الوحدة</th>
                    <th class="px-3 py-2 text-right">
                        <span data-invoice-fee-price-label data-invoice-price-label-base="سعر الوحدة">سعر الوحدة{{ $currencyCode ? ' ('.$currencyCode.')' : '' }}</span>
                    </th>
                    <th class="px-3 py-2 text-right">
                        <span data-invoice-fee-total-label data-invoice-price-label-base="المجموع">المجموع{{ $currencyCode ? ' ('.$currencyCode.')' : '' }}</span>
                    </th>
                    <th class="px-3 py-2 w-10"></th>
                </tr>
                </thead>
                <tbody id="invoice-custom-fees-body" class="divide-y divide-slate-100">
                @foreach ($oldCustomFees as $index => $customFee)
                    <tr data-invoice-custom-fee-row>
                        <td class="px-3 py-2 text-center text-slate-500" data-invoice-custom-fee-num>{{ $index + 1 }}</td>
                        <td class="px-3 py-2">
                            <input type="text" name="custom_fees[{{ $index }}][project_zone]"
                                   value="{{ $customFee['project_zone'] }}"
                                   placeholder="مثال: قاسيون"
                                   data-invoice-po-field
                                   data-invoice-custom-fee-project-zone
                                   class="admin-filter-control w-full min-w-[8rem] @error('custom_fees.'.$index.'.project_zone') border-red-500 @enderror">
                            @error('custom_fees.'.$index.'.project_zone')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                        </td>
                        <td class="px-3 py-2">
                            <input type="text" name="custom_fees[{{ $index }}][description]"
                                   value="{{ $customFee['description'] }}"
                                   placeholder="مثال: أجور متابعة و اشراف"
                                   data-invoice-po-field
                                   data-invoice-custom-fee-description
                                   class="admin-filter-control w-full min-w-[10rem] @error('custom_fees.'.$index.'.description') border-red-500 @enderror">
                            @error('custom_fees.'.$index.'.description')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                        </td>
                        <td class="px-3 py-2">
                            <input type="number" name="custom_fees[{{ $index }}][quantity]"
                                   value="{{ $customFee['quantity'] }}"
                                   min="0" step="0.001"
                                   data-invoice-po-field
                                   data-invoice-custom-fee-quantity
                                   class="admin-filter-control w-24 text-right @error('custom_fees.'.$index.'.quantity') border-red-500 @enderror">
                            @error('custom_fees.'.$index.'.quantity')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                        </td>
                        <td class="px-3 py-2">
                            <input type="text" name="custom_fees[{{ $index }}][unit]"
                                   value="{{ $customFee['unit'] }}"
                                   placeholder="مثال: يوم"
                                   data-invoice-po-field
                                   data-invoice-custom-fee-unit
                                   class="admin-filter-control w-24 @error('custom_fees.'.$index.'.unit') border-red-500 @enderror">
                            @error('custom_fees.'.$index.'.unit')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                        </td>
                        <td class="px-3 py-2">
                            <input type="number" name="custom_fees[{{ $index }}][unit_price]"
                                   value="{{ $customFee['unit_price'] }}"
                                   min="0" step="0.01"
                                   data-invoice-po-field
                                   data-invoice-custom-fee-unit-price
                                   class="admin-filter-control w-28 text-right @error('custom_fees.'.$index.'.unit_price') border-red-500 @enderror">
                            @error('custom_fees.'.$index.'.unit_price')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                        </td>
                        <td class="px-3 py-2 text-right font-medium text-slate-900 tabular-nums" data-invoice-custom-fee-line-total>0.00</td>
                        <td class="px-3 py-2 text-center">
                            <button type="button" data-invoice-remove-custom-fee
                                    class="rounded-lg border border-slate-300 px-2 py-1 text-sm text-slate-600 hover:bg-slate-50"
                                    title="حذف البند">×</button>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
        <button type="button" id="invoice-add-custom-fee-btn"
                class="mt-3 rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-800 hover:bg-slate-50">
            + إضافة بند
        </button>
        @error('custom_fees')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
        <div class="mt-4 max-w-xs rounded-lg border border-slate-200 bg-slate-50 px-4 py-3 text-sm">
            <div class="flex items-center justify-between gap-4">
                <span class="font-medium text-slate-700">المجموع الكلي (preview)</span>
                <span id="invoice-grand-total-preview-po" class="font-semibold text-slate-900 tabular-nums">0.00</span>
            </div>
            <p class="mt-1 text-xs text-slate-500">Line items subtotal + additional fees</p>
        </div>
    </section>
</div>

<script type="application/json" id="invoice-old-item-ids">@json($oldItemIds)</script>
<script type="application/json" id="invoice-old-po-ids">@json($oldPoIds)</script>
<script type="application/json" id="invoice-old-merge-groups">@json($oldMergeGroups)</script>
<script type="application/json" id="invoice-initial-source">@json($source)</script>
<script type="application/json" id="invoice-suggested-manual-po">@json($suggestedManualPoNumber ?? '')</script>
<script type="application/json" id="invoice-old-manual-lines">@json($oldManualLines)</script>
