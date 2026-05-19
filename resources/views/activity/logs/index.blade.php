@extends('layouts.admin')

@section('title', 'Activity Log')

@section('content')
    <div class="mb-6">
        <h1 class="text-2xl font-semibold tracking-tight text-slate-900">Activity Log</h1>
        <p class="mt-1 text-sm text-slate-600">Track user sign-ins and changes to vendors, purchase orders, and RFQs.</p>
    </div>

    <form method="get" action="{{ route('activity-logs.index') }}" class="mb-6 space-y-4 rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
        <input type="hidden" name="sort_by" value="{{ $sortColumn }}">
        <input type="hidden" name="sort_direction" value="{{ $sortDirection }}">
        <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
            <div>
                <label for="user" class="block text-xs font-medium uppercase tracking-wide text-slate-500">User</label>
                <select id="user" name="user" class="admin-filter-control">
                    <option value="">All users</option>
                    @foreach ($users as $userOption)
                        <option value="{{ $userOption->id }}" @selected((string) request('user') === (string) $userOption->id)>
                            {{ $userOption->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="action" class="block text-xs font-medium uppercase tracking-wide text-slate-500">Action</label>
                <select id="action" name="action" class="admin-filter-control">
                    <option value="">All actions</option>
                    @foreach ($actions as $actionOption)
                        <option value="{{ $actionOption }}" @selected(request('action') === $actionOption)>{{ $actionOption }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="date_from" class="block text-xs font-medium uppercase tracking-wide text-slate-500">From date</label>
                <input type="date" id="date_from" name="date_from" value="{{ request('date_from') }}" class="admin-filter-control">
            </div>
            <div>
                <label for="date_to" class="block text-xs font-medium uppercase tracking-wide text-slate-500">To date</label>
                <input type="date" id="date_to" name="date_to" value="{{ request('date_to') }}" class="admin-filter-control">
            </div>
            <div class="md:col-span-2 lg:col-span-4">
                <label for="q" class="block text-xs font-medium uppercase tracking-wide text-slate-500">Search</label>
                <input type="search" id="q" name="q" value="{{ request('q') }}"
                       placeholder="Description, action, or IP"
                       class="admin-filter-control">
            </div>
        </div>
        <div class="flex flex-wrap items-center gap-3">
            <button type="submit" class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-slate-800">Apply</button>
            <a href="{{ route('activity-logs.index') }}" class="text-sm font-medium text-slate-600 hover:text-slate-900">Reset</a>
        </div>
    </form>

    <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-left text-sm">
                <thead class="bg-slate-50 text-xs font-semibold uppercase tracking-wide text-slate-500">
                <tr>
                    <th class="px-3 py-2">
                        @include('partials.table-sort-link', ['route' => 'activity-logs.index', 'column' => 'created_at', 'label' => 'When', 'sortColumn' => $sortColumn, 'sortDirection' => $sortDirection])
                    </th>
                    <th class="px-3 py-2">User</th>
                    <th class="px-3 py-2">
                        @include('partials.table-sort-link', ['route' => 'activity-logs.index', 'column' => 'action', 'label' => 'Action', 'sortColumn' => $sortColumn, 'sortDirection' => $sortDirection])
                    </th>
                    <th class="px-3 py-2">Description</th>
                    <th class="px-3 py-2">IP</th>
                    <th class="px-3 py-2 text-right">Details</th>
                </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                @forelse ($logs as $log)
                    <tr class="hover:bg-slate-50/80">
                        <td class="whitespace-nowrap px-3 py-2 text-xs text-slate-600">{{ $log->created_at?->format('Y-m-d H:i:s') }}</td>
                        <td class="px-3 py-2">
                            @if ($log->user)
                                <span class="font-medium text-slate-900">{{ $log->user->name }}</span>
                                <span class="block text-xs text-slate-500">{{ $log->user->email }}</span>
                            @else
                                <span class="text-slate-400">—</span>
                            @endif
                        </td>
                        <td class="px-3 py-2">
                            <span class="inline-flex rounded-full bg-slate-100 px-2 py-0.5 font-mono text-xs text-slate-700">{{ $log->action }}</span>
                        </td>
                        <td class="max-w-xs truncate px-3 py-2 text-slate-700" title="{{ $log->description }}">{{ $log->description ?? '—' }}</td>
                        <td class="whitespace-nowrap px-3 py-2 font-mono text-xs text-slate-600">{{ $log->ip_address ?? '—' }}</td>
                        <td class="whitespace-nowrap px-3 py-2 text-right">
                            <a href="{{ route('activity-logs.show', $log) }}" class="font-medium text-slate-700 hover:text-slate-900">View</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-3 py-10 text-center text-sm text-slate-500">No activity recorded yet.</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
        @if ($logs->hasPages())
            <div class="border-t border-slate-200 bg-slate-50 px-4 py-3">{{ $logs->links() }}</div>
        @endif
    </div>
@endsection
