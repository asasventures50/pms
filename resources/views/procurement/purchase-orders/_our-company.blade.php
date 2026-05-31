@php
    use App\Enums\Procurement\PurchaseOrders\BuyerCompany;

    $buyerCompany = $buyerCompany ?? BuyerCompany::forDisplay($purchaseOrder ?? null);
@endphp

<section @class([
    'rounded-xl border border-slate-200 bg-white p-6 shadow-sm' => $variant === 'form',
    'mt-8' => $variant === 'show',
])>
    <h2 @class([
        'border-b border-slate-100 pb-3 text-base font-semibold text-slate-900' => $variant === 'form',
        'text-sm font-semibold uppercase tracking-wide text-slate-700' => $variant === 'show',
    ])>Our company</h2>

    @if (BuyerCompany::hasConfiguredDefaults())
        <dl @class([
            'mt-4 grid gap-4 md:grid-cols-2 text-sm' => $variant === 'form',
            'mt-4 grid gap-3 sm:grid-cols-2 text-sm' => $variant === 'show',
        ])>
            <div class="md:col-span-2 sm:col-span-2">
                <dt class="text-xs font-medium uppercase tracking-wide text-slate-500">Company name</dt>
                <dd @class(['mt-1 text-slate-900' => $variant === 'form', 'text-slate-900' => $variant === 'show'])>{{ $buyerCompany['name'] ?? '—' }}</dd>
            </div>
            <div>
                <dt class="text-xs font-medium uppercase tracking-wide text-slate-500">Phone</dt>
                <dd @class(['mt-1 text-slate-900' => $variant === 'form', 'text-slate-900' => $variant === 'show'])>{{ $buyerCompany['phone'] ?? '—' }}</dd>
            </div>
            <div>
                <dt class="text-xs font-medium uppercase tracking-wide text-slate-500">Email</dt>
                <dd @class(['mt-1 text-slate-900' => $variant === 'form', 'text-slate-900' => $variant === 'show'])>{{ $buyerCompany['email'] ?? '—' }}</dd>
            </div>
            <div class="md:col-span-2 sm:col-span-2">
                <dt class="text-xs font-medium uppercase tracking-wide text-slate-500">Address</dt>
                <dd @class([
                    'mt-1 whitespace-pre-wrap text-slate-900' => $variant === 'form',
                    'whitespace-pre-wrap text-slate-900' => $variant === 'show',
                ])>{{ $buyerCompany['address'] ?? '—' }}</dd>
            </div>
        </dl>
        @if ($variant === 'form')
            <p class="mt-3 text-xs text-slate-500">Filled automatically from <code class="rounded bg-slate-100 px-1">BuyerCompany</code>. Edit constants in <code class="rounded bg-slate-100 px-1">app/Enums/Procurement/PurchaseOrders/BuyerCompany.php</code>.</p>
        @endif
    @else
        <p class="mt-4 text-sm text-amber-800">Set <code class="rounded bg-amber-50 px-1">NAME</code>, <code class="rounded bg-amber-50 px-1">ADDRESS</code>, <code class="rounded bg-amber-50 px-1">PHONE</code>, and <code class="rounded bg-amber-50 px-1">EMAIL</code> in <code class="rounded bg-amber-50 px-1">BuyerCompany.php</code>.</p>
    @endif
</section>
