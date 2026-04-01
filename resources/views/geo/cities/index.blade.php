@extends('layouts.admin')

@section('title', 'Cities')

@section('content')
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-semibold tracking-tight text-slate-900">Cities</h1>
            <p class="mt-1 text-sm text-slate-600">Manage cities linked to countries.</p>
        </div>
        <a href="{{ route('cities.create') }}"
           class="inline-flex items-center justify-center rounded-lg bg-slate-900 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-slate-800">
            Add City
        </a>
    </div>

    <form method="get" action="{{ route('cities.index') }}" class="mb-6 space-y-4 rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
        <input type="hidden" name="sort_by" value="{{ $sortColumn }}">
        <input type="hidden" name="sort_direction" value="{{ $sortDirection }}">
        <div class="grid gap-4 md:grid-cols-4">
            <div class="md:col-span-2">
                <label for="q" class="block text-xs font-medium uppercase tracking-wide text-slate-500">Search</label>
                <input type="search" id="q" name="q" value="{{ request('q') }}" placeholder="Arabic name or English name"
                       class="admin-filter-control">
            </div>
            <div>
                <label for="country_id" class="block text-xs font-medium uppercase tracking-wide text-slate-500">Country</label>
                <select id="country_id" name="country_id" class="admin-filter-control">
                    <option value="">All countries</option>
                    @foreach ($countries as $country)
                        <option value="{{ $country->id }}" @selected((string) request('country_id') === (string) $country->id)>
                            {{ $country->name_ar }} — {{ $country->name_en }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="status" class="block text-xs font-medium uppercase tracking-wide text-slate-500">Status</label>
                <select id="status" name="status" class="admin-filter-control">
                    <option value="">All</option>
                    <option value="active" @selected(request('status') === 'active')>Active</option>
                    <option value="inactive" @selected(request('status') === 'inactive')>Inactive</option>
                </select>
            </div>
        </div>
        <div class="flex flex-wrap items-center gap-3">
            <button type="submit" class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-slate-800">Apply</button>
            <a href="{{ route('cities.index') }}" class="text-sm font-medium text-slate-600 hover:text-slate-900">Reset</a>
        </div>
    </form>

    <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-left text-sm">
                <thead class="bg-slate-50 text-xs font-semibold uppercase tracking-wide text-slate-500">
                <tr>
                    <th class="px-3 py-2">ID</th>
                    <th class="px-3 py-2">
                        @include('partials.table-sort-link', ['route' => 'cities.index', 'column' => 'name_ar', 'label' => 'Arabic Name', 'sortColumn' => $sortColumn, 'sortDirection' => $sortDirection])
                    </th>
                    <th class="px-3 py-2">
                        @include('partials.table-sort-link', ['route' => 'cities.index', 'column' => 'name_en', 'label' => 'English Name', 'sortColumn' => $sortColumn, 'sortDirection' => $sortDirection])
                    </th>
                    <th class="px-3 py-2">Country</th>
                    <th class="px-3 py-2">Status</th>
                    <th class="px-3 py-2">
                        @include('partials.table-sort-link', ['route' => 'cities.index', 'column' => 'created_at', 'label' => 'Created At', 'sortColumn' => $sortColumn, 'sortDirection' => $sortDirection])
                    </th>
                    <th class="px-3 py-2 text-right">Actions</th>
                </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                @forelse ($cities as $city)
                    <tr class="hover:bg-slate-50/80">
                        <td class="whitespace-nowrap px-3 py-2 font-mono text-xs text-slate-700">{{ $city->id }}</td>
                        <td class="px-3 py-2" dir="auto">{{ $city->name_ar }}</td>
                        <td class="px-3 py-2">{{ $city->name_en }}</td>
                        <td class="px-3 py-2">{{ $city->country?->name_ar }} — {{ $city->country?->name_en }}</td>
                        <td class="whitespace-nowrap px-3 py-2">{{ ucfirst($city->status) }}</td>
                        <td class="whitespace-nowrap px-3 py-2 text-xs text-slate-600">{{ $city->created_at?->format('Y-m-d H:i') }}</td>
                        <td class="whitespace-nowrap px-3 py-2 text-right text-xs">
                            <a href="{{ route('cities.edit', $city) }}" class="font-medium text-slate-700 hover:text-slate-900">Edit</a>
                            <span class="mx-1 text-slate-300">|</span>
                            <form action="{{ route('cities.destroy', $city) }}" method="post" class="inline" onsubmit="return confirm('Delete this city?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="font-medium text-red-700 hover:text-red-900">Delete</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-3 py-10 text-center text-sm text-slate-500">No cities found.</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
        @if ($cities->hasPages())
            <div class="border-t border-slate-200 bg-slate-50 px-4 py-3">{{ $cities->links() }}</div>
        @endif
    </div>
@endsection
