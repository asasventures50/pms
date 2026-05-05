@extends('layouts.admin')

@section('title', 'Purchase Order')

@section('content')
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <h1 class="text-2xl font-semibold tracking-tight text-slate-900 font-mono">{{ $purchaseOrder->po_number }}</h1>
            <p class="mt-1 text-sm text-slate-600">{{ $purchaseOrder->title }}</p>
        </div>
        <div class="flex flex-wrap gap-3">
            <a href="{{ route('purchase-orders.edit', $purchaseOrder) }}"
               class="inline-flex items-center justify-center rounded-lg bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-slate-800">Edit</a>
            <a href="{{ route('purchase-orders.index') }}"
               class="inline-flex items-center justify-center rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-800 hover:bg-slate-50">Back to list</a>
        </div>
    </div>

    <div class="space-y-6">
        <section class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="border-b border-slate-100 pb-2 text-base font-semibold text-slate-900">Details</h2>
            <dl class="mt-4 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                <div>
                    <dt class="text-xs font-medium uppercase tracking-wide text-slate-500">PO Number</dt>
                    <dd class="mt-1 font-mono text-sm text-slate-900">{{ $purchaseOrder->po_number }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-medium uppercase tracking-wide text-slate-500">Vendor</dt>
                    <dd class="mt-1 text-sm text-slate-900">{{ $purchaseOrder->vendor?->name ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-medium uppercase tracking-wide text-slate-500">Total Price</dt>
                    <dd class="mt-1 text-sm text-slate-900">{{ $purchaseOrder->total_price ? number_format($purchaseOrder->total_price, 2) : '—' }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-medium uppercase tracking-wide text-slate-500">Order Status</dt>
                    <dd class="mt-1">
                        <span class="inline-flex rounded-full bg-slate-100 px-2 py-0.5 text-xs font-medium text-slate-800">{{ ucfirst($purchaseOrder->status->value) }}</span>
                    </dd>
                </div>
                <div>
                    <dt class="text-xs font-medium uppercase tracking-wide text-slate-500">Payment Status</dt>
                    <dd class="mt-1">
                        <span class="inline-flex rounded-full bg-slate-100 px-2 py-0.5 text-xs font-medium text-slate-800">{{ ucfirst($purchaseOrder->payment_status->value) }}</span>
                    </dd>
                </div>
                <div>
                    <dt class="text-xs font-medium uppercase tracking-wide text-slate-500">Ordered At</dt>
                    <dd class="mt-1 text-sm text-slate-900">{{ $purchaseOrder->ordered_at?->format('Y-m-d') ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-medium uppercase tracking-wide text-slate-500">Delivered At</dt>
                    <dd class="mt-1 text-sm text-slate-900">{{ $purchaseOrder->delivered_at?->format('Y-m-d') ?? '—' }}</dd>
                </div>
                @if ($purchaseOrder->description)
                    <div class="sm:col-span-2 lg:col-span-3">
                        <dt class="text-xs font-medium uppercase tracking-wide text-slate-500">Description</dt>
                        <dd class="mt-1 whitespace-pre-wrap text-sm text-slate-900">{{ $purchaseOrder->description }}</dd>
                    </div>
                @endif
                @if ($purchaseOrder->notes)
                    <div class="sm:col-span-2 lg:col-span-3">
                        <dt class="text-xs font-medium uppercase tracking-wide text-slate-500">Notes</dt>
                        <dd class="mt-1 whitespace-pre-wrap text-sm text-slate-900">{{ $purchaseOrder->notes }}</dd>
                    </div>
                @endif
            </dl>
        </section>
    </div>
@endsection
