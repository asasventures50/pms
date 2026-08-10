@extends('layouts.admin')

@section('title', 'Vendor duplicates')

@section('content')
    @php
        $tabs = [
            'phone' => 'Same phone',
            'email' => 'Same email',
            'name' => 'Similar name',
        ];
        $statusLabel = fn ($v) => \Illuminate\Support\Str::headline(str_replace('_', ' ', (string) ($v instanceof \BackedEnum ? $v->value : $v)));
    @endphp

    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <h1 class="text-2xl font-semibold tracking-tight text-slate-900">Possible duplicates</h1>
            <p class="mt-1 text-sm text-slate-600">
                Review vendors that share the same phone, email, or a similar name.
                Soft-deleted vendors are excluded. Phone/email matches are stronger than name-only.
            </p>
            <p class="mt-2 text-sm text-slate-700">
                <span class="font-medium">{{ $groupCount }}</span> groups ·
                <span class="font-medium">{{ $vendorCount }}</span> vendor rows in these groups
            </p>
        </div>
        <a href="{{ route('vendors.index') }}"
           class="inline-flex items-center justify-center rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-800 hover:bg-slate-50">
            Back to vendors
        </a>
    </div>

    <div class="mb-6 flex flex-wrap gap-2">
        @foreach ($tabs as $key => $label)
            <a href="{{ route('vendors.duplicates', ['by' => $key]) }}"
               class="inline-flex items-center rounded-lg px-3 py-1.5 text-sm font-medium
                    {{ $matchType === $key
                        ? 'bg-slate-900 text-white'
                        : 'border border-slate-300 bg-white text-slate-700 hover:bg-slate-50' }}">
                {{ $label }}
            </a>
        @endforeach
    </div>

    @if ($groups === [])
        <div class="rounded-xl border border-slate-200 bg-white px-6 py-12 text-center text-sm text-slate-500 shadow-sm">
            No duplicate groups found for this match type.
        </div>
    @else
        <div class="space-y-4">
            @foreach ($groups as $group)
                <section class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
                    <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-100 bg-slate-50 px-4 py-3">
                        <div>
                            <p class="text-sm font-semibold text-slate-900">{{ $group['match_label'] }}</p>
                            <p class="text-xs text-slate-500">{{ $group['vendors']->count() }} records</p>
                        </div>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-slate-200 text-sm">
                            <thead class="bg-white text-left text-xs font-medium uppercase tracking-wide text-slate-500">
                                <tr>
                                    <th class="px-4 py-2">Code</th>
                                    <th class="px-4 py-2">Name</th>
                                    <th class="px-4 py-2">Phone</th>
                                    <th class="px-4 py-2">WhatsApp</th>
                                    <th class="px-4 py-2">Email</th>
                                    <th class="px-4 py-2">Created by</th>
                                    <th class="px-4 py-2">Status</th>
                                    <th class="px-4 py-2 text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @foreach ($group['vendors'] as $vendor)
                                    <tr class="hover:bg-slate-50/80">
                                        <td class="whitespace-nowrap px-4 py-2 font-mono text-xs text-slate-700">{{ $vendor->vendor_code }}</td>
                                        <td class="px-4 py-2 text-slate-900" dir="auto">{{ $vendor->name }}</td>
                                        <td class="whitespace-nowrap px-4 py-2 text-slate-700">{{ $vendor->phone ?: '—' }}</td>
                                        <td class="whitespace-nowrap px-4 py-2 text-slate-700">{{ $vendor->whatsapp ?: '—' }}</td>
                                        <td class="px-4 py-2 text-slate-700">{{ $vendor->email ?: '—' }}</td>
                                        <td class="whitespace-nowrap px-4 py-2 text-slate-700">{{ $vendor->creator?->name ?? '—' }}</td>
                                        <td class="whitespace-nowrap px-4 py-2 text-slate-700">{{ $statusLabel($vendor->status) }}</td>
                                        <td class="whitespace-nowrap px-4 py-2 text-right">
                                            <a href="{{ route('vendors.show', $vendor) }}" class="font-medium text-slate-700 hover:text-slate-900">View</a>
                                            <span class="mx-1 text-slate-300">|</span>
                                            <a href="{{ route('vendors.edit', $vendor) }}" class="font-medium text-slate-700 hover:text-slate-900">Edit</a>
                                            @if ($canDelete)
                                                <span class="mx-1 text-slate-300">|</span>
                                                <form action="{{ route('vendors.destroy', $vendor) }}" method="post" class="inline"
                                                      onsubmit="return confirm('Soft-delete this vendor? Related documents keep their link.');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <input type="hidden" name="return" value="{{ request()->fullUrl() }}">
                                                    <button type="submit" class="font-medium text-red-700 hover:text-red-900">Delete</button>
                                                </form>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </section>
            @endforeach
        </div>
    @endif
@endsection
