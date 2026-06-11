@php
    $prContext = $prContext ?? \App\Services\Procurement\PurchaseOrders\PurchaseOrderProcurementRequestContext::emptyAggregates();
    $linkedPrNumber = $linkedPrNumber ?? ($purchaseOrder?->procurementRequest?->request_number ?? '');
    $hasContent = ($linkedPrNumber !== '')
        || ($prContext['category'] ?? '') !== ''
        || ($prContext['scope_type'] ?? '') !== ''
        || ($prContext['project'] ?? '') !== ''
        || ($prContext['procurement_type'] ?? '') !== '';
@endphp

<div id="po-pr-context"
     class="mt-4 rounded-lg border border-slate-200 bg-slate-50 p-4 text-sm {{ $hasContent ? '' : 'hidden' }}"
     data-initial-scope-type-keys='@json($scopeTypeKeys ?? [])'>
    <h4 class="text-xs font-semibold uppercase tracking-wide text-slate-600">From linked P.R.</h4>
    <dl class="mt-3 grid gap-3 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5">
        <div>
            <dt class="text-xs font-medium uppercase tracking-wide text-slate-500">P.R. number</dt>
            <dd id="po-pr-context-number" class="mt-0.5 font-mono text-slate-900">{{ $linkedPrNumber ?: '—' }}</dd>
        </div>
        <div>
            <dt class="text-xs font-medium uppercase tracking-wide text-slate-500">Procurement type</dt>
            <dd id="po-pr-context-procurement-type" class="mt-0.5 text-slate-900">{{ $prContext['procurement_type'] ?? '—' }}</dd>
        </div>
        <div>
            <dt class="text-xs font-medium uppercase tracking-wide text-slate-500">Scope type</dt>
            <dd id="po-pr-context-scope-type" class="mt-0.5 text-slate-900">{{ $prContext['scope_type'] ?? '—' }}</dd>
        </div>
        <div>
            <dt class="text-xs font-medium uppercase tracking-wide text-slate-500">Category</dt>
            <dd id="po-pr-context-category" class="mt-0.5 text-slate-900">{{ $prContext['category'] ?? '—' }}</dd>
        </div>
        <div>
            <dt class="text-xs font-medium uppercase tracking-wide text-slate-500">Project</dt>
            <dd id="po-pr-context-project" class="mt-0.5 text-slate-900">{{ $prContext['project'] ?? '—' }}</dd>
        </div>
    </dl>
</div>
