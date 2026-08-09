@extends('layouts.admin')

@section('title', 'Activity Log')

@section('content')
    @php
        use Illuminate\Support\Carbon;
        use Illuminate\Support\Str;

        $filtersActive = collect($filters)->contains(fn ($value) => $value !== '');
        $reportQuery = request()->only(['user', 'action', 'q', 'date_from', 'date_to', 'time_from', 'time_to', 'sort_by', 'sort_direction']);
        $groupedLogs = $insightsService->groupByDay(collect($logs->items()));
    @endphp

    <div class="mb-8 flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div class="max-w-2xl">
            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-600">Audit trail</p>
            <h1 class="mt-2 text-3xl font-semibold tracking-tight text-slate-900">Activity Log</h1>
            <p class="mt-2 text-sm leading-relaxed text-slate-700">
                Monitor who did what — sign-ins and header-level actions on invoices, PRs, POs, vendors, and more.
            </p>
            @if ($filtersActive)
                <p class="mt-3 inline-flex rounded-full border border-slate-200 bg-slate-100 px-3 py-1 text-xs font-medium text-slate-800">
                    {{ $filterSummary }}
                </p>
            @endif
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('activity-logs.report', $reportQuery) }}"
               target="_blank" rel="noopener"
               class="inline-flex items-center justify-center rounded-xl bg-slate-900 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-slate-800">
                Print report
            </a>
            @if ($filtersActive)
                <a href="{{ route('activity-logs.index') }}"
                   class="inline-flex items-center justify-center rounded-xl border border-slate-300 px-4 py-2.5 text-sm font-medium text-slate-800 hover:bg-slate-50">
                    Clear filters
                </a>
            @endif
        </div>
    </div>

    <div class="mb-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-600">Matching events</p>
            <p class="mt-2 text-3xl font-semibold text-slate-900">{{ number_format($insights['total_events']) }}</p>
            <p class="mt-1 text-sm text-slate-700">In current filter scope</p>
        </div>
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-600">Active users</p>
            <p class="mt-2 text-3xl font-semibold text-slate-900">{{ number_format($insights['unique_users']) }}</p>
            <p class="mt-1 text-sm text-slate-700">Users with recorded activity</p>
        </div>
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-600">Time span</p>
            <p class="mt-2 text-2xl font-semibold text-slate-900">{{ $insights['total_span'] ?? '—' }}</p>
            <p class="mt-1 text-sm text-slate-700">
                @if ($insights['first_at'] && $insights['last_at'])
                    {{ $insights['first_at']->format('d M H:i') }} → {{ $insights['last_at']->format('d M H:i') }}
                @else
                    Not enough data yet
                @endif
            </p>
        </div>
        <div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-wide text-slate-600">Average gap</p>
            <p class="mt-2 text-2xl font-semibold text-slate-900">{{ $insights['average_gap'] ?? '—' }}</p>
            <p class="mt-1 text-sm text-slate-700">Between consecutive events</p>
        </div>
    </div>

    <form method="get" action="{{ route('activity-logs.index') }}" class="mb-6 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
        <div class="border-b border-slate-200 bg-slate-50 px-5 py-4">
            <h2 class="text-sm font-semibold text-slate-900">Filters</h2>
            <p class="text-sm text-slate-700">Narrow the feed by user, action, date, or time</p>
        </div>
        <div class="space-y-4 p-5">
            <input type="hidden" name="sort_by" value="{{ $sortColumn }}">
            <input type="hidden" name="sort_direction" value="{{ $sortDirection }}">
            <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                <div>
                    <label for="user" class="block text-xs font-medium uppercase tracking-wide text-slate-700">User</label>
                    <select id="user" name="user" class="admin-filter-control mt-1">
                        <option value="">All users</option>
                        @foreach ($users as $userOption)
                            <option value="{{ $userOption->id }}" @selected($filters['user'] === (string) $userOption->id)>
                                {{ $userOption->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="action" class="block text-xs font-medium uppercase tracking-wide text-slate-700">Action</label>
                    <select id="action" name="action" class="admin-filter-control mt-1">
                        <option value="">All actions</option>
                        @foreach ($actions as $actionOption)
                            <option value="{{ $actionOption }}" @selected($filters['action'] === $actionOption)>{{ $actionOption }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="date_from" class="block text-xs font-medium uppercase tracking-wide text-slate-700">From date</label>
                    <input type="date" id="date_from" name="date_from" value="{{ $filters['date_from'] }}" class="admin-filter-control mt-1">
                </div>
                <div>
                    <label for="time_from" class="block text-xs font-medium uppercase tracking-wide text-slate-700">From time</label>
                    <input type="time" id="time_from" name="time_from" value="{{ $filters['time_from'] }}" class="admin-filter-control mt-1">
                </div>
                <div>
                    <label for="date_to" class="block text-xs font-medium uppercase tracking-wide text-slate-700">To date</label>
                    <input type="date" id="date_to" name="date_to" value="{{ $filters['date_to'] }}" class="admin-filter-control mt-1">
                </div>
                <div>
                    <label for="time_to" class="block text-xs font-medium uppercase tracking-wide text-slate-700">To time</label>
                    <input type="time" id="time_to" name="time_to" value="{{ $filters['time_to'] }}" class="admin-filter-control mt-1">
                </div>
                <div class="md:col-span-2 xl:col-span-2">
                    <label for="q" class="block text-xs font-medium uppercase tracking-wide text-slate-700">Search</label>
                    <input type="search" id="q" name="q" value="{{ $filters['q'] }}"
                           placeholder="Description, action, or IP"
                           class="admin-filter-control mt-1">
                </div>
            </div>
            <div class="flex flex-wrap items-center gap-3">
                <button type="submit" class="rounded-xl bg-slate-900 px-4 py-2.5 text-sm font-semibold text-white hover:bg-slate-800">Apply filters</button>
                <a href="{{ route('activity-logs.index') }}" class="text-sm font-medium text-slate-600 hover:text-slate-900">Reset</a>
            </div>
        </div>
    </form>

    <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-lg font-semibold text-slate-900">Activity feed</h2>
            <p class="text-sm text-slate-700">
                Showing {{ $logs->firstItem() ?? 0 }}–{{ $logs->lastItem() ?? 0 }} of {{ number_format($logs->total()) }}
                · sorted by {{ $sortColumn }} ({{ $sortDirection }})
            </p>
        </div>
        <div class="flex flex-wrap gap-2 text-sm">
            @include('partials.table-sort-link', ['route' => 'activity-logs.index', 'column' => 'created_at', 'label' => 'Sort by time', 'sortColumn' => $sortColumn, 'sortDirection' => $sortDirection])
            @include('partials.table-sort-link', ['route' => 'activity-logs.index', 'column' => 'action', 'label' => 'Sort by action', 'sortColumn' => $sortColumn, 'sortDirection' => $sortDirection])
        </div>
    </div>

    @if ($logs->isEmpty())
        <div class="rounded-2xl border border-dashed border-slate-300 bg-white px-6 py-16 text-center shadow-sm">
            <p class="text-base font-medium text-slate-900">No activity recorded yet</p>
            <p class="mt-2 text-sm text-slate-700">Try widening your filters or check back after users start working.</p>
        </div>
    @else
        <div class="space-y-8">
            @foreach ($groupedLogs as $day => $dayLogs)
                @php
                    $dayDate = $day !== 'unknown' ? Carbon::parse($day) : null;
                @endphp
                <section>
                    <div class="mb-4 flex items-center gap-3">
                        <h3 class="text-sm font-semibold uppercase tracking-wide text-slate-700">
                            {{ $insightsService->dayHeading($dayDate) }}
                        </h3>
                        <div class="h-px flex-1 bg-slate-200"></div>
                        <span class="rounded-full bg-slate-100 px-2.5 py-0.5 text-xs font-medium text-slate-800">
                            {{ $dayLogs->count() }} {{ Str::plural('event', $dayLogs->count()) }}
                        </span>
                    </div>

                    <div class="relative space-y-3">
                        <div class="absolute bottom-0 left-5 top-0 w-px bg-gradient-to-b from-slate-300 via-slate-200 to-transparent"></div>

                        @foreach ($dayLogs as $log)
                            @php
                                $tone = $insightsService->actionTone($log->action);
                                $initial = Str::upper(Str::substr($log->user?->name ?? '?', 0, 1));
                            @endphp
                            <article class="relative pl-12">
                                <div @class([
                                    'absolute left-3 top-5 h-4 w-4 rounded-full border-2 border-white shadow-sm ring-2',
                                    'bg-emerald-500 ring-emerald-100' => $tone === 'create',
                                    'bg-sky-500 ring-sky-100' => $tone === 'update',
                                    'bg-rose-500 ring-rose-100' => $tone === 'delete',
                                    'bg-amber-500 ring-amber-100' => $tone === 'restore',
                                    'bg-violet-500 ring-violet-100' => $tone === 'login',
                                    'bg-slate-400 ring-slate-100' => in_array($tone, ['logout', 'default'], true),
                                ])></div>

                                <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm transition hover:border-slate-300 hover:shadow-md">
                                    <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                                        <div class="min-w-0 flex-1">
                                            <div class="flex flex-wrap items-center gap-2">
                                                @include('activity.logs._action-badge', [
                                                    'action' => $log->action,
                                                    'tone' => $tone,
                                                ])
                                                <span class="font-mono text-xs text-slate-600">{{ $log->created_at?->format('H:i:s') }}</span>
                                                @if (isset($pageGaps[$log->id]))
                                                    <span class="rounded-full bg-amber-50 px-2 py-0.5 text-xs font-medium text-amber-800">
                                                        + {{ $pageGaps[$log->id] }} since previous
                                                    </span>
                                                @endif
                                            </div>

                                            <p class="mt-3 text-sm font-medium text-slate-900">
                                                {{ $log->description ?? 'Activity recorded' }}
                                            </p>

                                            <div class="mt-3 flex flex-wrap items-center gap-3 text-sm text-slate-700">
                                                @if ($log->user)
                                                    <span class="inline-flex items-center gap-2">
                                                        <span class="inline-flex h-7 w-7 items-center justify-center rounded-full bg-slate-900 text-xs font-semibold text-white">
                                                            {{ $initial }}
                                                        </span>
                                                        <span>
                                                            <span class="font-medium text-slate-900">{{ $log->user->name }}</span>
                                                            <span class="block text-xs text-slate-600">{{ $log->user->email }}</span>
                                                        </span>
                                                    </span>
                                                @else
                                                    <span class="text-slate-600">Unknown user</span>
                                                @endif

                                                @if ($log->ip_address)
                                                    <span class="font-mono text-xs text-slate-600">IP {{ $log->ip_address }}</span>
                                                @endif
                                            </div>
                                        </div>

                                        <a href="{{ route('activity-logs.show', $log) }}"
                                           class="inline-flex shrink-0 items-center justify-center rounded-xl border border-slate-300 px-3 py-2 text-sm font-medium text-slate-800 hover:bg-slate-50">
                                            View details
                                        </a>
                                    </div>
                                </div>
                            </article>
                        @endforeach
                    </div>
                </section>
            @endforeach
        </div>

        @if ($logs->hasPages())
            <div class="mt-8 rounded-2xl border border-slate-200 bg-white px-4 py-3 shadow-sm">
                {{ $logs->links() }}
            </div>
        @endif
    @endif
@endsection
