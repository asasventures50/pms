@extends('layouts.admin')

@section('title', 'Add Procurement Request')

@section('content')
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-semibold tracking-tight text-slate-900">Add Procurement Request</h1>
            <p class="mt-1 text-sm text-slate-600">Create an internal procurement request form.</p>
        </div>
        <a href="{{ route('procurement-requests.index') }}" class="text-sm font-medium text-slate-600 hover:text-slate-900">Back to list</a>
    </div>

    <form action="{{ route('procurement-requests.store') }}" method="post" enctype="multipart/form-data" class="space-y-6">
        @csrf
        @include('procurement.procurement-requests._form', [
            'nextCode' => $nextCode,
            'defaultItems' => $defaultItems,
            'projects' => $projects,
        ])
        <div class="flex flex-wrap gap-3">
            <button type="submit" class="rounded-lg bg-slate-900 px-5 py-2.5 text-sm font-medium text-white hover:bg-slate-800">Send request</button>
            <a href="{{ route('procurement-requests.index') }}" class="rounded-lg border border-slate-300 bg-white px-5 py-2.5 text-sm font-medium text-slate-800 hover:bg-slate-50">Cancel</a>
        </div>
    </form>
@endsection
