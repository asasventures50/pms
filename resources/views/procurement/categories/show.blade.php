@extends('layouts.admin')

@section('title', 'Category')

@section('content')
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <h1 class="text-2xl font-semibold tracking-tight text-slate-900" dir="auto">{{ $category->name_ar }}</h1>
            <p class="mt-1 text-sm text-slate-600">{{ $category->name_en }}</p>
            <p class="mt-1 font-mono text-xs text-slate-500">{{ $category->slug }}</p>
        </div>
        <div class="flex flex-wrap gap-3">
            <a href="{{ route('categories.edit', $category) }}"
               class="inline-flex items-center justify-center rounded-lg bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-slate-800">Edit</a>
            <a href="{{ route('categories.index') }}"
               class="inline-flex items-center justify-center rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-800 hover:bg-slate-50">Back to list</a>
        </div>
    </div>

    <div class="space-y-6">
        <section class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="border-b border-slate-100 pb-2 text-base font-semibold text-slate-900">Details</h2>
            <dl class="mt-4 grid gap-4 sm:grid-cols-2">
                <div>
                    <dt class="text-xs font-medium uppercase tracking-wide text-slate-500">Arabic name</dt>
                    <dd class="mt-1 text-sm text-slate-900" dir="auto">{{ $category->name_ar }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-medium uppercase tracking-wide text-slate-500">English name</dt>
                    <dd class="mt-1 text-sm text-slate-900">{{ $category->name_en }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-medium uppercase tracking-wide text-slate-500">Slug</dt>
                    <dd class="mt-1 font-mono text-sm text-slate-800">{{ $category->slug }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-medium uppercase tracking-wide text-slate-500">Status</dt>
                    <dd class="mt-1 text-sm text-slate-900">{{ $category->status }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-medium uppercase tracking-wide text-slate-500">Created</dt>
                    <dd class="mt-1 text-sm text-slate-900">{{ $category->created_at?->format('Y-m-d H:i') }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-medium uppercase tracking-wide text-slate-500">Updated</dt>
                    <dd class="mt-1 text-sm text-slate-900">{{ $category->updated_at?->format('Y-m-d H:i') }}</dd>
                </div>
            </dl>
        </section>

        <section class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
            <div class="flex flex-col gap-3 border-b border-slate-100 pb-3 sm:flex-row sm:items-center sm:justify-between">
                <h2 class="text-base font-semibold text-slate-900">Category-level vendors</h2>
                <a href="{{ route('categories.vendor-links', $category) }}"
                   class="text-sm font-medium text-slate-700 hover:text-slate-900">
                    Manage vendors ({{ $categoryOnlyVendorCount }})
                </a>
            </div>
            <p class="mt-3 text-sm text-slate-500">Vendors linked to this category without a specific subcategory.</p>
        </section>

        <section class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="border-b border-slate-100 pb-2 text-base font-semibold text-slate-900">Subcategories</h2>
            @if ($category->subcategories->isEmpty())
                <p class="mt-3 text-sm text-slate-500">No subcategories.</p>
            @else
                <div class="mt-3 overflow-x-auto rounded-lg border border-slate-200">
                    <table class="min-w-full divide-y divide-slate-200 text-sm">
                        <thead class="bg-slate-50 text-xs font-semibold uppercase tracking-wide text-slate-500">
                        <tr>
                            <th class="px-3 py-2 text-left">ID</th>
                            <th class="px-3 py-2 text-left">Arabic Name</th>
                            <th class="px-3 py-2 text-left">English Name</th>
                            <th class="px-3 py-2 text-left">Slug</th>
                            <th class="px-3 py-2 text-left">Status</th>
                            <th class="px-3 py-2 text-left">Vendors</th>
                            <th class="px-3 py-2 text-right">Actions</th>
                        </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                        @foreach ($category->subcategories as $sub)
                            <tr>
                                <td class="px-3 py-2 font-mono text-xs">{{ $sub->id }}</td>
                                <td class="px-3 py-2" dir="auto">{{ $sub->name_ar }}</td>
                                <td class="px-3 py-2">{{ $sub->name_en }}</td>
                                <td class="px-3 py-2 font-mono text-xs">{{ $sub->slug }}</td>
                                <td class="px-3 py-2">{{ $sub->status }}</td>
                                <td class="px-3 py-2 text-slate-700">{{ $sub->vendors_count }}</td>
                                <td class="px-3 py-2 text-right text-xs">
                                    <a href="{{ route('categories.subcategories.vendor-links', [$category, $sub]) }}"
                                       class="font-medium text-slate-700 hover:text-slate-900">Vendors</a>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </section>
    </div>
@endsection
