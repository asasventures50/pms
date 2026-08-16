@extends('layouts.admin')

@section('title', 'Map category import')

@php
    $impactLabel = function (array $impact, bool $includeReceipts = false): string {
        $parts = [];
        if ((int) ($impact['procurement_requests'] ?? 0) > 0) {
            $parts[] = $impact['procurement_requests'].' PR';
        }
        if ((int) ($impact['vendor_links'] ?? 0) > 0) {
            $parts[] = $impact['vendor_links'].' vendor';
        }
        if ((int) ($impact['brochures'] ?? 0) > 0) {
            $parts[] = $impact['brochures'].' brochure';
        }
        if ($includeReceipts && (int) ($impact['quick_receipts'] ?? 0) > 0) {
            $parts[] = $impact['quick_receipts'].' receipt';
        }

        return $parts === [] ? 'No linked records' : implode(' · ', $parts);
    };
    $impactTotal = function (array $impact, bool $includeReceipts = false): int {
        return (int) ($impact['procurement_requests'] ?? 0)
            + (int) ($impact['vendor_links'] ?? 0)
            + (int) ($impact['brochures'] ?? 0)
            + ($includeReceipts ? (int) ($impact['quick_receipts'] ?? 0) : 0);
    };
@endphp

@section('content')
    <div class="mb-6 flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
        <div>
            <h1 class="text-2xl font-semibold tracking-tight text-slate-900">Map category import</h1>
            <p class="mt-1 text-sm text-slate-600">
                File <span class="font-medium text-slate-800">{{ $filename }}</span>
                @if ($sheet)
                    · sheet <span class="font-medium text-slate-800">{{ $sheet }}</span>
                @endif
            </p>
            <p class="mt-2 max-w-3xl text-sm text-slate-600">The uploaded file becomes the new catalog. Map each current classification to its new place. Keep means the old row stays and linked documents are not moved.</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <form action="{{ route('categories.import.rebuild.cancel') }}" method="post">
                @csrf
                @method('DELETE')
                <button type="submit" class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-800 hover:bg-slate-50">
                    Discard
                </button>
            </form>
            <a href="{{ route('categories.import.form') }}" class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-800 hover:bg-slate-50">
                Back
            </a>
        </div>
    </div>

    <div class="mb-6 grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
        <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
            <p class="text-xs font-medium uppercase tracking-wide text-slate-500">New catalog</p>
            <p class="mt-1 text-lg font-semibold text-slate-900">{{ $totals['proposed_categories'] }} categories</p>
            <p class="text-xs text-slate-500">{{ $totals['proposed_subcategories'] }} subcategories · {{ $totals['proposed_new_categories'] }} new</p>
        </div>
        <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
            <p class="text-xs font-medium uppercase tracking-wide text-slate-500">Current catalog</p>
            <p class="mt-1 text-lg font-semibold text-slate-900">{{ $totals['current_categories'] }} categories</p>
            <p class="text-xs text-slate-500">{{ $totals['current_subcategories'] }} subcategories</p>
        </div>
        <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
            <p class="text-xs font-medium uppercase tracking-wide text-slate-500">Suggested maps</p>
            <p class="mt-1 text-lg font-semibold text-slate-900">{{ $totals['suggested_mappings'] }}</p>
            <p class="text-xs text-slate-500">{{ $totals['used_without_suggestion'] }} used rows need a manual map</p>
        </div>
        <div class="rounded-xl border border-amber-200 bg-amber-50 p-4 shadow-sm">
            <p class="text-xs font-medium uppercase tracking-wide text-amber-800">Records that can move</p>
            <p class="mt-1 text-lg font-semibold text-amber-950">{{ $totals['procurement_requests'] }} PR lines</p>
            <p class="text-xs text-amber-900">{{ $totals['vendor_links'] }} vendor · {{ $totals['brochures'] }} brochure · {{ $totals['quick_receipts'] }} receipt</p>
        </div>
    </div>

    <details class="mb-6 rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
        <summary class="cursor-pointer text-sm font-semibold text-slate-900">New catalog from the file ({{ $totals['proposed_categories'] }} categories)</summary>
        <div class="mt-4 overflow-x-auto">
            <table class="min-w-full text-left text-sm">
                <thead class="text-xs font-semibold uppercase tracking-wide text-slate-500">
                <tr>
                    <th class="py-2 pr-4">Category</th>
                    <th class="py-2 pr-4">English</th>
                    <th class="py-2 pr-4">Status</th>
                    <th class="py-2">Subcategories</th>
                </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                @foreach ($proposed as $proposedCategory)
                    <tr>
                        <td class="py-2 pr-4 align-top" dir="auto">{{ $proposedCategory['name_ar'] }}</td>
                        <td class="py-2 pr-4 align-top">{{ $proposedCategory['name_en'] }}</td>
                        <td class="py-2 pr-4 align-top">
                            @if (! empty($proposedCategory['already_exists']))
                                <span class="rounded-full bg-sky-50 px-2 py-0.5 text-xs font-medium text-sky-800">Exists — will update</span>
                            @else
                                <span class="rounded-full bg-emerald-50 px-2 py-0.5 text-xs font-medium text-emerald-800">Will be created</span>
                            @endif
                        </td>
                        <td class="py-2 align-top text-slate-600">
                            {{ $proposedCategory['subcategory_count'] }}
                            <span class="block text-xs text-slate-500">{{ collect($proposedCategory['subcategories'])->pluck('name_en')->take(4)->implode(', ') }}{{ count($proposedCategory['subcategories']) > 4 ? '…' : '' }}</span>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </details>

    <form action="{{ route('categories.import.rebuild.apply') }}" method="post" class="space-y-6" id="rebuild-map-form">
        @csrf

        <div class="flex flex-col gap-3 rounded-xl border border-slate-200 bg-white p-4 shadow-sm sm:flex-row sm:items-center sm:justify-between">
            <label class="block sm:max-w-sm sm:flex-1">
                <span class="text-xs font-medium uppercase tracking-wide text-slate-500">Filter current rows</span>
                <input type="search" id="rebuild-filter" placeholder="Search Arabic or English name"
                       class="admin-filter-control mt-1">
            </label>
            <label class="inline-flex items-center gap-2 text-sm text-slate-700">
                <input type="checkbox" id="rebuild-used-only" class="rounded border-slate-300">
                Show only rows with linked records
            </label>
        </div>

        @foreach ($current as $category)
            @php
                $catUsage = $impactTotal($category['impact'], true);
                $groupUsage = $catUsage + collect($category['subcategories'])->sum(fn ($sub) => $impactTotal($sub['impact']));
                $selectedCategory = old('category_map.'.$category['id'], $category['suggested_key'] ?? '');
            @endphp
            <section class="rebuild-group overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm"
                     data-usage="{{ $groupUsage }}"
                     data-search="{{ mb_strtolower($category['name_ar'].' '.$category['name_en'].' '.collect($category['subcategories'])->map(fn ($s) => $s['name_ar'].' '.$s['name_en'])->implode(' ')) }}">
                <div class="border-b border-slate-100 bg-slate-50 px-4 py-3 sm:px-5">
                    <div class="flex flex-col gap-3 lg:flex-row lg:items-start lg:justify-between">
                        <div>
                            <h2 class="text-base font-semibold text-slate-900" dir="auto">{{ $category['name_ar'] }}</h2>
                            <p class="text-sm text-slate-600">{{ $category['name_en'] }} <span class="font-mono text-xs text-slate-400">{{ $category['slug'] }}</span></p>
                            <p class="mt-1 text-xs {{ $catUsage > 0 ? 'text-amber-800' : 'text-slate-500' }}">
                                Category-only records: {{ $impactLabel($category['impact'], true) }}
                                @if ($category['suggested_key'])
                                    · suggested {{ $category['suggestion_score'] }}%
                                @endif
                            </p>
                        </div>
                        <div class="lg:w-96">
                            <label class="block text-xs font-medium uppercase tracking-wide text-slate-500">Map category-only records to</label>
                            <select name="category_map[{{ $category['id'] }}]" class="admin-filter-control mt-1">
                                <option value="">Keep current (do not move)</option>
                                @foreach ($proposed as $proposedCategory)
                                    <option value="{{ $proposedCategory['key'] }}" @selected($selectedCategory === $proposedCategory['key'])>
                                        {{ $proposedCategory['name_ar'] }} — {{ $proposedCategory['name_en'] }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                @if (count($category['subcategories']) === 0)
                    <p class="px-4 py-3 text-sm text-slate-500 sm:px-5">No subcategories.</p>
                @else
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-left text-sm">
                            <thead class="text-xs font-semibold uppercase tracking-wide text-slate-500">
                            <tr>
                                <th class="px-4 py-2 sm:px-5">Current subcategory</th>
                                <th class="px-4 py-2">Linked records</th>
                                <th class="px-4 py-2 sm:px-5">Map to</th>
                            </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                            @foreach ($category['subcategories'] as $sub)
                                @php
                                    $subUsage = $impactTotal($sub['impact']);
                                    $selectedSub = old('subcategory_map.'.$sub['id'], $sub['suggested_key'] ?? '');
                                @endphp
                                <tr class="rebuild-sub-row {{ $subUsage > 0 ? 'bg-amber-50/40' : '' }}" data-usage="{{ $subUsage }}">
                                    <td class="px-4 py-3 align-top sm:px-5">
                                        <p dir="auto" class="font-medium text-slate-900">{{ $sub['name_ar'] }}</p>
                                        <p class="text-slate-600">{{ $sub['name_en'] }}</p>
                                        @if ($sub['suggested_key'])
                                            <p class="mt-1 text-xs text-emerald-700">Suggested match {{ $sub['suggestion_score'] }}%</p>
                                        @elseif ($subUsage > 0)
                                            <p class="mt-1 text-xs text-amber-800">Used — map manually or keep</p>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 align-top text-xs {{ $subUsage > 0 ? 'font-medium text-amber-900' : 'text-slate-500' }}">
                                        {{ $impactLabel($sub['impact']) }}
                                    </td>
                                    <td class="px-4 py-3 align-top sm:px-5">
                                        <select name="subcategory_map[{{ $sub['id'] }}]" class="admin-filter-control">
                                            <option value="">Keep current (do not move)</option>
                                            @foreach ($proposed as $proposedCategory)
                                                <optgroup label="{{ $proposedCategory['name_en'] }}">
                                                    @foreach ($proposedCategory['subcategories'] as $proposedSub)
                                                        <option value="{{ $proposedSub['key'] }}" @selected($selectedSub === $proposedSub['key'])>
                                                            {{ $proposedSub['name_ar'] }} — {{ $proposedSub['name_en'] }}
                                                        </option>
                                                    @endforeach
                                                </optgroup>
                                            @endforeach
                                        </select>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </section>
        @endforeach

        <div class="sticky bottom-4 space-y-4 rounded-xl border border-slate-300 bg-white p-5 shadow-lg">
            <label class="flex items-start gap-3 text-sm text-slate-800">
                <input type="hidden" name="retire_mapped" value="0">
                <input type="checkbox" name="retire_mapped" value="1" class="mt-0.5 rounded border-slate-300" @checked(old('retire_mapped', '1') == '1')>
                <span>After records are moved, retire the mapped old categories/subcategories (soft delete). Unmapped rows stay in the catalog.</span>
            </label>
            <label class="flex items-start gap-3 text-sm text-slate-800">
                <input type="checkbox" name="confirm" value="1" required class="mt-0.5 rounded border-slate-300" @checked(old('confirm'))>
                <span>I understand this will create the new catalog and move mapped PR lines, vendor links, brochures, and quick receipts to the new classifications.</span>
            </label>
            @error('confirm')<p class="text-sm text-red-600">{{ $message }}</p>@enderror
            <div class="flex flex-wrap gap-3">
                <button type="submit" class="rounded-lg bg-brand px-5 py-2.5 text-sm font-medium text-white shadow-sm hover:bg-brand-hover">
                    Apply catalog &amp; mappings
                </button>
                <a href="{{ route('categories.import.form') }}" class="rounded-lg border border-slate-300 bg-white px-5 py-2.5 text-sm font-medium text-slate-800 hover:bg-slate-50">
                    Cancel
                </a>
            </div>
        </div>
    </form>
@endsection

@push('scripts')
<script>
    (function () {
        const filterInput = document.getElementById('rebuild-filter');
        const usedOnly = document.getElementById('rebuild-used-only');
        const groups = Array.from(document.querySelectorAll('.rebuild-group'));

        const apply = () => {
            const term = (filterInput?.value || '').trim().toLowerCase();
            const onlyUsed = Boolean(usedOnly?.checked);
            groups.forEach((group) => {
                const haystack = group.getAttribute('data-search') || '';
                const usage = Number(group.getAttribute('data-usage') || '0');
                const matchesTerm = term === '' || haystack.includes(term);
                const matchesUsage = !onlyUsed || usage > 0;
                group.classList.toggle('hidden', !(matchesTerm && matchesUsage));
            });
        };

        filterInput?.addEventListener('input', apply);
        usedOnly?.addEventListener('change', apply);
    })();
</script>
@endpush
