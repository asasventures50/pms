@php
    use App\Enums\Procurement\BuyerCompany;
    use App\Enums\Procurement\PrCompany;

    $buyerCompany = $buyerCompany ?? BuyerCompany::forDisplay($document ?? null);
    $variant = $variant ?? 'admin-show';
    $poCompany = $poCompany ?? PrCompany::AsasVentures;
@endphp

@if ($variant === 'po-print')
    <section class="mt-6">
        <h2 class="text-sm font-bold uppercase tracking-wide">Our company</h2>
        @if (BuyerCompany::hasConfiguredDefaults())
            <dl class="mt-3 grid gap-2 text-sm sm:grid-cols-2">
                <div class="flex flex-col gap-0.5 border-b border-slate-900 pb-1 sm:col-span-2 sm:flex-row sm:gap-3">
                    <dt class="shrink-0 font-medium">Company name</dt>
                    <dd>{{ $buyerCompany['name'] ?? '—' }}</dd>
                </div>
                <div class="flex flex-col gap-0.5 border-b border-slate-900 pb-1 sm:flex-row sm:gap-3">
                    <dt class="shrink-0 font-medium">Phone</dt>
                    <dd>{{ $buyerCompany['phone'] ?? '—' }}</dd>
                </div>
                <div class="flex flex-col gap-0.5 border-b border-slate-900 pb-1 sm:flex-row sm:gap-3">
                    <dt class="shrink-0 font-medium">Email</dt>
                    <dd>{{ $buyerCompany['email'] ?? '—' }}</dd>
                </div>
                <div class="flex flex-col gap-0.5 border-b border-slate-900 pb-1 sm:col-span-2 sm:flex-row sm:gap-3">
                    <dt class="shrink-0 font-medium">Address</dt>
                    <dd class="whitespace-pre-wrap">{{ $buyerCompany['address'] ?? '—' }}</dd>
                </div>
                <div class="flex flex-col gap-0.5 border-b border-slate-900 pb-1 sm:flex-row sm:gap-3">
                    <dt class="shrink-0 font-medium">Fax</dt>
                    <dd>{{ $buyerCompany['fax'] ?? '—' }}</dd>
                </div>
            </dl>
        @else
            <p class="mt-2 text-sm text-amber-800">Configure buyer company in BuyerCompany.php.</p>
        @endif
    </section>
@elseif ($variant === 'rfq-doc')
    <section class="mt-6">
        <h3 class="text-sm font-bold uppercase tracking-wide text-slate-900">Our company</h3>
        @if (BuyerCompany::hasConfiguredDefaults())
            <dl class="mt-3 grid gap-3 text-sm sm:grid-cols-2">
                <div class="flex flex-col gap-1 border-b border-slate-900 pb-1 sm:flex-row sm:gap-3 sm:col-span-2">
                    <dt class="shrink-0 font-medium">Company name</dt>
                    <dd>{{ $buyerCompany['name'] ?? '—' }}</dd>
                </div>
                <div class="flex flex-col gap-1 border-b border-slate-900 pb-1 sm:flex-row sm:gap-3">
                    <dt class="shrink-0 font-medium">Phone</dt>
                    <dd>{{ $buyerCompany['phone'] ?? '—' }}</dd>
                </div>
                <div class="flex flex-col gap-1 border-b border-slate-900 pb-1 sm:flex-row sm:gap-3">
                    <dt class="shrink-0 font-medium">Email</dt>
                    <dd>{{ $buyerCompany['email'] ?? '—' }}</dd>
                </div>
                <div class="flex flex-col gap-1 border-b border-slate-900 pb-1 sm:flex-row sm:gap-3 sm:col-span-2">
                    <dt class="shrink-0 font-medium">Address</dt>
                    <dd class="whitespace-pre-wrap">{{ $buyerCompany['address'] ?? '—' }}</dd>
                </div>
                <div class="flex flex-col gap-1 border-b border-slate-900 pb-1 sm:flex-row sm:gap-3">
                    <dt class="shrink-0 font-medium">Fax</dt>
                    <dd>{{ $buyerCompany['fax'] ?? '—' }}</dd>
                </div>
            </dl>
        @else
            <p class="mt-2 text-sm text-amber-800">Configure buyer company in <code class="rounded bg-amber-50 px-1">BuyerCompany.php</code>.</p>
        @endif
    </section>
