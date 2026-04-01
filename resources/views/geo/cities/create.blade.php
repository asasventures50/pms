@extends('layouts.admin')

@section('title', 'Create City')

@section('content')
    <div class="mb-6">
        <h1 class="text-2xl font-semibold tracking-tight text-slate-900">Create City</h1>
        <p class="mt-1 text-sm text-slate-600">Add a city and assign it to a country.</p>
    </div>

    <form method="post" action="{{ route('cities.store') }}" class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
        @csrf
        @include('geo.cities._form', ['city' => $city, 'countries' => $countries])
    </form>
@endsection
