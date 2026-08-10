@extends('layouts.admin')

@section('title', 'Edit '.$schedule->document_number)

@section('content')
    <div class="mb-6">
        <a href="{{ route('schedule-of-works.index') }}" class="text-sm font-medium text-slate-600 hover:text-slate-900">← Schedule of Works</a>
        <h1 class="mt-2 text-2xl font-semibold tracking-tight text-slate-900">Edit Schedule of Works</h1>
        <p class="mt-1 text-sm text-slate-600">
            <span class="font-mono text-slate-800">{{ $schedule->document_number }}</span>
            — update lines, scope, fees, and notes, then print again.
        </p>
    </div>

    <form method="post" action="{{ route('schedule-of-works.update', $schedule) }}" id="sow-form" class="space-y-6">
        @csrf
        @method('PUT')
        @include('procurement.schedule-of-works._form', ['formDefaults' => $formDefaults])
        <div class="flex flex-wrap gap-3">
            <button type="submit"
                    class="rounded-lg bg-brand px-5 py-2.5 text-sm font-medium text-white shadow-sm hover:bg-brand-hover">
                Save &amp; Print
            </button>
            <a href="{{ route('schedule-of-works.print', ['schedule_of_work' => $schedule, 'locale' => $schedule->print_locale]) }}"
               class="rounded-lg border border-slate-300 bg-white px-5 py-2.5 text-sm font-medium text-slate-800 hover:bg-slate-50">
                Print without saving
            </a>
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
