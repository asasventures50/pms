@extends('layouts.admin')

@section('title', 'Purchase Orders')

@section('content')
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-semibold tracking-tight text-slate-900">Purchase Orders</h1>
            <p class="mt-1 text-sm text-slate-600">Track and manage procurement purchase orders.</p>
        </div>
        <a href="{{ route('purchase-orders.create') }}"
           class="inline-flex items-center justify-center rounded-lg bg-slate-900 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-slate-800">
            Add Purchase Order
        </a>
    </div>

    <form method="get" action="{{ route('purchase-orders.index') }}" class="mb-6 space-y-4 rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
        <div class="grid gap-4 md:grid-cols-3">
            <div class="md:col-span-1">
                <label for="q" class="block text-xs font-medium uppercase tracking-wide text-slate-500">Search</label>
                <input type="search" name="q" id="q" value="{{ request('q') }}"
                       placeholder="Title or PO number"
                       class="admin-filter-control">
            </div>
            <div>
                <label for="status" class="block text-xs font-medium uppercase tracking-wide text-slate-500">Order Status</label>
                <select name="status" id="status" class="admin-filter-control">
                    <option value="">All</option>
                    @foreach ($statuses as $s)
                        <option value="{{ $s->value }}" @selected(request('status') === $s->value)>{{ ucfirst($s->value) }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="payment_status" class="block text-xs font-medium uppercase tracking-wide text-slate-500">Payment Status</label>
                <select name="payment_status" id="payment_status" class="admin-filter-control">
                    <option value="">All</option>
                    @foreach ($paymentStatuses as $s)
                        <option value="{{ $s->value }}" @selected(request('payment_status') === $s->value)>{{ ucfirst($s->value) }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="flex flex-wrap items-center gap-3">
            <button type="submit" class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-slate-800">Apply</button>
            <a href="{{ route('purchase-orders.index') }}" class="text-sm font-medium text-slate-600 hover:text-slate-900">Reset</a>
        </div>
    </form>

    <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-left text-sm">
                <thead class="bg-slate-50 text-xs font-semibold uppercase tracking-wide text-slate-500">
                <tr>
                    <th class="px-3 py-2">PO Number</th>
                    <th class="px-3 py-2">Title</th>
                    <th class="px-3 py-2">Vendor</th>
                    <th class="px-3 py-2">Total Price</th>
                    <th class="px-3 py-2">Order Status</th>
                    <th class="px-3 py-2">Payment</th>
                    <th class="px-3 py-2">Ordered At</th>
                    <th class="px-3 py-2">Delivered At</th>
                    <th class="px-3 py-2 text-right">Actions</th>
                </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                @forelse ($purchaseOrders as $po)
                    <tr class="hover:bg-slate-50/80">
                        <td class="whitespace-nowrap px-3 py-2 font-mono text-xs text-slate-700">{{ $po->po_number }}</td>
                        <td class="max-w-[12rem] truncate px-3 py-2 text-slate-900" title="{{ $po->title }}">{{ $po->title }}</td>
                        <td class="px-3 py-2 text-slate-700">{{ $po->vendor?->name ?? '—' }}</td>
                        <td class="whitespace-nowrap px-3 py-2 text-slate-700">{{ $po->total_price ? number_format($po->total_price, 2) : '—' }}</td>
                        <td class="whitespace-nowrap px-3 py-2">
                            <span class="inline-flex rounded-full bg-slate-100 px-2 py-0.5 text-xs font-medium text-slate-800">{{ ucfirst($po->status->value) }}</span>
                        </td>
                        <td class="whitespace-nowrap px-3 py-2">
                            <span class="inline-flex rounded-full bg-slate-100 px-2 py-0.5 text-xs font-medium text-slate-800">{{ ucfirst($po->payment_status->value) }}</span>
                        </td>
                        <td class="whitespace-nowrap px-3 py-2 text-xs text-slate-600">{{ $po->ordered_at?->format('Y-m-d') ?? '—' }}</td>
                        <td class="whitespace-nowrap px-3 py-2 text-xs text-slate-600">{{ $po->delivered_at?->format('Y-m-d') ?? '—' }}</td>
                        <td class="whitespace-nowrap px-3 py-2 text-right text-xs">
                            <a href="{{ route('purchase-orders.show', $po) }}" class="font-medium text-slate-700 hover:text-slate-900">View</a>
                            <span class="mx-1 text-slate-300">|</span>
                            <a href="{{ route('purchase-orders.edit', $po) }}" class="font-medium text-slate-700 hover:text-slate-900">Edit</a>
                            <span class="mx-1 text-slate-300">|</span>
                            <form action="{{ route('purchase-orders.destroy', $po) }}" method="post" class="inline"
                                  onsubmit="return confirm('Delete this purchase order?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="font-medium text-red-700 hover:text-red-900">Delete</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="px-3 py-10 text-center text-sm text-slate-500">No purchase orders found.</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
        @if ($purchaseOrders->hasPages())
            <div class="border-t border-slate-200 bg-slate-50 px-4 py-3">
                {{ $purchaseOrders->links() }}
            </div>
        @endif
    </div>
@endsection
