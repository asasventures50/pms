@extends('layouts.admin')

@section('title', 'Add Category')

@section('content')
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-semibold tracking-tight text-slate-900">Add Category</h1>
            <p class="mt-1 text-sm text-slate-600">Create a category and optional subcategories.</p>
        </div>
        <a href="{{ route('categories.index') }}" class="text-sm font-medium text-slate-600 hover:text-slate-900">Back to list</a>
    </div>

    <form action="{{ route('categories.store') }}" method="post" class="space-y-8">
        @csrf
        @include('procurement.categories._form', ['mode' => 'create', 'category' => $category])

        <div class="flex flex-wrap items-center gap-3">
            <button type="submit"
                    class="rounded-lg bg-brand px-5 py-2.5 text-sm font-medium text-white shadow-sm hover:bg-brand-hover">
                Save
            </button>
            <a href="{{ route('categories.index') }}"
               class="rounded-lg border border-slate-300 bg-white px-5 py-2.5 text-sm font-medium text-slate-800 hover:bg-slate-50">
                Cancel
            </a>
        </div>
    </form>
@endsection
