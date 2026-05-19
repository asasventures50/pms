@extends('layouts.admin')

@section('title', 'Edit User')

@section('content')
    <div class="mb-6">
        <h1 class="text-2xl font-semibold tracking-tight text-slate-900">Edit User</h1>
        <p class="mt-1 text-sm text-slate-600">{{ $user->email }}</p>
    </div>

    <form method="post" action="{{ route('users.update', $user) }}" class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
        @csrf
        @method('PUT')
        @include('access.users._form', ['user' => $user, 'roles' => $roles, 'selectedRoles' => $selectedRoles])
    </form>
@endsection
