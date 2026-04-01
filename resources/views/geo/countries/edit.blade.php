@extends('layouts.admin')

@section('title', 'Edit Country')

@section('content')
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-semibold tracking-tight text-slate-900">Edit Country</h1>
            <p class="mt-1 text-sm text-slate-600">Update country details and manage related cities.</p>
        </div>
    </div>

    <div class="grid gap-6 lg:grid-cols-2">
        <form method="post" action="{{ route('countries.update', $country) }}" class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
            @csrf
            @method('PUT')
            @include('geo.countries._form', ['country' => $country])
        </form>

        <section class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
            <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                <h2 class="text-base font-semibold text-slate-900">Cities in this country</h2>
                <a href="{{ route('cities.create', ['country_id' => $country->id]) }}"
                   class="rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-xs font-medium text-slate-700 hover:bg-slate-50">
                    Add City
                </a>
            </div>
            @if ($country->cities->isEmpty())
                <p class="mt-4 text-sm text-slate-500">No cities available.</p>
            @else
                <div class="mt-4 overflow-hidden rounded-lg border border-slate-200">
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
                            <tr>
                                <td class="px-3 py-2" dir="auto">{{ $city->name_ar }}</td>
                                <td class="px-3 py-2">{{ $city->name_en }}</td>
                                <td class="px-3 py-2">{{ ucfirst($city->status) }}</td>
                                <td class="px-3 py-2 text-right">
                                    <a href="{{ route('cities.edit', $city) }}" class="text-xs font-medium text-slate-700 hover:text-slate-900">Edit</a>
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
