@extends('layouts.public-form')

@section('title', __('vendor_quotation_invite.page_title'))

@section('content')
    @php
        $isRtl = app()->getLocale() === 'ar';
        $quotationItemsByRfqItemId = collect($quotation?->items ?? [])->keyBy('rfq_item_id');
    @endphp

    <div class="mx-auto max-w-3xl">
        <div class="mb-6 text-center sm:text-start">
            <p class="text-sm font-medium uppercase tracking-wide text-[#e65100]">{{ __('vendor_quotation_invite.vendor') }}</p>
            <h1 class="mt-1 text-2xl font-semibold tracking-tight text-slate-900 sm:text-3xl">
                {{ __('vendor_quotation_invite.page_heading') }}
            </h1>
            <p class="mt-2 text-sm text-slate-600">{{ __('vendor_quotation_invite.page_subtitle') }}</p>
        </div>

        @if (session('success'))
            <div class="mb-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-900">
                {{ session('success') }}
            </div>
        @endif

        @if (session('info'))
            <div class="mb-4 rounded-xl border border-sky-200 bg-sky-50 px-4 py-3 text-sm text-sky-900">
                {{ session('info') }}
            </div>
        @endif

        @if ($readOnly)
            <div class="mb-4 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-950">
                @if ($invite->status->value === 'revoked')
                    {{ __('vendor_quotation_invite.revoked_notice') }}
                @else
                    {{ __('vendor_quotation_invite.read_only_notice') }}
                @endif
            </div>
        @endif

        @if ($errors->any())
            <div class="mb-4 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-900">
                <ul class="list-disc space-y-1 {{ $isRtl ? 'pr-5' : 'pl-5' }}">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <section class="mb-6 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
            <dl class="grid gap-4 sm:grid-cols-2">
                <div>
                    <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('vendor_quotation_invite.rfq_number') }}</dt>
                    <dd class="mt-1 font-mono text-base font-semibold text-slate-900">{{ $rfq->rfq_number }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('vendor_quotation_invite.vendor') }}</dt>
                    <dd class="mt-1 text-base font-medium text-slate-900">{{ $vendor?->name ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('vendor_quotation_invite.deadline') }}</dt>
                    <dd class="mt-1 text-sm text-slate-800">
                        @if ($rfq->submission_deadline_at)
                            {{ $rfq->submission_deadline_at->timezone($rfq->submission_timezone ?? config('app.timezone'))->format('Y-m-d H:i') }}
                        @else
                            {{ $rfq->submission_deadline?->format('Y-m-d') ?? '—' }}
                        @endif
                    </dd>
                </div>
                <div>
                    <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('vendor_quotation_invite.issue_date') }}</dt>
                    <dd class="mt-1 text-sm text-slate-800">{{ $rfq->issue_date?->format('Y-m-d') ?? '—' }}</dd>
                </div>
                @if ($rfq->quotation_validity)
                    <div class="sm:col-span-2">
                        <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('vendor_quotation_invite.validity') }}</dt>
                        <dd class="mt-1 text-sm text-slate-800">{{ $rfq->quotation_validity }}</dd>
                    </div>
                @endif
                @if (! empty($buyerCompany['name']))
                    <div class="sm:col-span-2">
                        <dt class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('vendor_quotation_invite.buyer') }}</dt>
                        <dd class="mt-1 text-sm text-slate-800">{{ $buyerCompany['name'] }}</dd>
                    </div>
                @endif
            </dl>
        </section>

        @if ($terms !== [])
            <section class="mb-6 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
                <h2 class="text-base font-semibold text-slate-900">{{ __('vendor_quotation_invite.terms_heading') }}</h2>
                <ol class="mt-4 list-decimal space-y-3 text-sm leading-relaxed text-slate-700 {{ $isRtl ? 'pr-5' : 'pl-5' }}">
                    @foreach ($terms as $term)
                        <li class="whitespace-pre-wrap text-start" dir="auto">{{ $term }}</li>
                    @endforeach
                </ol>
            </section>
        @endif

        @if ($readOnly)
            <section class="mb-6 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
                <div class="mb-4 flex flex-wrap items-center gap-2">
                    <h2 class="text-base font-semibold text-slate-900">{{ __('vendor_quotation_invite.your_prices') }}</h2>
                    @if ($invite->isSubmitted())
                        <span class="rounded-full bg-emerald-100 px-2.5 py-0.5 text-xs font-bold uppercase text-emerald-800">
                            {{ __('vendor_quotation_invite.submitted_badge') }}
                        </span>
                    @endif
                </div>

                <div class="space-y-4">
                    @foreach ($rfq->items as $line)
                        @php
                            $quoted = $quotationItemsByRfqItemId->get($line->id);
                        @endphp
                        <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                            <div class="flex flex-wrap items-start justify-between gap-2">
                                <div>
                                    <p class="font-mono text-xs text-slate-500">{{ $line->item ?: '#' }}</p>
                                    <p class="mt-1 whitespace-pre-wrap text-sm font-medium text-slate-900 text-start" dir="auto">{{ $line->description }}</p>
                                    <p class="mt-1 text-xs text-slate-600">
                                        {{ __('vendor_quotation_invite.quantity') }}:
                                        <span class="font-semibold">{{ number_format((float) $line->quantity, 3) }}</span>
                                        {{ $line->unit }}
                                    </p>
                                </div>
                                <div class="text-end">
                                    <p class="text-xs text-slate-500">{{ __('vendor_quotation_invite.unit_price') }}</p>
                                    <p class="font-mono text-base font-semibold text-slate-900">
                                        {{ $quoted?->unit_price !== null ? number_format((float) $quoted->unit_price, 2) : '—' }}
                                    </p>
                                    <p class="mt-1 text-xs text-slate-500">{{ __('vendor_quotation_invite.line_total') }}</p>
                                    <p class="font-mono text-sm text-slate-800">
                                        {{ $quoted ? number_format((float) $quoted->total_price, 2) : '—' }}
                                    </p>
                                </div>
                            </div>
                            @if ($quoted?->remarks)
                                <p class="mt-3 text-sm text-slate-600 whitespace-pre-wrap text-start" dir="auto">{{ $quoted->remarks }}</p>
                            @endif
                        </div>
                    @endforeach
                </div>

                @if ($quotation)
                    <div class="mt-5 flex items-center justify-between border-t border-slate-200 pt-4">
                        <span class="text-sm font-semibold text-slate-700">{{ __('vendor_quotation_invite.grand_total') }}</span>
                        <span class="font-mono text-lg font-bold text-slate-900">{{ number_format((float) $quotation->grand_total, 2) }}</span>
                    </div>
                    @if ($quotation->notes)
                        <p class="mt-4 whitespace-pre-wrap text-sm text-slate-700 text-start" dir="auto">{{ $quotation->notes }}</p>
                    @endif
                @endif
            </section>
        @else
            <form method="POST" action="{{ route('vendor-quotation-invite.store', $invite) }}" enctype="multipart/form-data" class="space-y-6">
                @csrf

                <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
                    <h2 class="text-base font-semibold text-slate-900">{{ __('vendor_quotation_invite.your_prices') }}</h2>
                    <p class="mt-1 text-sm text-slate-600">{{ __('vendor_quotation_invite.items_heading') }}</p>

                    <div class="mt-4 space-y-4" id="vendor-quote-lines">
                        @foreach ($rfq->items as $index => $line)
                            @php
                                $oldUnit = old('items.'.$index.'.unit_price');
                                $oldRemarks = old('items.'.$index.'.remarks');
                                $qty = (float) $line->quantity;
                            @endphp
                            <div class="rounded-xl border border-slate-200 bg-slate-50 p-4" data-qty="{{ $qty }}">
                                <input type="hidden" name="items[{{ $index }}][rfq_item_id]" value="{{ $line->id }}">
                                <div class="mb-3">
                                    <p class="font-mono text-xs text-slate-500">{{ $line->item ?: '#' }}</p>
                                    <p class="mt-1 whitespace-pre-wrap text-sm font-medium text-slate-900 text-start" dir="auto">{{ $line->description }}</p>
                                    <p class="mt-1 text-xs text-slate-600">
                                        {{ __('vendor_quotation_invite.quantity') }}:
                                        <span class="font-semibold">{{ number_format($qty, 3) }}</span>
                                        {{ $line->unit }}
                                    </p>
                                </div>
                                <div class="grid gap-3 sm:grid-cols-2">
                                    <label class="block">
                                        <span class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-600">
                                            {{ __('vendor_quotation_invite.unit_price') }}
                                        </span>
                                        <input type="number"
                                               name="items[{{ $index }}][unit_price]"
                                               value="{{ $oldUnit }}"
                                               min="0"
                                               step="0.01"
                                               inputmode="decimal"
                                               class="js-unit-price w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-base text-slate-900 shadow-sm focus:border-[#e65100] focus:outline-none focus:ring-2 focus:ring-[#e65100]/30"
                                               placeholder="0.00">
                                    </label>
                                    <div>
                                        <span class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-600">
                                            {{ __('vendor_quotation_invite.line_total') }}
                                        </span>
                                        <p class="js-line-total rounded-lg border border-transparent bg-white px-3 py-2.5 font-mono text-base font-semibold text-slate-900">
                                            0.00
                                        </p>
                                    </div>
                                </div>
                                <label class="mt-3 block">
                                    <span class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-600">
                                        {{ __('vendor_quotation_invite.line_notes') }}
                                    </span>
                                    <input type="text"
                                           name="items[{{ $index }}][remarks]"
                                           value="{{ $oldRemarks }}"
                                           maxlength="2000"
                                           class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-900 shadow-sm focus:border-[#e65100] focus:outline-none focus:ring-2 focus:ring-[#e65100]/30">
                                </label>
                            </div>
                        @endforeach
                    </div>

                    <div class="mt-5 flex items-center justify-between border-t border-slate-200 pt-4">
                        <span class="text-sm font-semibold text-slate-700">{{ __('vendor_quotation_invite.grand_total') }}</span>
                        <span id="vendor-quote-grand-total" class="font-mono text-lg font-bold text-slate-900">0.00</span>
                    </div>
                </section>

                <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
                    <h2 class="text-base font-semibold text-slate-900">{{ __('vendor_quotation_invite.contact_heading') }}</h2>
                    <div class="mt-4 grid gap-4 sm:grid-cols-2">
                        <label class="block sm:col-span-2">
                            <span class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-600">
                                {{ __('vendor_quotation_invite.rep_name') }} <span class="text-rose-600">*</span>
                            </span>
                            <input type="text"
                                   name="vendor_rep_name"
                                   value="{{ old('vendor_rep_name') }}"
                                   required
                                   maxlength="255"
                                   class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-base text-slate-900 shadow-sm focus:border-[#e65100] focus:outline-none focus:ring-2 focus:ring-[#e65100]/30">
                        </label>
                        <label class="block">
                            <span class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-600">
                                {{ __('vendor_quotation_invite.rep_email') }}
                            </span>
                            <input type="email"
                                   name="vendor_rep_email"
                                   value="{{ old('vendor_rep_email', $vendor?->email) }}"
                                   maxlength="255"
                                   class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-base text-slate-900 shadow-sm focus:border-[#e65100] focus:outline-none focus:ring-2 focus:ring-[#e65100]/30">
                        </label>
                        <label class="block">
                            <span class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-600">
                                {{ __('vendor_quotation_invite.rep_phone') }}
                            </span>
                            <input type="text"
                                   name="vendor_rep_phone"
                                   value="{{ old('vendor_rep_phone', $vendor?->phone) }}"
                                   maxlength="50"
                                   class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-base text-slate-900 shadow-sm focus:border-[#e65100] focus:outline-none focus:ring-2 focus:ring-[#e65100]/30">
                        </label>
                        <label class="block sm:col-span-2">
                            <span class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-600">
                                {{ __('vendor_quotation_invite.notes') }}
                            </span>
                            <textarea name="notes"
                                      rows="3"
                                      maxlength="5000"
                                      class="w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-sm text-slate-900 shadow-sm focus:border-[#e65100] focus:outline-none focus:ring-2 focus:ring-[#e65100]/30">{{ old('notes') }}</textarea>
                        </label>
                        <label class="block sm:col-span-2">
                            <span class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-600">
                                {{ __('vendor_quotation_invite.attachment') }}
                            </span>
                            <input type="file"
                                   name="attachment"
                                   accept=".pdf,.jpg,.jpeg,.png,.webp,.doc,.docx,.xls,.xlsx"
                                   class="block w-full text-sm text-slate-700 file:me-3 file:rounded-lg file:border-0 file:bg-slate-900 file:px-3 file:py-2 file:text-sm file:font-medium file:text-white">
                            <span class="mt-1 block text-xs text-slate-500">{{ __('vendor_quotation_invite.attachment_hint') }}</span>
                        </label>
                    </div>
                </section>

                <button type="submit"
                        class="inline-flex w-full items-center justify-center rounded-xl bg-[#e65100] px-5 py-3.5 text-base font-semibold text-white shadow-sm transition hover:bg-[#d84300] focus:outline-none focus:ring-2 focus:ring-[#e65100]/40 focus:ring-offset-2 sm:w-auto">
                    {{ __('vendor_quotation_invite.submit') }}
                </button>
            </form>
        @endif
    </div>
@endsection

@push('scripts')
    @unless ($readOnly)
        <script>
            (function () {
                const root = document.getElementById('vendor-quote-lines');
                const grandEl = document.getElementById('vendor-quote-grand-total');
                if (!root || !grandEl) return;

                function formatMoney(value) {
                    return (Math.round(value * 100) / 100).toFixed(2);
                }

                function recalc() {
                    let grand = 0;
                    root.querySelectorAll('[data-qty]').forEach(function (card) {
                        const qty = parseFloat(card.getAttribute('data-qty') || '0') || 0;
                        const input = card.querySelector('.js-unit-price');
                        const totalEl = card.querySelector('.js-line-total');
                        const unit = parseFloat(input && input.value ? input.value : '0') || 0;
                        const line = qty * unit;
                        grand += line;
                        if (totalEl) totalEl.textContent = formatMoney(line);
                    });
                    grandEl.textContent = formatMoney(grand);
                }

                root.addEventListener('input', function (event) {
                    if (event.target && event.target.classList.contains('js-unit-price')) {
                        recalc();
                    }
                });

                recalc();
            })();
        </script>
    @endunless
@endpush
