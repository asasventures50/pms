@extends('layouts.admin')

@section('title', 'Roles')

@section('content')
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-semibold tracking-tight text-slate-900">Roles</h1>
            <p class="mt-1 text-sm text-slate-600">Manage roles and their permissions.</p>
        </div>
        @if (auth()->user()->hasPermission('roles.create'))
            <a href="{{ route('roles.create') }}"
               class="inline-flex items-center justify-center rounded-lg bg-slate-900 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-slate-800">
                Add Role
            </a>
        @endif
    </div>

    <form method="get" action="{{ route('roles.index') }}" class="mb-6 rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
        <input type="hidden" name="sort_by" value="{{ $sortColumn }}">
        <input type="hidden" name="sort_direction" value="{{ $sortDirection }}">
        <label for="q" class="block text-xs font-medium uppercase tracking-wide text-slate-500">Search</label>
        <div class="mt-2 flex flex-wrap items-end gap-3">
            <input type="search" id="q" name="q" value="{{ request('q') }}"
                   placeholder="Name or slug"
                   class="admin-filter-control min-w-[16rem] flex-1">
            <button type="submit" class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-slate-800">Apply</button>
            <a href="{{ route('roles.index') }}" class="text-sm font-medium text-slate-600 hover:text-slate-900">Reset</a>
        </div>
    </form>

    <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-left text-sm">
                <thead class="bg-slate-50 text-xs font-semibold uppercase tracking-wide text-slate-500">
                <tr>
                    <th class="px-3 py-2">ID</th>
                    <th class="px-3 py-2">
                        @include('partials.table-sort-link', ['route' => 'roles.index', 'column' => 'label', 'label' => 'Name', 'sortColumn' => $sortColumn, 'sortDirection' => $sortDirection])
                    </th>
                    <th class="px-3 py-2">
                        @include('partials.table-sort-link', ['route' => 'roles.index', 'column' => 'name', 'label' => 'Slug', 'sortColumn' => $sortColumn, 'sortDirection' => $sortDirection])
                    </th>
                    <th class="px-3 py-2">Users</th>
                    <th class="px-3 py-2">Permissions</th>
                    <th class="px-3 py-2 text-right">Actions</th>
                </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                @forelse ($roles as $role)
                    <tr class="hover:bg-slate-50/80">
                        <td class="whitespace-nowrap px-3 py-2 font-mono text-xs text-slate-700">{{ $role->id }}</td>
                        <td class="px-3 py-2 font-medium text-slate-900">{{ $role->label }}</td>
                        <td class="px-3 py-2 font-mono text-xs text-slate-600">{{ $role->name }}</td>
                        <td class="px-3 py-2">{{ $role->users_count }}</td>
                        <td class="px-3 py-2">{{ $role->permissions_count }}</td>
                        <td class="whitespace-nowrap px-3 py-2 text-right text-xs">
                            @if (auth()->user()->hasPermission('roles.update'))
                                <a href="{{ route('roles.edit', $role) }}" class="font-medium text-slate-700 hover:text-slate-900">Edit</a>
                            @endif
                            @if (auth()->user()->hasPermission('roles.delete') && ! in_array($role->name, ['super-admin', 'procurement-officer'], true))
                                <span class="mx-1 text-slate-300">|</span>
                                <form action="{{ route('roles.destroy', $role) }}" method="post" class="inline" onsubmit="return confirm('Delete this role?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="font-medium text-red-700 hover:text-red-900">Delete</button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-3 py-10 text-center text-sm text-slate-500">No roles found.</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
        @if ($roles->hasPages())
            <div class="border-t border-slate-200 bg-slate-50 px-4 py-3">{{ $roles->links() }}</div>
        @endif
    </div>
@endsection
