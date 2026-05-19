@extends('layouts.admin')

@section('title', 'Add RFQ')

@section('content')
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-semibold tracking-tight text-slate-900">Add RFQ</h1>
            <p class="mt-1 text-sm text-slate-600">Create a request for quotation.</p>
        </div>
        <a href="{{ route('rfqs.index') }}" class="text-sm font-medium text-slate-600 hover:text-slate-900">Back to list</a>
    </div>

    <form action="{{ route('rfqs.store') }}" method="post" class="space-y-6">
        @csrf
        @include('procurement.rfqs._form', [
            'vendors' => $vendors,
            'nextCode' => $nextCode,
            'defaultItems' => $defaultItems,
        ])
        <div class="flex flex-wrap gap-3">
            <button type="submit" class="rounded-lg bg-slate-900 px-5 py-2.5 text-sm font-medium text-white hover:bg-slate-800">Save RFQ</button>
            <a href="{{ route('rfqs.index') }}" class="rounded-lg border border-slate-300 bg-white px-5 py-2.5 text-sm font-medium text-slate-800 hover:bg-slate-50">Cancel</a>
        </div>
    </form>
@endsection
