@extends('layouts.admin')

@section('title', 'Add Project')

@section('content')
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-semibold tracking-tight text-slate-900">Add Project</h1>
            <p class="mt-1 text-sm text-slate-600">Create a project and define its zones.</p>
        </div>
        <div class="flex gap-3 text-sm">
            <a href="{{ route('dashboard') }}" class="font-medium text-slate-600 hover:text-slate-900">Dashboard</a>
            <a href="{{ route('projects.index') }}" class="font-medium text-slate-600 hover:text-slate-900">Projects list</a>
        </div>
    </div>

    <form action="{{ route('projects.store') }}" method="post" class="space-y-8">
        @csrf
        @include('procurement.projects._form', [
            'mode' => 'create',
            'project' => $project,
            'nextProjectCode' => $nextProjectCode,
        ])

        <div class="flex flex-wrap items-center gap-3">
            <button type="submit"
                    class="rounded-lg bg-brand px-5 py-2.5 text-sm font-medium text-white shadow-sm hover:bg-brand-hover">
                Save Project
            </button>
            <a href="{{ route('projects.index') }}"
               class="rounded-lg border border-slate-300 bg-white px-5 py-2.5 text-sm font-medium text-slate-800 hover:bg-slate-50">
                Cancel
            </a>
        </div>
    </form>
@endsection
