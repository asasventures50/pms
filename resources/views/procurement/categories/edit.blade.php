@extends('layouts.admin')

@section('title', 'Edit Category')

@section('content')
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-semibold tracking-tight text-slate-900">Edit Category</h1>
            <p class="mt-1 text-sm text-slate-600">{{ $category->name_en }}</p>
        </div>
        <div class="flex flex-wrap gap-3">
            <a href="{{ route('categories.show', $category) }}" class="text-sm font-medium text-slate-600 hover:text-slate-900">View</a>
            <a href="{{ route('categories.index') }}" class="text-sm font-medium text-slate-600 hover:text-slate-900">Back to list</a>
        </div>
    </div>

    <form action="{{ route('categories.update', $category) }}" method="post" class="space-y-8">
        @csrf
        @method('PUT')
        @include('procurement.categories._form', ['mode' => 'edit', 'category' => $category])

        <div class="flex flex-wrap items-center gap-3">
            <button type="submit"
                    class="rounded-lg bg-slate-900 px-5 py-2.5 text-sm font-medium text-white shadow-sm hover:bg-slate-800">
                Update
            </button>
            <a href="{{ route('categories.show', $category) }}"
               class="rounded-lg border border-slate-300 bg-white px-5 py-2.5 text-sm font-medium text-slate-800 hover:bg-slate-50">
                Cancel
            </a>
        </div>
    </form>
@endsection
