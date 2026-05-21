@extends('layouts.admin')

@section('title', 'Project')

@section('content')
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <h1 class="text-2xl font-semibold tracking-tight text-slate-900">{{ $project->name }}</h1>
            <p class="mt-1 font-mono text-sm text-slate-600">{{ $project->code }}</p>
        </div>
        <div class="flex flex-wrap gap-3">
            @if (auth()->user()->hasPermission('projects.update'))
                <a href="{{ route('projects.edit', $project) }}"
                   class="inline-flex items-center justify-center rounded-lg bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-slate-800">Edit</a>
            @endif
            <a href="{{ route('projects.index') }}"
               class="inline-flex items-center justify-center rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-800 hover:bg-slate-50">Back to list</a>
        </div>
    </div>

    <div class="space-y-6">
        <section class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="border-b border-slate-100 pb-2 text-base font-semibold text-slate-900">Details</h2>
            <dl class="mt-4 grid gap-4 sm:grid-cols-2">
                <div>
                    <dt class="text-xs font-medium uppercase tracking-wide text-slate-500">Code</dt>
                    <dd class="mt-1 font-mono text-sm text-slate-900">{{ $project->code }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-medium uppercase tracking-wide text-slate-500">Name</dt>
                    <dd class="mt-1 text-sm text-slate-900">{{ $project->name }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-medium uppercase tracking-wide text-slate-500">Status</dt>
                    <dd class="mt-1 capitalize text-sm text-slate-900">{{ $project->status }}</dd>
                </div>
                <div>
                    <dt class="text-xs font-medium uppercase tracking-wide text-slate-500">Updated</dt>
                    <dd class="mt-1 text-sm text-slate-900">{{ $project->updated_at?->format('Y-m-d H:i') }}</dd>
                </div>
            </dl>
        </section>

        <section class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="border-b border-slate-100 pb-2 text-base font-semibold text-slate-900">Zones</h2>
            @if ($project->zones->isEmpty())
                <p class="mt-3 text-sm text-slate-500">No zones defined.</p>
            @else
                <div class="mt-3 overflow-x-auto rounded-lg border border-slate-200">
                    <table class="min-w-full divide-y divide-slate-200 text-sm">
                        <thead class="bg-slate-50 text-xs font-semibold uppercase tracking-wide text-slate-500">
                        <tr>
                            <th class="px-3 py-2 text-left">Code</th>
                            <th class="px-3 py-2 text-left">Name</th>
                            <th class="px-3 py-2 text-left">Status</th>
                        </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                        @foreach ($project->zones as $zone)
                            <tr>
                                <td class="px-3 py-2 font-mono text-xs">{{ $zone->code }}</td>
                                <td class="px-3 py-2">{{ $zone->name }}</td>
                                <td class="px-3 py-2 capitalize">{{ $zone->status }}</td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </section>
    </div>
@endsection
