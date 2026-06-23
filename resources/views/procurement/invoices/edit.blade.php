@extends('layouts.admin')

@section('title', 'Edit Invoice '.$invoice->invoice_number)

@section('content')
    <div class="mb-6">
        <a href="{{ route('invoices.index') }}" class="text-sm font-medium text-slate-600 hover:text-slate-900">← Invoices</a>
        <h1 class="mt-2 text-2xl font-semibold tracking-tight text-slate-900">Edit Invoice</h1>
        <p class="mt-1 text-sm text-slate-600">
            <span class="font-mono text-slate-800">{{ $invoice->invoice_number }}</span>
            — update purchase orders, line items, fees, and notes, then print again.
        </p>
    </div>

    <form method="post" action="{{ route('invoices.update', $invoice) }}" id="invoice-form" class="space-y-6">
        @csrf
        @method('PUT')
        @include('procurement.invoices._form', ['invoiceDefaults' => $invoiceDefaults])
        <div class="flex flex-wrap gap-3">
            <button type="submit"
                    class="rounded-lg bg-slate-900 px-5 py-2.5 text-sm font-medium text-white shadow-sm hover:bg-slate-800">
                Save &amp; Print
            </button>
            <a href="{{ route('invoices.print', $invoice) }}"
               class="rounded-lg border border-slate-300 bg-white px-5 py-2.5 text-sm font-medium text-slate-800 hover:bg-slate-50">
                Print without saving
            </a>
            <a href="{{ route('invoices.index') }}"
               class="rounded-lg border border-slate-300 bg-white px-5 py-2.5 text-sm font-medium text-slate-800 hover:bg-slate-50">
                Cancel
            </a>
        </div>
    </form>
@endsection

@push('scripts')
    @include('procurement.invoices._form-scripts')
@endpush
