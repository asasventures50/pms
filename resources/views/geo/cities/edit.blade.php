@extends('layouts.admin')

@section('title', 'Edit City')

@section('content')
    <div class="mb-6">
        <h1 class="text-2xl font-semibold tracking-tight text-slate-900">Edit City</h1>
        <p class="mt-1 text-sm text-slate-600">Update city details and country assignment.</p>
    </div>

    <form method="post" action="{{ route('cities.update', $city) }}" class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
        @csrf
        @method('PUT')
        @include('geo.cities._form', ['city' => $city, 'countries' => $countries])
    </form>
@endsection
