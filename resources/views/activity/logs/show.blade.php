@extends('layouts.admin')

@section('title', 'Activity #'.$log->id)

@section('content')
    <div class="mb-6">
        <a href="{{ route('activity-logs.index', request()->only(['user', 'action', 'q', 'date_from', 'date_to'])) }}"
           class="text-sm font-medium text-slate-600 hover:text-slate-900">&larr; Back to activity log</a>
        <h1 class="mt-3 text-2xl font-semibold tracking-tight text-slate-900">Activity details</h1>
        <p class="mt-1 text-sm text-slate-600">{{ $log->description ?? $log->action }}</p>
    </div>

    <div class="grid gap-6 lg:grid-cols-2">
        <section class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
            <h2 class="text-sm font-semibold uppercase tracking-wide text-slate-500">Event</h2>
            <dl class="mt-4 space-y-3 text-sm">
                <div class="flex justify-between gap-4">
                    <dt class="text-slate-500">When</dt>
                    <dd class="text-right font-medium text-slate-900">{{ $log->created_at?->format('Y-m-d H:i:s') }}</dd>
                </div>
                <div class="flex justify-between gap-4">
                    <dt class="text-slate-500">Action</dt>
                    <dd class="font-mono text-slate-900">{{ $log->action }}</dd>
                </div>
                <div class="flex justify-between gap-4">
                    <dt class="text-slate-500">User</dt>
                    <dd class="text-right text-slate-900">
                        @if ($log->user)
                            {{ $log->user->name }}<br>
                            <span class="text-xs text-slate-500">{{ $log->user->email }}</span>
                        @else
                            —
                        @endif
                    </dd>
                </div>
                @if ($log->model)
                    <div class="flex justify-between gap-4">
                        <dt class="text-slate-500">Model</dt>
                        <dd class="text-right font-mono text-xs text-slate-900">{{ class_basename($log->model) }} #{{ $log->model_id }}</dd>
                    </div>
                @endif
                <div class="flex justify-between gap-4">
                    <dt class="text-slate-500">IP address</dt>
                    <dd class="font-mono text-slate-900">{{ $log->ip_address ?? '—' }}</dd>
                </div>
            </dl>
        </section>

        <section class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
            <h2 class="text-sm font-semibold uppercase tracking-wide text-slate-500">Browser</h2>
            <p class="mt-4 break-all text-sm text-slate-700">{{ $log->user_agent ?? '—' }}</p>
        </section>
    </div>

    @if (! empty($log->changes))
        <section class="mt-6 rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
            <h2 class="text-sm font-semibold uppercase tracking-wide text-slate-500">Changes</h2>
            <pre class="mt-4 max-h-[32rem] overflow-auto rounded-lg bg-slate-50 p-4 text-xs leading-relaxed text-slate-800">{{ json_encode($log->changes, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
        </section>
    @endif
@endsection
