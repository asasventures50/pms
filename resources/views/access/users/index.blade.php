@php
    use App\Support\Access\UserDepartment;
@endphp

@extends('layouts.admin')

@section('title', 'Users')

@section('content')
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-semibold tracking-tight text-slate-900">Users</h1>
            <p class="mt-1 text-sm text-slate-600">Manage back-office accounts and their roles.</p>
        </div>
        @if (auth()->user()->hasPermission('users.create'))
            <a href="{{ route('users.create') }}"
               class="inline-flex items-center justify-center rounded-lg bg-brand px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-brand-hover">
                Add User
            </a>
        @endif
    </div>

    <form method="get" action="{{ route('users.index') }}" class="mb-6 space-y-4 rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
        <input type="hidden" name="sort_by" value="{{ $sortColumn }}">
        <input type="hidden" name="sort_direction" value="{{ $sortDirection }}">
        <div class="grid gap-4 md:grid-cols-4">
            <div class="md:col-span-2">
                <label for="q" class="block text-xs font-medium uppercase tracking-wide text-slate-500">Search</label>
                <input type="search" id="q" name="q" value="{{ request('q') }}"
                       placeholder="Name, email, or department"
                       class="admin-filter-control">
            </div>
            <div>
                <label for="department" class="block text-xs font-medium uppercase tracking-wide text-slate-500">Department</label>
                <select id="department" name="department" class="admin-filter-control">
                    <option value="">All</option>
                    @foreach ($departments as $value => $label)
                        <option value="{{ $value }}" @selected(request('department') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="role" class="block text-xs font-medium uppercase tracking-wide text-slate-500">Role</label>
                <select id="role" name="role" class="admin-filter-control">
                    <option value="">All</option>
                    @foreach ($roles as $roleOption)
                        <option value="{{ $roleOption->name }}" @selected(request('role') === $roleOption->name)>{{ $roleOption->label }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="flex flex-wrap items-center gap-3">
            <button type="submit" class="rounded-lg bg-brand px-4 py-2 text-sm font-medium text-white hover:bg-brand-hover">Apply</button>
            <a href="{{ route('users.index') }}" class="text-sm font-medium text-slate-600 hover:text-slate-900">Reset</a>
        </div>
    </form>

    <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-left text-sm">
                <thead class="bg-slate-50 text-xs font-semibold uppercase tracking-wide text-slate-500">
                <tr>
                    <th class="px-3 py-2">ID</th>
                    <th class="px-3 py-2">
                        @include('partials.table-sort-link', ['route' => 'users.index', 'column' => 'name', 'label' => 'Name', 'sortColumn' => $sortColumn, 'sortDirection' => $sortDirection])
                    </th>
                    <th class="px-3 py-2">
                        @include('partials.table-sort-link', ['route' => 'users.index', 'column' => 'email', 'label' => 'Email', 'sortColumn' => $sortColumn, 'sortDirection' => $sortDirection])
                    </th>
                    <th class="px-3 py-2">
                        @include('partials.table-sort-link', ['route' => 'users.index', 'column' => 'department', 'label' => 'Department', 'sortColumn' => $sortColumn, 'sortDirection' => $sortDirection])
                    </th>
                    <th class="px-3 py-2">Roles</th>
                    <th class="px-3 py-2">
                        @include('partials.table-sort-link', ['route' => 'users.index', 'column' => 'created_at', 'label' => 'Created', 'sortColumn' => $sortColumn, 'sortDirection' => $sortDirection])
                    </th>
                    <th class="px-3 py-2 text-right">Actions</th>
                </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                @forelse ($users as $user)
                    <tr class="hover:bg-slate-50/80">
                        <td class="whitespace-nowrap px-3 py-2 font-mono text-xs text-slate-700">{{ $user->id }}</td>
                        <td class="px-3 py-2 font-medium text-slate-900">{{ $user->name }}</td>
                        <td class="px-3 py-2 text-slate-600">{{ $user->email }}</td>
                        <td class="px-3 py-2 text-slate-600">{{ UserDepartment::label($user->department) }}</td>
                        <td class="px-3 py-2">
                            @forelse ($user->roles as $role)
                                <span class="mr-1 inline-flex rounded-full bg-slate-100 px-2 py-0.5 text-xs font-medium text-slate-700">{{ $role->label }}</span>
                            @empty
                                <span class="text-slate-400">—</span>
                            @endforelse
                        </td>
                        <td class="whitespace-nowrap px-3 py-2 text-xs text-slate-600">{{ $user->created_at?->format('Y-m-d H:i') }}</td>
                        <td class="whitespace-nowrap px-3 py-2 text-right text-xs">
                            @if (auth()->user()->hasPermission('users.update'))
                                <a href="{{ route('users.edit', $user) }}" class="font-medium text-slate-700 hover:text-slate-900">Edit</a>
                            @endif
                            @if (auth()->user()->hasPermission('users.delete') && $user->id !== auth()->id())
                                <span class="mx-1 text-slate-300">|</span>
                                <form action="{{ route('users.destroy', $user) }}" method="post" class="inline" onsubmit="return confirm('Delete this user?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="font-medium text-red-700 hover:text-red-900">Delete</button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-3 py-10 text-center text-sm text-slate-500">No users found.</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
        @if ($users->hasPages())
            <div class="border-t border-slate-200 bg-slate-50 px-4 py-3">{{ $users->links() }}</div>
        @endif
    </div>
@endsection

