@extends('layouts.admin')

@section('title', 'Create Country')

@section('content')
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-semibold tracking-tight text-slate-900">Create Country</h1>
            <p class="mt-1 text-sm text-slate-600">Add a new country for city and vendor location management.</p>
        </div>
    </div>

    <form method="post" action="{{ route('countries.store') }}" class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
        @csrf
        @include('geo.countries._form', ['country' => $country])
    </form>
@endsection
