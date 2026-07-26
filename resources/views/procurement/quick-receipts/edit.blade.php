@extends('layouts.admin')

@section('title', 'Edit '.$receipt->code)

@section('content')
    <div class="mb-6">
        <a href="{{ route('quick-receipts.show', $receipt) }}"
           class="inline-flex items-center gap-1.5 rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-sm font-medium text-slate-800 shadow-sm hover:bg-slate-50">
            <span aria-hidden="true">←</span> Back to {{ $receipt->code }}
        </a>
        <h1 class="mt-2 text-2xl font-semibold tracking-tight text-slate-900">Edit Quick Receipt</h1>
        <p class="mt-1 text-sm text-slate-600 font-mono">{{ $receipt->code }} · {{ $receipt->status?->label() }} — saving sends it back to pending approval.</p>
    </div>

    <form method="post" action="{{ route('quick-receipts.update', $receipt) }}" enctype="multipart/form-data" class="space-y-6">
        @csrf
        @method('PUT')
        @include('procurement.quick-receipts._form')

        <div class="flex flex-wrap items-center gap-3">
            <button type="submit"
                    class="rounded-lg bg-slate-900 px-5 py-2.5 text-sm font-medium text-white shadow-sm hover:bg-slate-800">
                Save changes
            </button>
            <a href="{{ route('quick-receipts.show', $receipt) }}"
               class="rounded-lg border border-slate-300 bg-white px-5 py-2.5 text-sm font-medium text-slate-800 hover:bg-slate-50">
                Cancel
            </a>
        </div>
    </form>
@endsection

@push('scripts')
    @include('partials.searchable-select-scripts')
@endpush
