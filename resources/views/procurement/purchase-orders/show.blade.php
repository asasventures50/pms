@extends('layouts.admin')

@section('title', 'Purchase Order '.$purchaseOrder->po_number)

@section('content')
    @php
        use App\Enums\Procurement\Rfqs\RfqTermsLocale;
    @endphp
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between print:hidden">
        <div>
            <h1 class="text-2xl font-semibold tracking-tight text-slate-900 font-mono">{{ $purchaseOrder->po_number }}</h1>
            <p class="mt-1 text-sm text-slate-600">Requested by {{ $purchaseOrder->creator?->name ?? '—' }}</p>
        </div>
        <div class="flex flex-wrap items-center gap-3">
            @if (auth()->user()->hasPermission('purchase-orders.update'))
                <a href="{{ route('purchase-orders.edit', $purchaseOrder) }}"
                   class="inline-flex items-center justify-center rounded-lg bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-slate-800">Edit</a>
            @endif
            <a href="{{ route('purchase-orders.index') }}"
               class="inline-flex items-center justify-center rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-800 hover:bg-slate-50">Back to list</a>
            <div class="inline-flex flex-wrap items-center gap-2 rounded-lg border border-slate-300 bg-white px-3 py-2">
                <label for="po-print-locale" class="text-sm font-medium text-slate-600">Language</label>
                <select id="po-print-locale"
                        class="rounded-md border border-slate-300 bg-white px-2 py-1.5 text-sm text-slate-800 focus:border-slate-400 focus:outline-none">
                    @foreach (RfqTermsLocale::cases() as $locale)
                        <option value="{{ $locale->value }}" @selected($locale === RfqTermsLocale::En)>{{ $locale->label() }}</option>
                    @endforeach
                </select>
                <label for="po-print-with-terms" class="text-sm font-medium text-slate-600">Terms</label>
                <select id="po-print-with-terms"
                        class="rounded-md border border-slate-300 bg-white px-2 py-1.5 text-sm text-slate-800 focus:border-slate-400 focus:outline-none">
                    <option value="1">With terms &amp; conditions</option>
                    <option value="0">Without terms &amp; conditions</option>
                </select>
                <button type="button" id="po-print-btn"
                        class="inline-flex items-center justify-center rounded-lg bg-slate-900 px-4 py-1.5 text-sm font-medium text-white hover:bg-slate-800">
                    Print
                </button>
            </div>
        </div>
    </div>

    <article class="mx-auto max-w-4xl rounded-xl border border-slate-200 bg-white p-8 shadow-sm print:border-0 print:shadow-none">
        <header class="flex flex-col gap-2 border-b border-slate-200 pb-6 sm:flex-row sm:items-start sm:justify-between">
            <h1 class="text-2xl font-semibold text-slate-900">Purchase Order</h1>
            <p class="text-sm font-medium text-slate-600">Procurement Department</p>
        </header>

        <section class="mt-8">
            <h2 class="text-sm font-semibold uppercase tracking-wide text-slate-700">Order information</h2>
            <dl class="mt-4 grid gap-4 sm:grid-cols-3 text-sm">
                <div>
                    <dt class="text-xs font-medium uppercase tracking-wide text-slate-500">Requested by</dt>
                    <dd class="mt-1 text-slate-900">{{ $purchaseOrder->creator?->name ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-medium uppercase tracking-wide text-slate-500">P.O. number</dt>
                    <dd class="mt-1 font-mono text-slate-900">{{ $purchaseOrder->po_number }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-medium uppercase tracking-wide text-slate-500">Date</dt>
                    <dd class="mt-1 text-slate-900">{{ $purchaseOrder->ordered_at?->format('Y-m-d') ?? '—' }}</dd>
                </div>
            </dl>

            @if ($purchaseOrder->procurementRequest)
                <div class="mt-6 rounded-lg border border-slate-200 bg-slate-50 p-4">
                    <h3 class="text-xs font-semibold uppercase tracking-wide text-slate-600">Linked P.R. — {{ $purchaseOrder->procurementRequest->request_number }}</h3>
                    <dl class="mt-3 grid gap-3 sm:grid-cols-2 lg:grid-cols-6 text-sm">
                        <div>
                            <dt class="text-xs font-medium uppercase tracking-wide text-slate-500">Company</dt>
                            <dd class="mt-0.5 text-slate-900">{{ ($prContext['company'] ?? '') ?: '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium uppercase tracking-wide text-slate-500">Procurement type</dt>
                            <dd class="mt-0.5 text-slate-900">{{ ($prContext['procurement_type'] ?? '') ?: '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium uppercase tracking-wide text-slate-500">Local / International</dt>
                            <dd class="mt-0.5 text-slate-900">{{ ($prContext['geographic_scope'] ?? '') ?: '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium uppercase tracking-wide text-slate-500">Scope type</dt>
                            <dd class="mt-0.5 text-slate-900">{{ ($prContext['scope_type'] ?? '') ?: '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium uppercase tracking-wide text-slate-500">Category</dt>
                            <dd class="mt-0.5 text-slate-900">{{ ($prContext['category'] ?? '') ?: '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium uppercase tracking-wide text-slate-500">Project</dt>
                            <dd class="mt-0.5 text-slate-900">{{ ($prContext['project'] ?? '') ?: '—' }}</dd>
                        </div>
                        @php
                            $displayPackage = trim((string) ($purchaseOrder->package ?? ''));
                            if ($displayPackage === '') {
                                $displayPackage = trim((string) ($prContext['package'] ?? ''));
                            }
                        @endphp
                        @if (filled($displayPackage))
                            <div>
                                <dt class="text-xs font-medium uppercase tracking-wide text-slate-500">Package / الحزمة</dt>
                                <dd class="mt-0.5 text-slate-900">{{ $displayPackage }}</dd>
                            </div>
                        @endif
                    </dl>
                </div>
            @endif
        </section>

        @include('procurement._our-company', [
            'document' => $purchaseOrder,
            'buyerCompany' => $buyerCompany ?? null,
            'variant' => 'admin-show',
        ])

        <section class="mt-8">
            <h2 class="text-sm font-semibold uppercase tracking-wide text-slate-700">Vendor</h2>
            <div class="mt-4">
                @include('procurement.purchase-orders._vendor-section-display', [
                    'purchaseOrder' => $purchaseOrder,
                    'variant' => 'show',
                ])
            </div>
        </section>

        @if ($purchaseOrder->delivery_contact_name || $purchaseOrder->delivery_contact_phone || $purchaseOrder->delivery_contact_email || $purchaseOrder->delivery_location)
            <section class="mt-8">
                <h2 class="text-sm font-semibold uppercase tracking-wide text-slate-700">Delivery</h2>
                <dl class="mt-4 grid gap-3 sm:grid-cols-2 text-sm">
                    <div><dt class="text-xs text-slate-500">Contact person</dt><dd class="text-slate-900">{{ $purchaseOrder->delivery_contact_name ?? '—' }}</dd></div>
                    <div><dt class="text-xs text-slate-500">Phone</dt><dd class="text-slate-900">{{ $purchaseOrder->delivery_contact_phone ?? '—' }}</dd></div>
                    <div><dt class="text-xs text-slate-500">Email</dt><dd class="text-slate-900">{{ $purchaseOrder->delivery_contact_email ?? '—' }}</dd></div>
                    <div class="sm:col-span-2"><dt class="text-xs text-slate-500">Delivery location</dt><dd class="whitespace-pre-wrap text-slate-900 text-start" dir="auto">{{ $purchaseOrder->delivery_location ?? '—' }}</dd></div>
                </dl>
            </section>
        @endif

        @php
            $currency = $purchaseOrder->displayCurrency();
            $linesSubtotal = $purchaseOrder->items->sum('line_total');
        @endphp

        <section class="mt-8 overflow-x-auto">
            @if ($currency)
                <p class="mb-2 text-xs font-medium uppercase tracking-wide text-slate-500">Currency: {{ $currency }}</p>
            @endif
            <table class="min-w-full border border-slate-200 text-sm">
                <thead class="bg-slate-50 text-xs font-semibold uppercase text-slate-600">
                <tr>
                    <th class="border border-slate-200 px-3 py-2 text-left">Item</th>
                    <th class="border border-slate-200 px-3 py-2 text-left">Item or service description</th>
                    <th class="border border-slate-200 px-3 py-2 text-right">Quantity</th>
                    <th class="border border-slate-200 px-3 py-2 text-right">Unit</th>
                    <th class="border border-slate-200 px-3 py-2 text-right">Price per unit{{ $currency ? ' ('.$currency.')' : '' }}</th>
                    <th class="border border-slate-200 px-3 py-2 text-right">Line total{{ $currency ? ' ('.$currency.')' : '' }}</th>
                </tr>
                </thead>
                <tbody>
                @forelse ($purchaseOrder->items as $line)
                    <tr>
                        <td class="border border-slate-200 px-3 py-2 font-mono text-xs">{{ $line->item ?: '—' }}</td>
                        <td class="border border-slate-200 px-3 py-2 whitespace-pre-wrap text-start" dir="auto">{{ $line->description }}</td>
                        <td class="border border-slate-200 px-3 py-2 text-right">{{ number_format($line->quantity, 3) }}</td>
                        <td class="border border-slate-200 px-3 py-2 text-right">{{ $line->unit ?: '—' }}</td>
                        <td class="border border-slate-200 px-3 py-2 text-right">{{ number_format($line->unit_price, 2) }}</td>
                        <td class="border border-slate-200 px-3 py-2 text-right font-mono">{{ number_format($line->line_total, 2) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="border border-slate-200 px-3 py-6 text-center text-slate-500">No line items.</td>
                    </tr>
                @endforelse
                </tbody>
                <tfoot>
                <tr>
                    <td colspan="5" class="border border-slate-200 px-3 py-2 text-right text-slate-700">Subtotal</td>
                    <td class="border border-slate-200 px-3 py-2 text-right font-mono">{{ $purchaseOrder->formatMoneyAmount($linesSubtotal) }}</td>
                </tr>
                <tr>
                    <td colspan="5" class="border border-slate-200 px-3 py-2 text-right text-slate-700">Delivery fee</td>
                    <td class="border border-slate-200 px-3 py-2 text-right font-mono">{{ $purchaseOrder->formatMoneyAmount($purchaseOrder->delivery_fee ?? 0) }}</td>
                </tr>
                <tr>
                    <td colspan="5" class="border border-slate-200 px-3 py-2 text-right text-slate-700">Discount</td>
                    <td class="border border-slate-200 px-3 py-2 text-right font-mono">−{{ $purchaseOrder->formatMoneyAmount($purchaseOrder->discount ?? 0) }}</td>
                </tr>
                <tr class="bg-slate-50 font-semibold">
                    <td colspan="5" class="border border-slate-200 px-3 py-2 text-right">Total price{{ $currency ? ' ('.$currency.')' : '' }}</td>
                    <td class="border border-slate-200 px-3 py-2 text-right font-mono">{{ $purchaseOrder->formatMoneyAmount($purchaseOrder->total_price ?? 0) }}</td>
                </tr>
                </tfoot>
            </table>
        </section>

        <section class="mt-8">
            <h2 class="text-sm font-semibold uppercase tracking-wide text-slate-700">Order terms</h2>
            <dl class="mt-4 space-y-3 text-sm">
                @if ($purchaseOrder->handover_at)
                    <div>
                        <dt class="text-xs text-slate-500">Handover date (maintenance from)</dt>
                        <dd class="text-slate-900">{{ $purchaseOrder->handover_at->format('Y-m-d') }}</dd>
                    </div>
                @endif
                @if ($purchaseOrder->dismantling_at)
                    <div>
                        <dt class="text-xs text-slate-500">Dismantling date</dt>
                        <dd class="text-slate-900">{{ $purchaseOrder->dismantling_at->format('Y-m-d') }}</dd>
                    </div>
                @endif
                @if ($purchaseOrder->handover_at && $purchaseOrder->dismantling_at)
                    <div>
                        <dt class="text-xs text-slate-500">Maintenance period</dt>
                        <dd class="text-slate-900">{{ $purchaseOrder->handover_at->format('Y-m-d') }} — {{ $purchaseOrder->dismantling_at->format('Y-m-d') }}</dd>
                    </div>
                @elseif ($purchaseOrder->handover_at)
                    <div>
                        <dt class="text-xs text-slate-500">Maintenance</dt>
                        <dd class="text-slate-900">From {{ $purchaseOrder->handover_at->format('Y-m-d') }}</dd>
                    </div>
                @endif
                @php
                    $paymentTermsDisplay = trim((string) ($purchaseOrder->payment_terms ?? ''));
                    $notesDisplay = trim((string) ($purchaseOrder->notes ?? ''));
                    $showPaymentTermsOnPo = $purchaseOrder->show_payment_terms && $paymentTermsDisplay !== '';
                @endphp
                @if ($showPaymentTermsOnPo)
                    <div>
                        <dt class="text-xs text-slate-500">Payment terms</dt>
                        <dd class="whitespace-pre-wrap text-slate-900 text-start" dir="auto">{{ $paymentTermsDisplay }}</dd>
                    </div>
                @endif
                @include('procurement.purchase-orders._commercial-terms-display', ['purchaseOrder' => $purchaseOrder])
                <div>
                    <dt class="text-xs text-slate-500">Notes</dt>
                    <dd class="whitespace-pre-wrap text-slate-900 text-start" dir="auto">{{ $notesDisplay !== '' ? $notesDisplay : '—' }}</dd>
                </div>
            </dl>
        </section>

        @include('procurement.purchase-orders._terms', [
            'po' => $purchaseOrder,
            'terms' => $terms ?? [],
            'editable' => false,
        ])

        <section class="mt-8">
            <h2 class="text-sm font-semibold uppercase tracking-wide text-slate-700">Signatures</h2>
            <div class="mt-4 space-y-4 text-sm">
                @foreach ([
                    'Vendor' => ['signature' => $purchaseOrder->vendor_signature, 'date' => $purchaseOrder->vendor_signed_at],
                    'Procurement' => ['signature' => $purchaseOrder->procurement_signature, 'date' => $purchaseOrder->procurement_signed_at],
                ] as $role => $fields)
                    <div class="grid gap-2 border-t border-slate-100 pt-3 first:border-0 first:pt-0 sm:grid-cols-3">
                        <p class="font-medium text-slate-800">{{ $role }}</p>
                        <p><span class="text-xs text-slate-500">Signature:</span> {{ $fields['signature'] ?? '—' }}</p>
                        <p><span class="text-xs text-slate-500">Date:</span> {{ $fields['date']?->format('Y-m-d') ?? '—' }}</p>
                    </div>
                @endforeach
            </div>
        </section>

        <footer class="mt-10 flex flex-col gap-1 border-t border-slate-200 pt-4 text-xs text-slate-500 sm:flex-row sm:justify-between">
            <span>Form PO — Revision: ver.01 / Date: 16-05-2026</span>
            <span>Page 1 of 1</span>
        </footer>
    </article>
@endsection

@push('scripts')
    <script>
        document.getElementById('po-print-btn')?.addEventListener('click', function () {
            const locale = document.getElementById('po-print-locale')?.value ?? 'en';
            const withTerms = document.getElementById('po-print-with-terms')?.value ?? '1';
            const baseUrl = @json(route('purchase-orders.print', $purchaseOrder));
            const url = baseUrl + '?locale=' + encodeURIComponent(locale) + '&with_terms=' + encodeURIComponent(withTerms);

            window.open(url, '_blank', 'noopener');
        });
    </script>
@endpush
