@extends('layouts.admin')

@section('title', 'Edit Project')

@section('content')
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-semibold tracking-tight text-slate-900">Edit Project</h1>
            <p class="mt-1 font-mono text-sm text-slate-600">{{ $project->code }}</p>
        </div>
        <div class="flex gap-3 text-sm">
            <a href="{{ route('projects.show', $project) }}" class="font-medium text-slate-600 hover:text-slate-900">View</a>
            <a href="{{ route('projects.index') }}" class="font-medium text-slate-600 hover:text-slate-900">Back to list</a>
        </div>
    </div>

    <form action="{{ route('projects.update', $project) }}" method="post" class="space-y-8">
        @csrf
        @method('PUT')
        @include('procurement.projects._form', ['mode' => 'edit', 'project' => $project])

        <div class="flex flex-wrap items-center gap-3">
            <button type="submit"
                    class="rounded-lg bg-brand px-5 py-2.5 text-sm font-medium text-white shadow-sm hover:bg-brand-hover">
                Update Project
            </button>
            <a href="{{ route('projects.show', $project) }}"
               class="rounded-lg border border-slate-300 bg-white px-5 py-2.5 text-sm font-medium text-slate-800 hover:bg-slate-50">
                Cancel
            </a>
        </div>
    </form>

    @if (auth()->user()->hasPermission('projects.update'))
        <form action="{{ route('projects.destroy', $project) }}" method="post" class="mt-8"
              onsubmit="return confirm('Delete this project and all its zones?');">
            @csrf
            @method('DELETE')
            <button type="submit"
                    class="rounded-lg border border-red-200 bg-white px-4 py-2 text-sm font-medium text-red-700 hover:bg-red-50">
                Delete project
            </button>
        </form>
    @endif
@endsection
