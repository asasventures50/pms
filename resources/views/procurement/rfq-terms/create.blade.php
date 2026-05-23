@extends('layouts.admin')

@section('title', 'Add RFQ general term')

@section('content')
    <div class="mb-6">
        <h1 class="text-2xl font-semibold tracking-tight text-slate-900">Add general term</h1>
        <p class="mt-1 text-sm text-slate-600">
            <a href="{{ route('rfq-terms.index') }}" class="font-medium text-slate-600 hover:text-slate-900">Back to terms</a>
        </p>
    </div>

    <form action="{{ route('rfq-terms.store') }}" method="post" class="space-y-6 max-w-3xl">
        @csrf
        @include('procurement.rfq-terms._form', ['term' => $term])
        <div class="flex flex-wrap gap-3">
            <button type="submit" class="rounded-lg bg-slate-900 px-5 py-2.5 text-sm font-medium text-white hover:bg-slate-800">Save</button>
            <a href="{{ route('rfq-terms.index') }}" class="rounded-lg border border-slate-300 bg-white px-5 py-2.5 text-sm font-medium text-slate-800 hover:bg-slate-50">Cancel</a>
        </div>
    </form>
@endsection
