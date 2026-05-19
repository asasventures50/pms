@extends('layouts.admin')

@section('title', 'Edit Procurement Request')

@section('content')
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-semibold tracking-tight text-slate-900">Edit Procurement Request</h1>
            <p class="mt-1 text-sm text-slate-600">{{ $procurementRequest->request_number }}</p>
        </div>
        <div class="flex gap-3 text-sm">
            <a href="{{ route('procurement-requests.show', $procurementRequest) }}" class="font-medium text-slate-600 hover:text-slate-900">View</a>
            <a href="{{ route('procurement-requests.index') }}" class="font-medium text-slate-600 hover:text-slate-900">Back to list</a>
        </div>
    </div>

    <form action="{{ route('procurement-requests.update', $procurementRequest) }}" method="post" class="space-y-6">
        @csrf
        @method('PUT')
        @include('procurement.procurement-requests._form', [
            'procurementRequest' => $procurementRequest,
            'defaultItems' => $defaultItems,
        ])
        <div class="flex flex-wrap gap-3">
            <button type="submit" class="rounded-lg bg-slate-900 px-5 py-2.5 text-sm font-medium text-white hover:bg-slate-800">Update request</button>
            <a href="{{ route('procurement-requests.show', $procurementRequest) }}" class="rounded-lg border border-slate-300 bg-white px-5 py-2.5 text-sm font-medium text-slate-800 hover:bg-slate-50">Cancel</a>
        </div>
    </form>
@endsection
