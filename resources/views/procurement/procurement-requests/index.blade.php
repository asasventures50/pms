@extends('layouts.admin')

@section('title', 'Procurement Requests')

@section('content')
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-semibold tracking-tight text-slate-900">Procurement Request</h1>
            <p class="mt-1 text-sm text-slate-600">Internal procurement request forms.</p>
        </div>
        @if (auth()->user()->hasPermission('procurement-requests.create'))
            <a href="{{ route('procurement-requests.create') }}"
               class="inline-flex items-center justify-center rounded-lg bg-slate-900 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-slate-800">
                Add request
            </a>
        @endif
    </div>

    <form method="get" action="{{ route('procurement-requests.index') }}" class="mb-6 rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
        <div class="grid gap-4 md:grid-cols-3">
            <div>
                <label for="q" class="block text-xs font-medium uppercase tracking-wide text-slate-500">Search</label>
                <input type="search" name="q" id="q" value="{{ request('q') }}"
                       placeholder="Request no., name, or department"
                       class="admin-filter-control">
            </div>
            <div>
                <label for="status" class="block text-xs font-medium uppercase tracking-wide text-slate-500">Status</label>
                <select name="status" id="status" class="admin-filter-control">
                    <option value="">All</option>
                    @foreach ($statuses as $s)
                        <option value="{{ $s->value }}" @selected(request('status') === $s->value)>{{ ucfirst($s->value) }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="mt-3 flex gap-3">
            <button type="submit" class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-slate-800">Apply</button>
            <a href="{{ route('procurement-requests.index') }}" class="text-sm font-medium text-slate-600 hover:text-slate-900">Reset</a>
        </div>
    </form>

    <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-left text-sm">
                <thead class="bg-slate-50 text-xs font-semibold uppercase tracking-wide text-slate-500">
                <tr>
                    <th class="px-3 py-2">Request No.</th>
                    <th class="px-3 py-2">Requestor</th>
                    <th class="px-3 py-2">Department</th>
                    <th class="px-3 py-2">Date</th>
                    <th class="px-3 py-2">Delivery date</th>
                    <th class="px-3 py-2">Status</th>
                    <th class="px-3 py-2 text-right">Actions</th>
                </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                @forelse ($procurementRequests as $request)
                    <tr class="hover:bg-slate-50/80">
                        <td class="whitespace-nowrap px-3 py-2 font-mono text-xs">{{ $request->request_number }}</td>
                        <td class="px-3 py-2">{{ $request->requestor_name ?? $request->creator?->name ?? '—' }}</td>
                        <td class="px-3 py-2">{{ $request->requestor_department ?: '—' }}</td>
                        <td class="whitespace-nowrap px-3 py-2 text-xs">{{ $request->requested_at?->format('Y-m-d') ?? '—' }}</td>
                        <td class="whitespace-nowrap px-3 py-2 text-xs">{{ $request->required_delivery_date?->format('Y-m-d') ?? '—' }}</td>
                        <td class="px-3 py-2"><span class="rounded-full bg-slate-100 px-2 py-0.5 text-xs font-medium">{{ ucfirst($request->status->value) }}</span></td>
                        <td class="whitespace-nowrap px-3 py-2 text-right text-xs">
                            <a href="{{ route('procurement-requests.show', $request) }}" class="font-medium text-slate-700 hover:text-slate-900">View</a>
                            @if (auth()->user()->hasPermission('procurement-requests.update'))
                                <span class="mx-1 text-slate-300">|</span>
                                <a href="{{ route('procurement-requests.edit', $request) }}" class="font-medium text-slate-700 hover:text-slate-900">Edit</a>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="px-3 py-10 text-center text-slate-500">No procurement requests found.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        @if ($procurementRequests->hasPages())
            <div class="border-t border-slate-200 bg-slate-50 px-4 py-3">{{ $procurementRequests->links() }}</div>
        @endif
    </div>
@endsection
