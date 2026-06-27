@extends('layouts.admin')

@section('title', 'Add Schedule of Works')

@section('content')
    <div class="mb-6">
        <a href="{{ route('schedule-of-works.index') }}" class="text-sm font-medium text-slate-600 hover:text-slate-900">← Schedule of Works</a>
        <h1 class="mt-2 text-2xl font-semibold tracking-tight text-slate-900">Add Schedule of Works</h1>
        <p class="mt-1 text-sm text-slate-600">Enter line items manually, choose scope of work, then print in Arabic or English.</p>
    </div>

    <form method="post" action="{{ route('schedule-of-works.store') }}" id="sow-form" class="space-y-6">
        @csrf
        @include('procurement.schedule-of-works._form')
        <div class="flex flex-wrap gap-3">
            <button type="submit"
                    class="rounded-lg bg-slate-900 px-5 py-2.5 text-sm font-medium text-white shadow-sm hover:bg-slate-800">
                Create &amp; Print
            </button>
            <a href="{{ route('schedule-of-works.index') }}"
               class="rounded-lg border border-slate-300 bg-white px-5 py-2.5 text-sm font-medium text-slate-800 hover:bg-slate-50">
                Cancel
            </a>
        </div>
    </form>
@endsection

@push('scripts')
    @include('procurement.partials._vendor-search-scripts')
    @include('procurement.schedule-of-works._form-scripts')
@endpush
