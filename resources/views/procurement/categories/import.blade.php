@extends('layouts.admin')

@section('title', 'Import Categories')

@section('content')
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-semibold tracking-tight text-slate-900">Import Categories</h1>
            <p class="mt-1 text-sm text-slate-600">Upload an Excel or CSV file. Rows are matched by category slug or English name, then subcategory slug or English name.</p>
        </div>
        <a href="{{ route('categories.index') }}" class="text-sm font-medium text-slate-600 hover:text-slate-900">Back to list</a>
    </div>

    <div class="mb-6 rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
        <h2 class="text-sm font-semibold text-slate-900">Sample template</h2>
        <p class="mt-1 text-sm text-slate-600">Download a file with the required column headers and one example row.</p>
        <a href="{{ route('categories.import.template') }}"
           class="mt-3 inline-flex rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-800 hover:bg-slate-50">
            Download sample template
        </a>
    </div>

    <form action="{{ route('categories.import') }}" method="post" enctype="multipart/form-data" class="max-w-xl space-y-6 rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
        @csrf
        <div>
            <label for="file" class="block text-xs font-medium uppercase tracking-wide text-slate-500">File <span class="text-red-600">*</span></label>
            <input type="file" name="file" id="file" required accept=".xlsx,.xls,.csv"
                   class="admin-form-file @error('file') border-red-500 @enderror">
            <p class="mt-1 text-xs text-slate-500">Accepted: .xlsx, .xls, .csv — max 20 MB.</p>
            @error('file')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>
        <div class="flex flex-wrap gap-3">
            <button type="submit" class="rounded-lg bg-slate-900 px-5 py-2.5 text-sm font-medium text-white shadow-sm hover:bg-slate-800">
                Import
            </button>
            <a href="{{ route('categories.index') }}" class="rounded-lg border border-slate-300 bg-white px-5 py-2.5 text-sm font-medium text-slate-800 hover:bg-slate-50">
                Cancel
            </a>
        </div>
    </form>

    @if (session('import_errors') && count(session('import_errors')) > 0)
        <div class="mt-6 rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-950">
            <p class="font-medium">Import messages</p>
            <ul class="mt-2 list-inside list-disc space-y-1">
                @foreach (session('import_errors') as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif
@endsection
