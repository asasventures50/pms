@php
    use App\Enums\Procurement\BuyerCompany;

    $buyerCompany = $buyerCompany ?? BuyerCompany::forDisplay($document ?? null);
    $variant = $variant ?? 'admin-show';
@endphp

@if ($variant === 'rfq-doc')
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

        @if (BuyerCompany::hasConfiguredDefaults())
            <dl @class([
                'mt-4 grid gap-4 md:grid-cols-2 text-sm' => $variant === 'admin-form',
                'mt-4 grid gap-3 sm:grid-cols-2 text-sm' => $variant === 'admin-show',
            ])>
                <div class="md:col-span-2 sm:col-span-2">
                    <dt class="text-xs font-medium uppercase tracking-wide text-slate-500">Company name</dt>
                    <dd @class(['mt-1 text-slate-900' => $variant === 'admin-form', 'text-slate-900' => $variant === 'admin-show'])>{{ $buyerCompany['name'] ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-medium uppercase tracking-wide text-slate-500">Phone</dt>
                    <dd @class(['mt-1 text-slate-900' => $variant === 'admin-form', 'text-slate-900' => $variant === 'admin-show'])>{{ $buyerCompany['phone'] ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-medium uppercase tracking-wide text-slate-500">Email</dt>
                    <dd @class(['mt-1 text-slate-900' => $variant === 'admin-form', 'text-slate-900' => $variant === 'admin-show'])>{{ $buyerCompany['email'] ?? '—' }}</dd>
                </div>
                <div class="md:col-span-2 sm:col-span-2">
                    <dt class="text-xs font-medium uppercase tracking-wide text-slate-500">Address</dt>
                    <dd @class([
                        'mt-1 whitespace-pre-wrap text-slate-900' => $variant === 'admin-form',
                        'whitespace-pre-wrap text-slate-900' => $variant === 'admin-show',
                    ])>{{ $buyerCompany['address'] ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-medium uppercase tracking-wide text-slate-500">Fax</dt>
                    <dd @class(['mt-1 text-slate-900' => $variant === 'admin-form', 'text-slate-900' => $variant === 'admin-show'])>{{ $buyerCompany['fax'] ?? '—' }}</dd>
                </div>
            </dl>
            @if ($variant === 'admin-form')
                <p class="mt-3 text-xs text-slate-500">Filled automatically from <code class="rounded bg-slate-100 px-1">BuyerCompany</code> (<code class="rounded bg-slate-100 px-1">app/Enums/Procurement/BuyerCompany.php</code>).</p>
            @endif
        @else
            <p class="mt-4 text-sm text-amber-800">Set company constants in <code class="rounded bg-amber-50 px-1">app/Enums/Procurement/BuyerCompany.php</code>.</p>
        @endif
    </section>
@endif
