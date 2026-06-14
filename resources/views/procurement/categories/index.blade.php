@extends('layouts.admin')

@section('title', 'Categories')

@section('content')
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-semibold tracking-tight text-slate-900">Categories</h1>
            <p class="mt-1 text-sm text-slate-600">Manage procurement categories and subcategories.</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('categories.create') }}"
               class="inline-flex items-center justify-center rounded-lg bg-slate-900 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-slate-800">
                Add Category
            </a>
            <a href="{{ route('categories.export') }}"
               class="inline-flex items-center justify-center rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-800 hover:bg-slate-50">
                Export Excel
            </a>
            <a href="{{ route('categories.import.form') }}"
               class="inline-flex items-center justify-center rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-800 hover:bg-slate-50">
                Import Excel
            </a>
        </div>
    </div>

    <form method="get" action="{{ route('categories.index') }}" class="mb-6 space-y-4 rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
        <input type="hidden" name="sort_by" value="{{ $sortColumn }}">
        <input type="hidden" name="sort_direction" value="{{ $sortDirection }}">
        <div class="grid gap-4 md:grid-cols-3">
            <div class="md:col-span-2">
                <label for="q" class="block text-xs font-medium uppercase tracking-wide text-slate-500">Search</label>
                <input type="search" name="q" id="q" value="{{ request('q') }}"
                       placeholder="Arabic name, English name, or slug"
                       class="admin-filter-control">
            </div>
            <div>
                <label for="status" class="block text-xs font-medium uppercase tracking-wide text-slate-500">Status</label>
                <select name="status" id="status"
                        class="admin-filter-control">
                    <option value="">All</option>
                    <option value="active" @selected(request('status') === 'active')>Active</option>
                    <option value="inactive" @selected(request('status') === 'inactive')>Inactive</option>
                </select>
            </div>
        </div>
        <div class="flex flex-wrap items-center gap-3">
            <button type="submit" class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-slate-800">Apply</button>
            <a href="{{ route('categories.index') }}" class="text-sm font-medium text-slate-600 hover:text-slate-900">Reset</a>
        </div>
    </form>

    <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-left text-sm">
                <thead class="bg-slate-50 text-xs font-semibold uppercase tracking-wide text-slate-500">
                <tr>
                    <th class="px-3 py-2">ID</th>
                    <th class="px-3 py-2">
                        @include('partials.table-sort-link', [
                            'route' => 'categories.index',
                            'column' => 'name_ar',
                            'label' => 'Arabic Name',
                            'sortColumn' => $sortColumn,
                            'sortDirection' => $sortDirection,
                        ])
                    </th>
                    <th class="px-3 py-2">
                        @include('partials.table-sort-link', [
                            'route' => 'categories.index',
                            'column' => 'name_en',
                            'label' => 'English Name',
                            'sortColumn' => $sortColumn,
                            'sortDirection' => $sortDirection,
                        ])
                    </th>
                    <th class="px-3 py-2">Slug</th>
                    <th class="px-3 py-2">Status</th>
                    <th class="px-3 py-2">Subcategories</th>
                    <th class="px-3 py-2">Vendors</th>
                    <th class="px-3 py-2">
                        @include('partials.table-sort-link', [
                            'route' => 'categories.index',
                            'column' => 'created_at',
                            'label' => 'Created',
                            'sortColumn' => $sortColumn,
                            'sortDirection' => $sortDirection,
                        ])
                    </th>
                    <th class="px-3 py-2 text-right">Actions</th>
                </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                @forelse ($categories as $category)
                    <tr class="hover:bg-slate-50/80">
                        <td class="whitespace-nowrap px-3 py-2 font-mono text-xs text-slate-700">{{ $category->id }}</td>
                        <td class="max-w-[10rem] truncate px-3 py-2 text-slate-900" dir="auto" title="{{ $category->name_ar }}">{{ $category->name_ar }}</td>
                        <td class="max-w-[10rem] truncate px-3 py-2 text-slate-700" title="{{ $category->name_en }}">{{ $category->name_en }}</td>
                        <td class="whitespace-nowrap px-3 py-2 font-mono text-xs text-slate-600">{{ $category->slug }}</td>
                        <td class="whitespace-nowrap px-3 py-2">
                            <span class="inline-flex rounded-full bg-slate-100 px-2 py-0.5 text-xs font-medium text-slate-800">{{ $category->status }}</span>
                        </td>
                        <td class="whitespace-nowrap px-3 py-2 text-slate-700">{{ $category->subcategories_count }}</td>
                        <td class="whitespace-nowrap px-3 py-2 text-slate-700">{{ $category->vendors_count }}</td>
                        <td class="whitespace-nowrap px-3 py-2 text-xs text-slate-600">{{ $category->created_at?->format('Y-m-d H:i') }}</td>
                        <td class="whitespace-nowrap px-3 py-2 text-right text-xs">
                            <a href="{{ route('categories.show', $category) }}" class="font-medium text-slate-700 hover:text-slate-900">View</a>
                            <span class="mx-1 text-slate-300">|</span>
                            <a href="{{ route('categories.edit', $category) }}" class="font-medium text-slate-700 hover:text-slate-900">Edit</a>
                            <span class="mx-1 text-slate-300">|</span>
                            @if ((int) $category->vendors_count === 0)
                                <form action="{{ route('categories.destroy', $category) }}" method="post" class="inline" onsubmit="return confirm('Delete this category and soft-delete its subcategories?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="font-medium text-red-700 hover:text-red-900">Delete</button>
                                </form>
                            @else
                                <span class="font-medium text-slate-400" title="Cannot delete while this category is linked to one or more vendors.">Delete</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="px-3 py-10 text-center text-sm text-slate-500">No categories found.</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
        @if ($categories->hasPages())
            <div class="border-t border-slate-200 bg-slate-50 px-4 py-3">
                {{ $categories->links() }}
            </div>
        @endif
    </div>
@endsection
