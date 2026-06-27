@extends('layouts.admin')

@section('title', 'Schedule of Works')

@section('content')
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-semibold tracking-tight text-slate-900">Schedule of Works</h1>
            <p class="mt-1 text-sm text-slate-600">Manual schedule-of-works documents for printing.</p>
        </div>
        <div class="flex flex-wrap items-center gap-3">
            <a href="{{ route('dashboard') }}" class="text-sm font-medium text-slate-600 hover:text-slate-900">Dashboard</a>
            <a href="{{ route('schedule-of-works.create') }}"
               class="inline-flex items-center justify-center rounded-lg bg-slate-900 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-slate-800">
                Add Schedule
            </a>
        </div>
    </div>

    <form method="get" action="{{ route('schedule-of-works.index') }}" class="mb-6 rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
        <div class="flex flex-wrap items-end gap-4">
            <div class="min-w-[14rem] flex-1">
                <label for="q" class="block text-xs font-medium uppercase tracking-wide text-slate-500">Search</label>
                <input type="search" name="q" id="q" value="{{ request('q') }}"
                       placeholder="Document #, recipient, vendor"
                       class="admin-filter-control mt-1">
            </div>
            <button type="submit" class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-slate-800">Apply</button>
            @if (request()->hasAny(['q']))
                <a href="{{ route('schedule-of-works.index') }}" class="text-sm font-medium text-slate-600 hover:text-slate-900">Clear</a>
            @endif
        </div>
    </form>

    <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
        <table class="min-w-full divide-y divide-slate-200 text-sm">
            <thead class="bg-slate-50 text-left text-xs font-semibold uppercase tracking-wide text-slate-500">
            <tr>
                <th class="px-4 py-3">Document #</th>
                <th class="px-4 py-3">Recipient</th>
                <th class="px-4 py-3">Scope</th>
                <th class="px-4 py-3">Date</th>
                <th class="px-4 py-3">Language</th>
                <th class="px-4 py-3 text-right">Total</th>
                <th class="px-4 py-3 print:hidden"></th>
            </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
            @forelse ($schedules as $schedule)
                <tr>
                    <td class="px-4 py-3 font-mono text-slate-900">{{ $schedule->document_number }}</td>
                    <td class="px-4 py-3 text-slate-800">{{ $schedule->recipient_name }}</td>
                    <td class="px-4 py-3 text-slate-700">{{ $schedule->scopeTypesDisplay(false) ?: '—' }}</td>
                    <td class="px-4 py-3 text-slate-600">{{ $schedule->documented_at?->format('Y-m-d') }}</td>
                    <td class="px-4 py-3 text-slate-600 uppercase">{{ $schedule->print_locale }}</td>
                    <td class="px-4 py-3 text-right text-slate-900">{{ $schedule->formatMoneyAmount($schedule->total_price) }}</td>
                    <td class="px-4 py-3 text-right">
                        <div class="flex flex-wrap items-center justify-end gap-3">
                            <a href="{{ route('schedule-of-works.edit', $schedule) }}"
                               class="font-medium text-slate-700 hover:text-slate-900">Edit</a>
                            <a href="{{ route('schedule-of-works.print', ['schedule_of_work' => $schedule, 'locale' => $schedule->print_locale]) }}"
                               class="font-medium text-slate-700 hover:text-slate-900">Print</a>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="px-4 py-10 text-center text-slate-500">No schedules yet.</td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>

    @if ($schedules->hasPages())
        <div class="mt-4">{{ $schedules->links() }}</div>
    @endif
@endsection
