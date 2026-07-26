@extends('layouts.admin')

@section('title', 'New Quick Receipt')

@section('content')
    <div class="mb-6">
        <a href="{{ route('quick-receipts.index') }}"
           class="inline-flex items-center gap-1.5 rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-sm font-medium text-slate-800 shadow-sm hover:bg-slate-50">
            <span aria-hidden="true">←</span> Back to Quick Receipts
        </a>
        <h1 class="mt-2 text-2xl font-semibold tracking-tight text-slate-900">New Quick Receipt</h1>
        <p class="mt-1 text-sm text-slate-600">Create a one-off expense. It goes to pending approval immediately.</p>
    </div>

    <form method="post" action="{{ route('quick-receipts.store') }}" enctype="multipart/form-data" class="space-y-6">
        @csrf
        @include('procurement.quick-receipts._form')

        <div class="flex flex-wrap items-center gap-3">
            <button type="submit"
                    class="rounded-lg bg-slate-900 px-5 py-2.5 text-sm font-medium text-white shadow-sm hover:bg-slate-800">
                Create receipt
            </button>
            <a href="{{ route('quick-receipts.index') }}"
               class="rounded-lg border border-slate-300 bg-white px-5 py-2.5 text-sm font-medium text-slate-800 hover:bg-slate-50">
                Cancel
            </a>
        </div>
    </form>
@endsection

@push('scripts')
    @include('partials.searchable-select-scripts')
@endpush
