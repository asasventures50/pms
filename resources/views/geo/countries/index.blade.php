@extends('layouts.admin')

@section('title', 'Locations Management')

@section('content')
    @php($locationsRoute = \Illuminate\Support\Facades\Route::has('locations.index') ? 'locations.index' : 'countries.index')
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-semibold tracking-tight text-slate-900">Locations Management</h1>
            <p class="mt-1 text-sm text-slate-600">Manage countries and their cities from one page.</p>
        </div>
        <a href="{{ route('countries.create') }}"
           class="inline-flex items-center justify-center rounded-lg bg-brand px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-brand-hover">
            Add Country
        </a>
    </div>

    <form method="get" action="{{ route($locationsRoute) }}" class="mb-6 space-y-4 rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
        <input type="hidden" name="sort_by" value="{{ $sortColumn }}">
        <input type="hidden" name="sort_direction" value="{{ $sortDirection }}">
        <div class="grid gap-4 md:grid-cols-3">
            <div class="md:col-span-2">
                <label for="q" class="block text-xs font-medium uppercase tracking-wide text-slate-500">Search</label>
                <input type="search" id="q" name="q" value="{{ request('q') }}"
                       placeholder="Arabic name, English name, or ISO code"
                       class="admin-filter-control">
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
            <button type="submit" class="rounded-lg bg-brand px-4 py-2 text-sm font-medium text-white hover:bg-brand-hover">Apply</button>
            <a href="{{ route($locationsRoute) }}" class="text-sm font-medium text-slate-600 hover:text-slate-900">Reset</a>
        </div>
    </form>

    <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-left text-sm">
                <thead class="bg-slate-50 text-xs font-semibold uppercase tracking-wide text-slate-500">
                <tr>
                    <th class="px-3 py-2">ID</th>
                    <th class="px-3 py-2">
                        @include('partials.table-sort-link', ['route' => $locationsRoute, 'column' => 'name_ar', 'label' => 'Arabic Name', 'sortColumn' => $sortColumn, 'sortDirection' => $sortDirection])
                    </th>
                    <th class="px-3 py-2">
                        @include('partials.table-sort-link', ['route' => $locationsRoute, 'column' => 'name_en', 'label' => 'English Name', 'sortColumn' => $sortColumn, 'sortDirection' => $sortDirection])
                    </th>
                    <th class="px-3 py-2">ISO Code</th>
                    <th class="px-3 py-2">Flag</th>
                    <th class="px-3 py-2">Status</th>
                    <th class="px-3 py-2">Cities Count</th>
                    <th class="px-3 py-2">
                        @include('partials.table-sort-link', ['route' => $locationsRoute, 'column' => 'created_at', 'label' => 'Created At', 'sortColumn' => $sortColumn, 'sortDirection' => $sortDirection])
                    </th>
                    <th class="px-3 py-2 text-right">Actions</th>
                </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 bg-white">
                @forelse ($countries as $country)
                    @php($open = (string) request('country_id') === (string) $country->id)
                    @php($countryDeleteBlocked = $country->cities_count > 0 || (bool) ($country->vendor_locations_exists ?? false))
                    <tr class="hover:bg-slate-50/80">
                        <td class="whitespace-nowrap px-3 py-2 font-mono text-xs text-slate-700">{{ $country->id }}</td>
                        <td class="px-3 py-2" dir="auto">{{ $country->name_ar }}</td>
                        <td class="px-3 py-2">{{ $country->name_en }}</td>
                        <td class="whitespace-nowrap px-3 py-2 font-mono text-xs text-slate-600">{{ $country->iso_code ?: '—' }}</td>
                        <td class="whitespace-nowrap px-3 py-2 text-lg">{{ $country->flag_emoji ?: '—' }}</td>
                        <td class="whitespace-nowrap px-3 py-2">{{ ucfirst($country->status) }}</td>
                        <td class="whitespace-nowrap px-3 py-2">{{ $country->cities_count }}</td>
                        <td class="whitespace-nowrap px-3 py-2 text-xs text-slate-600">{{ $country->created_at?->format('Y-m-d H:i') }}</td>
                        <td class="whitespace-nowrap px-3 py-2 text-right text-xs">
                            <a href="{{ route($locationsRoute, array_merge(request()->query(), ['country_id' => $country->id])) }}" class="font-medium text-slate-700 hover:text-slate-900">Manage Cities</a>
                            <span class="mx-1 text-slate-300">|</span>
                            <a href="{{ route('countries.edit', $country) }}" class="font-medium text-slate-700 hover:text-slate-900">Edit</a>
                            <span class="mx-1 text-slate-300">|</span>
                            @if ($countryDeleteBlocked)
                                <span class="font-medium text-slate-400" title="Cannot delete while this country has cities or is used in vendor locations.">Delete</span>
                            @else
                                <form action="{{ route('countries.destroy', $country) }}" method="post" class="inline" onsubmit="return confirm('Delete this country?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="font-medium text-red-700 hover:text-red-900">Delete</button>
                                </form>
                            @endif
                        </td>
                    </tr>
                    <tr class="@if (! $open) hidden @endif bg-slate-50/50" id="country-cities-{{ $country->id }}">
                        <td colspan="9" class="px-4 py-4">
                            <div class="rounded-lg border border-slate-200 bg-white">
                                <div class="flex items-center justify-between border-b border-slate-100 px-4 py-3">
                                    <h3 class="text-sm font-semibold text-slate-900">
                                        Cities - {{ $country->name_ar }} / {{ $country->name_en }}
                                    </h3>
                                    <a href="{{ route('cities.create', ['country_id' => $country->id]) }}"
                                       class="rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-xs font-medium text-slate-700 hover:bg-slate-50">
                                        Add City
                                    </a>
                                </div>
                                @if ($country->cities->isEmpty())
                                    <p class="px-4 py-4 text-sm text-slate-500">No cities found for this country.</p>
                                @else
                                    <div class="overflow-x-auto">
                                        <table class="min-w-full divide-y divide-slate-200 text-sm">
                                            <thead class="bg-slate-50 text-xs font-semibold uppercase tracking-wide text-slate-500">
                                            <tr>
                                                <th class="px-3 py-2 text-left">Arabic Name</th>
                                                <th class="px-3 py-2 text-left">English Name</th>
                                                <th class="px-3 py-2 text-left">Status</th>
                                                <th class="px-3 py-2 text-right">Actions</th>
                                            </tr>
                                            </thead>
                                            <tbody class="divide-y divide-slate-100 bg-white">
                                            @foreach ($country->cities as $city)
                                                @php($cityDeleteBlocked = (bool) ($city->vendor_locations_exists ?? false))
                                                <tr>
                                                    <td class="px-3 py-2" dir="auto">{{ $city->name_ar }}</td>
                                                    <td class="px-3 py-2">{{ $city->name_en }}</td>
                                                    <td class="px-3 py-2">{{ ucfirst($city->status) }}</td>
                                                    <td class="px-3 py-2 text-right text-xs">
                                                        <a href="{{ route('cities.edit', $city) }}" class="font-medium text-slate-700 hover:text-slate-900">Edit City</a>
                                                        <span class="mx-1 text-slate-300">|</span>
                                                        @if ($cityDeleteBlocked)
                                                            <span class="font-medium text-slate-400" title="Cannot delete while this city is used in vendor locations.">Delete City</span>
                                                        @else
                                                            <form action="{{ route('cities.destroy', $city) }}" method="post" class="inline" onsubmit="return confirm('Delete this city?');">
                                                                @csrf
                                                                @method('DELETE')
                                                                <button type="submit" class="font-medium text-red-700 hover:text-red-900">Delete City</button>
                                                            </form>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="px-3 py-10 text-center text-sm text-slate-500">No countries found.</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
        @if ($countries->hasPages())
            <div class="border-t border-slate-200 bg-slate-50 px-4 py-3">{{ $countries->links() }}</div>
        @endif
    </div>
@endsection
