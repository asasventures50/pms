@extends('layouts.admin')

@section('title', 'Add Invoice')

@section('content')
    <div class="mb-6">
        <a href="{{ route('invoices.index') }}" class="text-sm font-medium text-slate-600 hover:text-slate-900">← Invoices</a>
        <h1 class="mt-2 text-2xl font-semibold tracking-tight text-slate-900">Add Invoice</h1>
        <p class="mt-1 text-sm text-slate-600">Select purchase orders, duplicate an existing invoice, enter data manually, then print.</p>
    </div>

    <form method="post" action="{{ route('invoices.store') }}" id="invoice-form" class="space-y-6">
        @csrf
        @include('procurement.invoices._form')
        <div class="flex flex-wrap gap-3">
            <button type="submit"
                    class="rounded-lg bg-brand px-5 py-2.5 text-sm font-medium text-white shadow-sm hover:bg-brand-hover">
                Save
            </button>
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
