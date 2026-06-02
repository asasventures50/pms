@extends('layouts.admin')

@section('title', 'RFQ general terms')

@section('content')
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-semibold tracking-tight text-slate-900">RFQ general terms</h1>
            <p class="mt-1 text-sm text-slate-600">General terms apply to every RFQ. Scope-specific terms can target one or more scope types and are added when any of those scopes appear on a line.</p>
        </div>
        <div class="flex flex-wrap gap-2">
            @if (auth()->user()->hasPermission('rfq-terms.manage'))
                <a href="{{ route('rfq-terms.create') }}"
                   class="inline-flex items-center justify-center rounded-lg bg-slate-900 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-slate-800">
                    Add term
                </a>
            @endif
            <a href="{{ route('rfq-terms.print', array_filter([
                'scope_type' => request('scope_type'),
                'q' => request('q'),
                'is_active' => request()->filled('is_active') ? request('is_active') : '1',
            ])) }}"
               class="inline-flex items-center justify-center rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-800 hover:bg-slate-50"
               target="_blank" rel="noopener">
                Print terms
            </a>
            <a href="{{ route('rfqs.index') }}"
               class="inline-flex items-center justify-center rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-800 hover:bg-slate-50">
                Back to RFQs
            </a>
        </div>
    </div>

    <form method="get" action="{{ route('rfq-terms.index') }}" class="mb-6 space-y-4 rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
        <div class="grid gap-4 md:grid-cols-4">
            <div class="md:col-span-2">
                <label for="q" class="block text-xs font-medium uppercase tracking-wide text-slate-500">Search</label>
                <input type="search" name="q" id="q" value="{{ request('q') }}"
                       placeholder="Arabic or English text"
                       class="admin-filter-control">
            </div>
            <div>
                <label for="scope_type" class="block text-xs font-medium uppercase tracking-wide text-slate-500">Scope type</label>
                <select name="scope_type" id="scope_type" class="admin-filter-control">
                    <option value="">All</option>
                    <option value="global" @selected(request('scope_type') === 'global')>General (all RFQs)</option>
                    @foreach ($scopeTypes as $scopeType)
                        <option value="{{ $scopeType }}" @selected(request('scope_type') === $scopeType)>{{ $scopeType }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="is_active" class="block text-xs font-medium uppercase tracking-wide text-slate-500">Status</label>
                <select name="is_active" id="is_active" class="admin-filter-control">
                    <option value="">All</option>
                    <option value="1" @selected(request('is_active') === '1')>Active</option>
                    <option value="0" @selected(request('is_active') === '0')>Inactive</option>
                </select>
            </div>
        </div>
        <div class="flex flex-wrap items-center gap-3">
            <button type="submit" class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-slate-800">Apply</button>
            <a href="{{ route('rfq-terms.print', array_filter([
                'scope_type' => request('scope_type'),
                'q' => request('q'),
                'is_active' => request()->filled('is_active') ? request('is_active') : '1',
            ])) }}"
               class="text-sm font-medium text-slate-600 hover:text-slate-900"
               target="_blank" rel="noopener">Print with these filters</a>
            <a href="{{ route('rfq-terms.index') }}" class="text-sm font-medium text-slate-600 hover:text-slate-900">Reset</a>
        </div>
    </form>

    <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-left text-sm">
                <thead class="bg-slate-50 text-xs font-semibold uppercase tracking-wide text-slate-500">
                <tr>
                    <th class="px-3 py-2 w-16">Order</th>
                    <th class="px-3 py-2 w-40">Scope types</th>
                    <th class="px-3 py-2">Term (AR / EN)</th>
                    <th class="px-3 py-2 w-24">Active</th>
                    <th class="px-3 py-2 w-32"></th>
                </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                @forelse ($terms as $term)
                    <tr>
                        <td class="px-3 py-3 font-mono text-slate-600">{{ $term->sort_order }}</td>
                        <td class="px-3 py-3 text-slate-700">{{ \App\Services\Procurement\Rfqs\RfqGeneralTermsService::scopeTypesLabel($term->scope_types) }}</td>
                        <td class="px-3 py-3 text-slate-900">
                            @if ($term->body_ar)
                                <p class="text-sm" dir="rtl">{{ $term->body_ar }}</p>
                            @endif
                            @if ($term->body_en)
                                <p class="mt-1 text-sm {{ $term->body_ar ? 'text-slate-600' : '' }}">{{ $term->body_en }}</p>
                            @endif
                            @if (! $term->body_ar && ! $term->body_en)
                                <span class="text-slate-500">—</span>
                            @endif
                        </td>
                        <td class="px-3 py-3">
                            @if ($term->is_active)
                                <span class="text-emerald-700">Yes</span>
                            @else
                                <span class="text-slate-500">No</span>
                            @endif
                        </td>
                        <td class="px-3 py-3 text-right">
                            @if (auth()->user()->hasPermission('rfq-terms.manage'))
                                <a href="{{ route('rfq-terms.edit', $term) }}" class="font-medium text-slate-900 hover:underline">Edit</a>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-3 py-8 text-center text-slate-500">No general terms yet.</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
        @if ($terms->hasPages())
            <div class="border-t border-slate-200 px-3 py-3">
                {{ $terms->links() }}
            </div>
        @endif
    </div>
@endsection
