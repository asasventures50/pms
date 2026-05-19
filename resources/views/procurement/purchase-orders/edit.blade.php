@extends('layouts.admin')

@section('title', 'Edit Purchase Order')

@section('content')
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-semibold tracking-tight text-slate-900">Edit Purchase Order</h1>
            <p class="mt-1 text-sm text-slate-600">{{ $purchaseOrder->po_number }} — {{ $purchaseOrder->title }}</p>
        </div>
        <div class="flex flex-wrap gap-3">
            <a href="{{ route('purchase-orders.show', $purchaseOrder) }}" class="text-sm font-medium text-slate-600 hover:text-slate-900">View</a>
            <a href="{{ route('purchase-orders.index') }}" class="text-sm font-medium text-slate-600 hover:text-slate-900">Back to list</a>
        </div>
    </div>

    <form action="{{ route('purchase-orders.update', $purchaseOrder) }}" method="post" class="space-y-8">
        @csrf
        @method('PUT')
        @include('procurement.purchase-orders._form', [
            'purchaseOrder' => $purchaseOrder,
            'vendors' => $vendors,
            'defaultItems' => $defaultItems,
        ])

        <div class="flex flex-wrap items-center gap-3">
            <button type="submit"
                    class="rounded-lg bg-slate-900 px-5 py-2.5 text-sm font-medium text-white shadow-sm hover:bg-slate-800">
                Update
            </button>
            <a href="{{ route('purchase-orders.show', $purchaseOrder) }}"
               class="rounded-lg border border-slate-300 bg-white px-5 py-2.5 text-sm font-medium text-slate-800 hover:bg-slate-50">
                Cancel
            </a>
        </div>
    </form>
@endsection
