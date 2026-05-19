@extends('layouts.admin')

@section('title', 'Edit Role')

@section('content')
    <div class="mb-6">
        <h1 class="text-2xl font-semibold tracking-tight text-slate-900">Edit Role</h1>
        <p class="mt-1 text-sm text-slate-600">{{ $role->label }}</p>
    </div>

    <form method="post" action="{{ route('roles.update', $role) }}" class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
        @csrf
        @method('PUT')
        @include('access.roles._form', [
            'role' => $role,
            'permissionGroups' => $permissionGroups,
            'permissionLabels' => $permissionLabels,
            'selectedPermissions' => $selectedPermissions,
            'isSystemRole' => $isSystemRole,
        ])
    </form>
@endsection
