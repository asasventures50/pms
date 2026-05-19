@extends('layouts.admin')

@section('title', 'Create User')

@section('content')
    <div class="mb-6">
        <h1 class="text-2xl font-semibold tracking-tight text-slate-900">Create User</h1>
        <p class="mt-1 text-sm text-slate-600">Add a back-office account and assign roles.</p>
    </div>

    <form method="post" action="{{ route('users.store') }}" class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
        @csrf
        @include('access.users._form', ['user' => $user, 'roles' => $roles, 'selectedRoles' => $selectedRoles])
    </form>
@endsection
