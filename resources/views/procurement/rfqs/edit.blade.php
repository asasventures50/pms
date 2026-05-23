@extends('layouts.admin')

@section('title', 'Edit RFQ')

@section('content')
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-semibold tracking-tight text-slate-900">Edit RFQ</h1>
            <p class="mt-1 text-sm text-slate-600">{{ $rfq->rfq_number }} — Prepared by {{ $rfq->creator?->name }}</p>
        </div>
        <div class="flex gap-3 text-sm">
            <a href="{{ route('rfqs.show', $rfq) }}" class="font-medium text-slate-600 hover:text-slate-900">View</a>
            <a href="{{ route('rfqs.index') }}" class="font-medium text-slate-600 hover:text-slate-900">Back to list</a>
        </div>
    </div>

    <form action="{{ route('rfqs.update', $rfq) }}" method="post" class="space-y-6">
        @csrf
        @method('PUT')
        @include('procurement.rfqs._form', [
            'rfq' => $rfq,
            'vendors' => $vendors,
            'defaultItems' => $defaultItems,
            'prItemOptions' => $prItemOptions,
        ])
        <div class="flex flex-wrap gap-3">
            <button type="submit" class="rounded-lg bg-slate-900 px-5 py-2.5 text-sm font-medium text-white hover:bg-slate-800">Update RFQ</button>
            <a href="{{ route('rfqs.show', $rfq) }}" class="rounded-lg border border-slate-300 bg-white px-5 py-2.5 text-sm font-medium text-slate-800 hover:bg-slate-50">Cancel</a>
        </div>
    </form>
@endsection
