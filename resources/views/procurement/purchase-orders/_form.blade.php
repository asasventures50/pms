@php
    use App\Enums\Procurement\PrCompany;
    use App\Services\Procurement\PurchaseOrders\PurchaseOrderBuyerCompanyApplier;

    $po = $purchaseOrder ?? null;
    $lineItems = old('items', $defaultItems ?? [['item' => '', 'description' => '', 'quantity' => 1, 'unit_price' => 0]]);
    $poCompany = $po?->exists
        ? PurchaseOrderBuyerCompanyApplier::resolveForPurchaseOrder($po)
        : PrCompany::AsasVentures;
@endphp

<div class="space-y-8">
    <section class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
        <div class="flex flex-col gap-1 border-b border-slate-100 pb-4 sm:flex-row sm:items-center sm:justify-between">
            <h2 class="text-lg font-semibold text-slate-900">Purchase Order</h2>
            <p class="text-sm text-slate-500">Procurement Department</p>
        </div>

        <h3 class="mt-6 text-sm font-semibold uppercase tracking-wide text-slate-700">Order information</h3>
        <div class="mt-4 grid gap-4 md:grid-cols-3">
            <div>
                <label class="block text-xs font-medium uppercase tracking-wide text-slate-500">Requested by</label>
                <p class="mt-1 rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-800">
                    {{ $po?->creator?->name ?? auth()->user()->name }}
                </p>
                @if ($po?->exists)
                    <p class="mt-1 text-xs text-slate-500">Created {{ $po->created_at?->format('Y-m-d H:i') }}</p>
                @endif
            </div>
            <div>
                <label for="po_number" class="block text-xs font-medium uppercase tracking-wide text-slate-500">P.O. number</label>
                <input type="text" name="po_number" id="po_number"
                       value="{{ old('po_number', $po?->po_number ?? ($nextCode ?? '')) }}"
                       class="admin-filter-control font-mono @error('po_number') border-red-500 @enderror">
                @error('po_number')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="ordered_at" class="block text-xs font-medium uppercase tracking-wide text-slate-500">Date</label>
                <input type="date" name="ordered_at" id="ordered_at"
                       value="{{ old('ordered_at', $po?->ordered_at?->format('Y-m-d') ?? now()->format('Y-m-d')) }}"
                       class="admin-filter-control @error('ordered_at') border-red-500 @enderror">
                @error('ordered_at')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
            <div class="md:col-span-2">
                <label for="procurement_request_id" class="block text-xs font-medium uppercase tracking-wide text-slate-500">Linked P.R.</label>
                <div class="mt-1 flex flex-wrap items-center gap-2">
                    <select
                        name="procurement_request_id"
                        id="procurement_request_id"
                        data-lines-url-template="{{ url('/procurement-requests/__ID__/purchase-order-lines') }}"
                        class="admin-filter-control min-w-[14rem] flex-1 @error('procurement_request_id') border-red-500 @enderror"
                    >
                        <option value="">— Not linked —</option>
                        @foreach (($procurementRequestOptions ?? []) as $option)
                            <option value="{{ $option['id'] }}" @selected(old('procurement_request_id', $po?->procurement_request_id) == $option['id'])>
                                {{ $option['label'] }}
                            </option>
                        @endforeach
                    </select>
                    <button type="button" id="po-import-pr-lines"
                            class="rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50 disabled:cursor-not-allowed disabled:opacity-50"
                            disabled>
                        Import lines…
                    </button>
                </div>
                <p class="mt-1 text-xs text-slate-500">Choose which P.R. lines to add — not all lines are imported automatically.</p>
                @error('procurement_request_id')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
        </div>

        @include('procurement.purchase-orders._pr-context', [
            'purchaseOrder' => $po,
            'prContext' => $prContext ?? null,
            'scopeTypeKeys' => $scopeTypeKeys ?? [],
        ])
    </section>

    @include('procurement._our-company', [
        'document' => $po,
        'variant' => 'admin-form',
        'poCompany' => $poCompany,
    ])

    @include('procurement.purchase-orders._vendor-section', [
        'po' => $po,
        'selectedVendor' => $selectedVendor ?? null,
    ])

    <section class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
        <h2 class="border-b border-slate-100 pb-3 text-base font-semibold text-slate-900">Delivery</h2>
        <div class="mt-4 grid gap-4 md:grid-cols-2">
            <div>
                <label for="delivery_contact_name" class="block text-xs font-medium uppercase tracking-wide text-slate-500">Contact person</label>
                <input type="text" name="delivery_contact_name" id="delivery_contact_name"
                       value="{{ old('delivery_contact_name', $po?->delivery_contact_name ?? '') }}"
                       class="admin-filter-control @error('delivery_contact_name') border-red-500 @enderror">
            </div>
            <div>
                <label for="delivery_contact_phone" class="block text-xs font-medium uppercase tracking-wide text-slate-500">Phone</label>
                <input type="text" name="delivery_contact_phone" id="delivery_contact_phone"
                       value="{{ old('delivery_contact_phone', $po?->delivery_contact_phone ?? '') }}"
                       class="admin-filter-control @error('delivery_contact_phone') border-red-500 @enderror">
            </div>
            <div>
                <label for="delivery_contact_email" class="block text-xs font-medium uppercase tracking-wide text-slate-500">Email</label>
                <input type="email" name="delivery_contact_email" id="delivery_contact_email"
                       value="{{ old('delivery_contact_email', $po?->delivery_contact_email ?? '') }}"
                       class="admin-filter-control @error('delivery_contact_email') border-red-500 @enderror">
            </div>
            <div class="md:col-span-2">
                <label for="delivery_location" class="block text-xs font-medium uppercase tracking-wide text-slate-500">Delivery location</label>
                <input type="text" name="delivery_location" id="delivery_location"
                       value="{{ old('delivery_location', $po?->delivery_location ?? '') }}"
                       class="admin-filter-control @error('delivery_location') border-red-500 @enderror">
            </div>
        </div>
    </section>

    @include('procurement.purchase-orders._line-items', [
        'lineItems' => $lineItems,
        'po' => $po,
    ])

    <section class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
        <h2 class="border-b border-slate-100 pb-3 text-base font-semibold text-slate-900">Order terms</h2>
        <div class="mt-4 grid gap-4 md:grid-cols-2">
            <div>
                <label for="handover_at" class="block text-xs font-medium uppercase tracking-wide text-slate-500">Handover date (maintenance from)</label>
                <input type="date" name="handover_at" id="handover_at"
                       value="{{ old('handover_at', $po?->handover_at?->format('Y-m-d') ?? '') }}"
                       class="admin-filter-control po-order-term-date @error('handover_at') border-red-500 @enderror">
                @error('handover_at')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="dismantling_at" class="block text-xs font-medium uppercase tracking-wide text-slate-500">Dismantling date (if any)</label>
                <input type="date" name="dismantling_at" id="dismantling_at"
                       value="{{ old('dismantling_at', $po?->dismantling_at?->format('Y-m-d') ?? '') }}"
                       class="admin-filter-control po-order-term-date @error('dismantling_at') border-red-500 @enderror">
                @error('dismantling_at')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
            <div class="md:col-span-2">
                <p class="text-xs text-slate-500">Maintenance period runs from handover date until dismantling date (when set).</p>
            </div>
            @php
                $paymentTermsValue = old('payment_terms', $po?->payment_terms ?? '');
                $notesValue = old('notes', $po?->notes ?? '');
                $paymentTermsRtl = \App\Support\TextDirection::isRtl($paymentTermsValue);
                $notesRtl = \App\Support\TextDirection::isRtl($notesValue);
                $showPaymentTerms = filter_var(old('show_payment_terms', $po?->show_payment_terms ?? true), FILTER_VALIDATE_BOOLEAN);
            @endphp
            <div class="md:col-span-2">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <label for="payment_terms" class="block text-xs font-medium uppercase tracking-wide text-slate-500">Payment terms</label>
                    <label class="inline-flex items-center gap-2 text-sm text-slate-700">
                        <input type="hidden" name="show_payment_terms" value="0">
                        <input type="checkbox" name="show_payment_terms" id="show_payment_terms" value="1" @checked($showPaymentTerms)>
                        Include on purchase order
                    </label>
                </div>
                <p class="mt-0.5 text-xs text-slate-500">Imported from the linked P.R. when you import lines. You can edit below. Uncheck to omit from the printed P.O.</p>
                <textarea name="payment_terms" id="payment_terms" rows="3"
                          class="po-bilingual-text admin-form-textarea @error('payment_terms') border-red-500 @enderror"
                          @if ($paymentTermsRtl) dir="rtl" lang="ar" @endif>{{ $paymentTermsValue }}</textarea>
            </div>

            @include('procurement.purchase-orders._commercial-terms', ['po' => $po])
            <div class="md:col-span-2">
                <label for="notes" class="block text-xs font-medium uppercase tracking-wide text-slate-500">Notes</label>
                <textarea name="notes" id="notes" rows="3"
                          class="po-bilingual-text admin-form-textarea @error('notes') border-red-500 @enderror"
                          @if ($notesRtl) dir="rtl" lang="ar" @endif>{{ $notesValue }}</textarea>
            </div>
        </div>
    </section>

    @include('procurement.purchase-orders._terms', [
        'po' => $po,
        'poTerms' => $poTerms ?? ['general' => [], 'custom_rows' => []],
        'editable' => true,
    ])

    <script type="application/json" id="po-scope-terms-map">@json($scopeTermsMap ?? [])</script>
    <script type="application/json" id="po-default-buyer-company">@json(\App\Enums\Procurement\BuyerCompany::defaults())</script>
    <script type="application/json" id="po-default-company">@json(PrCompany::AsasVentures->toPurchaseOrderApiPayload())</script>

    <section class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
        <h2 class="border-b border-slate-100 pb-3 text-base font-semibold text-slate-900">Signatures</h2>
        @foreach ([
            'vendor' => 'Vendor',
            'procurement' => 'Procurement',
        ] as $key => $label)
            <div class="mt-4 grid gap-4 border-t border-slate-100 pt-4 first:mt-0 first:border-0 first:pt-0 md:grid-cols-3">
                <p class="text-sm font-medium text-slate-800 md:pt-2">{{ $label }}</p>
                <div>
                    <label for="{{ $key }}_signature" class="block text-xs font-medium uppercase tracking-wide text-slate-500">Signature</label>
                    <input type="text" name="{{ $key }}_signature" id="{{ $key }}_signature"
                           value="{{ old($key.'_signature', $po?->{$key.'_signature'} ?? '') }}"
                           class="admin-filter-control">
                </div>
                <div>
                    <label for="{{ $key }}_signed_at" class="block text-xs font-medium uppercase tracking-wide text-slate-500">Date</label>
                    <input type="date" name="{{ $key }}_signed_at" id="{{ $key }}_signed_at"
                           value="{{ old($key.'_signed_at', $po?->{$key.'_signed_at'}?->format('Y-m-d') ?? '') }}"
                           class="admin-filter-control">
                </div>
            </div>
        @endforeach
    </section>

    <details class="rounded-xl border border-slate-200 bg-slate-50 p-4 text-sm">
        <summary class="cursor-pointer font-medium text-slate-700">Internal tracking (optional)</summary>
        <div class="mt-4 grid gap-4 md:grid-cols-2">
            <div>
                <label for="status" class="block text-xs font-medium uppercase tracking-wide text-slate-500">Order status</label>
                <select name="status" id="status" class="admin-filter-control">
                    @foreach (\App\Enums\Procurement\PurchaseOrders\PurchaseOrderStatus::cases() as $case)
                        <option value="{{ $case->value }}" @selected(old('status', $po?->status?->value ?? 'draft') === $case->value)>{{ ucfirst($case->value) }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="payment_status" class="block text-xs font-medium uppercase tracking-wide text-slate-500">Payment status</label>
                <select name="payment_status" id="payment_status" class="admin-filter-control">
                    @foreach (\App\Enums\Procurement\PurchaseOrders\PaymentStatus::cases() as $case)
                        <option value="{{ $case->value }}" @selected(old('payment_status', $po?->payment_status?->value ?? 'unpaid') === $case->value)>{{ ucfirst($case->value) }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </details>
</div>

<div id="po-pr-import-modal" class="fixed inset-0 z-50 hidden" role="dialog" aria-modal="true" aria-labelledby="po-pr-import-title">
    <div class="absolute inset-0 bg-slate-900/50" data-po-pr-import-dismiss></div>
    <div class="relative flex min-h-full items-center justify-center p-4">
        <div class="max-h-[90vh] w-full max-w-3xl overflow-hidden rounded-xl border border-slate-200 bg-white shadow-xl">
            <div class="border-b border-slate-100 px-5 py-4">
                <h3 id="po-pr-import-title" class="text-lg font-semibold text-slate-900">Import lines from P.R.</h3>
                <p id="po-pr-import-subtitle" class="mt-1 text-sm text-slate-500"></p>
            </div>
            <div class="max-h-[50vh] overflow-y-auto px-5 py-3">
                <p id="po-pr-import-empty" class="hidden py-6 text-center text-sm text-slate-500">This P.R. has no line items.</p>
                <table id="po-pr-import-table" class="min-w-full text-left text-sm">
                    <thead class="sticky top-0 bg-white text-xs font-semibold uppercase tracking-wide text-slate-500">
                    <tr>
                        <th class="w-10 py-2 pr-2">
                            <input type="checkbox" id="po-pr-import-select-all" class="rounded border-slate-300" title="Select all">
                        </th>
                        <th class="py-2 pr-3">Line</th>
                        <th class="py-2 pr-3">Project</th>
                        <th class="py-2 pr-3">Scope type</th>
                        <th class="py-2 pr-3">Category</th>
                        <th class="py-2">Description</th>
                    </tr>
                    </thead>
                    <tbody id="po-pr-import-body" class="divide-y divide-slate-100"></tbody>
                </table>
            </div>
            <div class="flex flex-wrap items-center justify-end gap-2 border-t border-slate-100 bg-slate-50 px-5 py-4">
                <button type="button" data-po-pr-import-dismiss
                        class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-100">
                    Cancel
                </button>
                <button type="button" id="po-pr-import-confirm"
                        class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-slate-800 disabled:cursor-not-allowed disabled:opacity-50"
                        disabled>
                    Import selected
                </button>
            </div>
        </div>
    </div>
</div>

@once
@push('scripts')
    @include('procurement.purchase-orders._form-scripts')
@endpush
@endonce
