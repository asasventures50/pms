@extends('layouts.admin')

@section('title', 'Projects')

@section('content')
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-semibold tracking-tight text-slate-900">Projects</h1>
            <p class="mt-1 text-sm text-slate-600">Manage projects and their zones for procurement requests.</p>
        </div>
        <div class="flex flex-wrap items-center gap-3">
            <a href="{{ route('dashboard') }}" class="text-sm font-medium text-slate-600 hover:text-slate-900">Dashboard</a>
            @if (auth()->user()->hasPermission('projects.create'))
                <a href="{{ route('projects.create') }}"
                   class="inline-flex items-center justify-center rounded-lg bg-brand px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-brand-hover">
                    Add project + zones
                </a>
            @endif
        </div>
    </div>

    <form method="get" action="{{ route('projects.index') }}" class="mb-6 space-y-4 rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
        <input type="hidden" name="sort_by" value="{{ $sortColumn }}">
        <input type="hidden" name="sort_direction" value="{{ $sortDirection }}">
        <div class="grid gap-4 md:grid-cols-3">
            <div class="md:col-span-2">
                <label for="q" class="block text-xs font-medium uppercase tracking-wide text-slate-500">Search</label>
                <input type="search" name="q" id="q" value="{{ request('q') }}"
                       placeholder="Code or name"
                       class="admin-filter-control">
            </div>
            <div>
                <label for="status" class="block text-xs font-medium uppercase tracking-wide text-slate-500">Status</label>
                <select name="status" id="status" class="admin-filter-control">
                    <option value="">All</option>
                    <option value="active" @selected(request('status') === 'active')>Active</option>
                    <option value="inactive" @selected(request('status') === 'inactive')>Inactive</option>
                </select>
            </div>
        </div>
        <div class="flex flex-wrap items-center gap-3">
            <button type="submit" class="rounded-lg bg-brand px-4 py-2 text-sm font-medium text-white hover:bg-brand-hover">Apply</button>
            <a href="{{ route('projects.index') }}" class="text-sm font-medium text-slate-600 hover:text-slate-900">Reset</a>
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
                            'route' => 'projects.index',
                            'column' => 'code',
                            'label' => 'Code',
                            'sortColumn' => $sortColumn,
                            'sortDirection' => $sortDirection,
                        ])
                    </th>
                    <th class="px-3 py-2">
                        @include('partials.table-sort-link', [
                            'route' => 'projects.index',
                            'column' => 'name',
                            'label' => 'Name',
                            'sortColumn' => $sortColumn,
                            'sortDirection' => $sortDirection,
                        ])
                    </th>
                    <th class="px-3 py-2">Status</th>
                    <th class="px-3 py-2">Zones</th>
                    <th class="px-3 py-2">Actions</th>
                </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                @forelse ($projects as $project)
                    <tr>
                        <td class="px-3 py-3 text-slate-600">{{ $project->id }}</td>
                        <td class="px-3 py-3 font-mono text-xs text-slate-800">{{ $project->code }}</td>
                        <td class="px-3 py-3 font-medium text-slate-900">{{ $project->name }}</td>
                        <td class="px-3 py-3 capitalize text-slate-700">{{ $project->status }}</td>
                        <td class="px-3 py-3 text-slate-700">{{ $project->zones_count }}</td>
                        <td class="px-3 py-3">
                            <div class="flex flex-wrap gap-2">
                                <a href="{{ route('projects.show', $project) }}" class="font-medium text-slate-700 hover:text-slate-900">View</a>
                                @if (auth()->user()->hasPermission('projects.update'))
                                    <a href="{{ route('projects.edit', $project) }}" class="font-medium text-slate-700 hover:text-slate-900">Edit</a>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-3 py-8 text-center text-slate-500">No projects found.</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
        @if ($projects->hasPages())
            <div class="border-t border-slate-200 px-4 py-3">
                {{ $projects->links() }}
            </div>
        @endif
    </div>
@endsection
