@extends('layouts.admin')

@section('title', 'Edit RFQ general term')

@section('content')
    <div class="mb-6">
        <h1 class="text-2xl font-semibold tracking-tight text-slate-900">Edit general term</h1>
        <p class="mt-1 text-sm text-slate-600">
            <a href="{{ route('rfq-terms.index') }}" class="font-medium text-slate-600 hover:text-slate-900">Back to terms</a>
        </p>
    </div>

    <form action="{{ route('rfq-terms.update', $term) }}" method="post" class="space-y-6 max-w-3xl">
        @csrf
        @method('PUT')
        @include('procurement.rfq-terms._form', ['term' => $term])
        <div class="flex flex-wrap gap-3">
            <button type="submit" class="rounded-lg bg-brand px-5 py-2.5 text-sm font-medium text-white hover:bg-brand-hover">Save</button>
            <a href="{{ route('rfq-terms.index') }}" class="rounded-lg border border-slate-300 bg-white px-5 py-2.5 text-sm font-medium text-slate-800 hover:bg-slate-50">Cancel</a>
            @if (auth()->user()->hasPermission('rfq-terms.manage'))
                <button type="submit"
                        form="delete-rfq-term"
                        class="rounded-lg border border-red-300 bg-white px-5 py-2.5 text-sm font-medium text-red-700 hover:bg-red-50"
                        onclick="return confirm('Delete this term?')">
                    Delete
                </button>
            @endif
        </div>
    </form>

    @if (auth()->user()->hasPermission('rfq-terms.manage'))
        <form id="delete-rfq-term" action="{{ route('rfq-terms.destroy', $term) }}" method="post" class="hidden">
            @csrf
            @method('DELETE')
        </form>
    @endif
@endsection