@else
    <section @class([
        'rounded-xl border border-slate-200 bg-white p-6 shadow-sm' => $variant === 'admin-form',
        'mt-8' => $variant === 'admin-show',
    ])>
        <h2 @class([
            'border-b border-slate-100 pb-3 text-base font-semibold text-slate-900' => $variant === 'admin-form',
            'text-sm font-semibold uppercase tracking-wide text-slate-700' => $variant === 'admin-show',
        ])>Our company</h2>

        @if ($variant === 'admin-form')
            <div class="mt-4 flex flex-wrap items-center gap-4 rounded-lg border border-slate-200 bg-slate-50 p-4">
                <div id="po-company-logo-preview" class="flex min-h-[4rem] min-w-[6rem] items-center justify-center">
                    <img src="{{ $poCompany->logoUrl() }}" alt="{{ $poCompany->label() }}"
                         class="max-h-16 max-w-full object-contain"
                         data-po-company-logo
                         @unless ($poCompany->logoExists())
                             onerror="this.style.display='none';this.nextElementSibling.style.display='block';"
                         @endunless>
                    <div class="text-xs font-bold leading-tight text-slate-800" data-po-company-logo-fallback
                         @if ($poCompany->logoExists()) style="display:none;" @endif>
                        {!! $poCompany->logoFallbackHtml() !!}
                    </div>
                </div>
                <div class="min-w-0 flex-1 text-sm">
                    <p class="text-xs font-medium uppercase tracking-wide text-slate-500">Company</p>
                    <p id="po-company-label" class="mt-0.5 font-medium text-slate-900" data-po-company-label>{{ $poCompany->label() }}</p>
                </div>
            </div>
        @endif

        @if (BuyerCompany::hasConfiguredDefaults())
            <dl id="po-our-company-fields" @class([
                'mt-4 grid gap-4 md:grid-cols-2 text-sm' => $variant === 'admin-form',
                'mt-4 grid gap-3 sm:grid-cols-2 text-sm' => $variant === 'admin-show',
            ])>
                <div class="md:col-span-2 sm:col-span-2">
                    <dt class="text-xs font-medium uppercase tracking-wide text-slate-500">Company name</dt>
                    <dd @class(['mt-1 text-slate-900' => $variant === 'admin-form', 'text-slate-900' => $variant === 'admin-show']) data-po-company-name>{{ $buyerCompany['name'] ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-medium uppercase tracking-wide text-slate-500">Phone</dt>
                    <dd @class(['mt-1 text-slate-900' => $variant === 'admin-form', 'text-slate-900' => $variant === 'admin-show']) data-po-company-phone>{{ $buyerCompany['phone'] ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-medium uppercase tracking-wide text-slate-500">Email</dt>
                    <dd @class(['mt-1 text-slate-900' => $variant === 'admin-form', 'text-slate-900' => $variant === 'admin-show']) data-po-company-email>{{ $buyerCompany['email'] ?? '—' }}</dd>
                </div>
                <div class="md:col-span-2 sm:col-span-2">
                    <dt class="text-xs font-medium uppercase tracking-wide text-slate-500">Address</dt>
                    <dd @class([
                        'mt-1 whitespace-pre-wrap text-slate-900' => $variant === 'admin-form',
                        'whitespace-pre-wrap text-slate-900' => $variant === 'admin-show',
                    ]) data-po-company-address>{{ $buyerCompany['address'] ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-medium uppercase tracking-wide text-slate-500">Fax</dt>
                    <dd @class(['mt-1 text-slate-900' => $variant === 'admin-form', 'text-slate-900' => $variant === 'admin-show']) data-po-company-fax>{{ $buyerCompany['fax'] ?? '—' }}</dd>
                </div>
            </dl>
            @if ($variant === 'admin-form')
                <p id="po-our-company-source" class="mt-3 text-xs text-slate-500">
                    Filled from the linked P.R. company (<code class="rounded bg-slate-100 px-1">PrCompany</code>) when you import lines, or from <code class="rounded bg-slate-100 px-1">BuyerCompany</code> when no P.R. is linked.
                </p>
            @endif
        @else
            <p class="mt-4 text-sm text-amber-800">Set company constants in <code class="rounded bg-amber-50 px-1">app/Enums/Procurement/BuyerCompany.php</code>.</p>
        @endif
    </section>
@endif
