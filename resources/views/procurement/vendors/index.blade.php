@extends('layouts.admin')

@section('title', 'Vendors')

@section('content')
    @php
        $vendorExportQuery = request()->except(['page', 'per_page']);
        $vendorFiltersActive = request()->filled('q')
            || request()->filled('status')
            || request()->filled('language')
            || request()->filled('category_id')
            || request()->filled('company_type')
            || request()->filled('coverage_type')
            || request()->filled('business_type')
            || request()->filled('country_id')
            || collect((array) request('subcategory_ids', []))->contains(fn ($v) => (int) $v > 0)
            || collect((array) request('city_ids', []))->contains(fn ($v) => (int) $v > 0);
        $vendorExportLabel = $vendorFiltersActive ? 'Export filtered Excel' : 'Export all Excel';
        $vendorListReturn = request()->getRequestUri();
    @endphp
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-semibold tracking-tight text-slate-900">Vendors</h1>
            <p class="mt-1 text-sm text-slate-600">Manage vendor records and procurement profiles.</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('vendors.create') }}"
               class="inline-flex items-center justify-center rounded-lg bg-slate-900 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-slate-800">
                Add Vendor
            </a>
            <button type="button"
                    id="copy-vendor-registration-link"
                    data-registration-url="{{ route('vendor-registration.create') }}"
                    class="inline-flex items-center justify-center rounded-lg border border-emerald-300 bg-emerald-50 px-4 py-2 text-sm font-medium text-emerald-900 hover:bg-emerald-100">
                Copy registration link
            </button>
            <a href="{{ route('vendors.export', $vendorExportQuery) }}"
               class="inline-flex items-center justify-center rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-800 hover:bg-slate-50">
                {{ $vendorExportLabel }}
            </a>
            <a href="{{ route('vendors.import.form') }}"
               class="inline-flex items-center justify-center rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-800 hover:bg-slate-50">
                Add Excel
            </a>
        </div>
    </div>

    @php
        $vendorPerPageOptions = [15, 30, 50, 75, 100];
        $vendorPerPage = (int) request('per_page', 15);
        if (! in_array($vendorPerPage, $vendorPerPageOptions, true)) {
            $vendorPerPage = 15;
        }
    @endphp
    <form method="get" action="{{ route('vendors.index') }}" class="mb-6 space-y-4 rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
        <input type="hidden" name="per_page" value="{{ $vendorPerPage }}">
        <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
            <div class="md:col-span-2">
                <label for="q" class="block text-xs font-medium uppercase tracking-wide text-slate-500">Search</label>
                <input type="search" name="q" id="q" value="{{ request('q') }}"
                       placeholder="Vendor code, name, email, or phone"
                       class="admin-filter-control">
            </div>
            <div>
                <label for="status" class="block text-xs font-medium uppercase tracking-wide text-slate-500">Status</label>
                <select name="status" id="status"
                        class="admin-filter-control">
                    <option value="">All</option>
                    @foreach (\App\Enums\Procurement\Vendors\VendorStatus::cases() as $case)
                        <option value="{{ $case->value }}" @selected(request('status') === $case->value)>{{ \Illuminate\Support\Str::headline($case->value) }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="language" class="block text-xs font-medium uppercase tracking-wide text-slate-500">Language</label>
                <select name="language" id="language"
                        class="admin-filter-control">
                    <option value="">All</option>
                    @foreach (\App\Enums\Procurement\Vendors\VendorLanguage::cases() as $case)
                        <option value="{{ $case->value }}" @selected(request('language') === $case->value)>{{ strtoupper($case->value) }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="company_type" class="block text-xs font-medium uppercase tracking-wide text-slate-500">Company Type</label>
                <select name="company_type" id="company_type" class="admin-filter-control">
                    <option value="">All</option>
                    @foreach (\App\Enums\Procurement\Vendors\CompanyType::cases() as $case)
                        <option value="{{ $case->value }}" @selected(request('company_type') === $case->value)>{{ \Illuminate\Support\Str::headline($case->value) }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="coverage_type" class="block text-xs font-medium uppercase tracking-wide text-slate-500">Coverage Type</label>
                <select name="coverage_type" id="coverage_type" class="admin-filter-control">
                    <option value="">All</option>
                    @foreach (\App\Enums\Procurement\Vendors\CoverageType::cases() as $case)
                        <option value="{{ $case->value }}" @selected(request('coverage_type') === $case->value)>{{ \Illuminate\Support\Str::headline($case->value) }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="business_type" class="block text-xs font-medium uppercase tracking-wide text-slate-500">Business Type</label>
                <select name="business_type" id="business_type" class="admin-filter-control">
                    <option value="">All</option>
                    @foreach (\App\Enums\Procurement\Vendors\VendorBusinessType::cases() as $case)
                        <option value="{{ $case->value }}" @selected(request('business_type') === $case->value)>{{ \Illuminate\Support\Str::headline($case->value) }}</option>
                    @endforeach
                </select>
            </div>
            @php
                $filterCategoryId = request('category_id');
                $selectedSubcategoryIds = collect((array) request('subcategory_ids', []))
                    ->map(fn ($v) => (string) (int) $v)
                    ->filter(fn (string $s) => $s !== '0')
                    ->unique()
                    ->values()
                    ->all();
                $filterSubOptions = $filterCategoryId
                    ? ($subcategoriesByCategory->get((int) $filterCategoryId) ?? collect())
                    : collect();
                if (! $filterCategoryId) {
                    $subcategoryButtonLabel = 'Select a category first';
                } elseif (count($selectedSubcategoryIds) === 0) {
                    $subcategoryButtonLabel = 'All subcategories';
                } elseif (count($selectedSubcategoryIds) === 1) {
                    $one = $filterSubOptions->first(function ($s) use ($selectedSubcategoryIds) {
                        return (string) $s['id'] === $selectedSubcategoryIds[0];
                    });
                    $subcategoryButtonLabel = $one
                        ? ($one['name_ar'].' — '.$one['name_en'])
                        : '1 subcategory selected';
                } else {
                    $subcategoryButtonLabel = count($selectedSubcategoryIds).' subcategories selected';
                }
            @endphp
            <div class="flex flex-col lg:col-span-2">
                <label for="vendor_filter_category_id" class="block text-xs font-medium uppercase tracking-wide text-slate-500">Category</label>
                <select name="category_id" id="vendor_filter_category_id"
                        class="admin-filter-control">
                    <option value="">All</option>
                    @foreach ($filterCategories as $cat)
                        <option value="{{ $cat->id }}" title="{{ $cat->name_en }}" @selected((string) request('category_id') === (string) $cat->id)>{{ $cat->name_ar }} — {{ $cat->name_en }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex flex-col lg:col-span-2" id="vendor_filter_subcategory_wrap">
                <span class="block text-xs font-medium uppercase tracking-wide text-slate-500" id="vendor_filter_subcategory_label">Subcategory</span>
                <div class="relative mt-1">
                    <button type="button"
                            id="vendor_filter_subcategory_btn"
                            @disabled(! $filterCategoryId)
                            aria-expanded="false"
                            aria-haspopup="listbox"
                            aria-labelledby="vendor_filter_subcategory_label"
                            class="admin-filter-dropdown-btn">
                        <span id="vendor_filter_subcategory_btn_label" class="min-w-0 flex-1 truncate">{{ $subcategoryButtonLabel }}</span>
                        <svg class="h-3.5 w-3.5 shrink-0 text-slate-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5"/>
                        </svg>
                    </button>
                    <div id="vendor_filter_subcategory_panel"
                         class="hidden absolute left-0 right-0 top-full z-30 mt-1 overflow-hidden rounded-lg border border-slate-200 bg-white shadow-lg ring-1 ring-black/5"
                         role="listbox">
                    <div id="vendor_filter_subcategory_list" class="max-h-52 overflow-y-auto py-1">
                        @forelse ($filterSubOptions as $s)
                            <label class="flex cursor-pointer items-start gap-2 px-3 py-2 text-sm hover:bg-slate-50">
                                <input type="checkbox" name="subcategory_ids[]" value="{{ $s['id'] }}"
                                       class="mt-0.5 rounded border-slate-300 text-slate-900 focus:ring-slate-500"
                                       data-sub-label="{{ e($s['name_ar'].' — '.$s['name_en']) }}"
                                    @checked(in_array((string) $s['id'], $selectedSubcategoryIds, true))>
                                <span class="text-slate-800" dir="auto">{{ $s['name_ar'] }} — {{ $s['name_en'] }}</span>
                            </label>
                        @empty
                            <p class="px-3 py-2 text-sm text-slate-500">No subcategories in this category.</p>
                        @endforelse
                    </div>
                    </div>
                </div>
            </div>
            <div class="md:col-span-2 lg:col-span-4">
                <p id="vendor_filter_subcategory_hint" class="text-xs leading-snug text-slate-500">
                    @if ($filterCategoryId)
                        Subcategory is optional. Vendors matching <span class="font-medium text-slate-600">any</span> selected subcategory are shown; leave empty to include all subcategories under the chosen category.
                    @else
                        Choose a category to enable the subcategory filter.
                    @endif
                </p>
            </div>

            {{-- Country / City filter --}}
            @php
                $filterCountryId = request('country_id');
                $selectedCityIds = collect((array) request('city_ids', []))
                    ->map(fn ($v) => (string) (int) $v)
                    ->filter(fn (string $s) => $s !== '0')
                    ->unique()
                    ->values()
                    ->all();
                $filterCityOptions = $filterCountryId
                    ? ($citiesByCountry->get((int) $filterCountryId) ?? collect())
                    : collect();
                if (! $filterCountryId) {
                    $cityButtonLabel = 'Select a country first';
                } elseif (count($selectedCityIds) === 0) {
                    $cityButtonLabel = 'All cities';
                } elseif (count($selectedCityIds) === 1) {
                    $oneCity = $filterCityOptions->first(fn ($c) => (string) $c['id'] === $selectedCityIds[0]);
                    $cityButtonLabel = $oneCity
                        ? ($oneCity['name_ar'] . ($oneCity['name_en'] ? ' — ' . $oneCity['name_en'] : ''))
                        : '1 city selected';
                } else {
                    $cityButtonLabel = count($selectedCityIds) . ' cities selected';
                }
            @endphp
            <div class="flex flex-col lg:col-span-2">
                <label for="vendor_filter_country_id" class="block text-xs font-medium uppercase tracking-wide text-slate-500">Country</label>
                <select name="country_id" id="vendor_filter_country_id" class="admin-filter-control">
                    <option value="">All</option>
                    @foreach ($filterCountries as $country)
                        <option value="{{ $country->id }}" @selected((string) request('country_id') === (string) $country->id)>
                            {{ $country->name_ar }}{{ $country->name_en ? ' — ' . $country->name_en : '' }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="flex flex-col lg:col-span-2" id="vendor_filter_city_wrap">
                <span class="block text-xs font-medium uppercase tracking-wide text-slate-500" id="vendor_filter_city_label">City</span>
                <div class="relative mt-1">
                    <button type="button"
                            id="vendor_filter_city_btn"
                            @disabled(! $filterCountryId)
                            aria-expanded="false"
                            aria-haspopup="listbox"
                            aria-labelledby="vendor_filter_city_label"
                            class="admin-filter-dropdown-btn">
                        <span id="vendor_filter_city_btn_label" class="min-w-0 flex-1 truncate">{{ $cityButtonLabel }}</span>
                        <svg class="h-3.5 w-3.5 shrink-0 text-slate-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5"/>
                        </svg>
                    </button>
                    <div id="vendor_filter_city_panel"
                         class="hidden absolute left-0 right-0 top-full z-30 mt-1 overflow-hidden rounded-lg border border-slate-200 bg-white shadow-lg ring-1 ring-black/5"
                         role="listbox">
                        <div id="vendor_filter_city_list" class="max-h-52 overflow-y-auto py-1">
                            @forelse ($filterCityOptions as $c)
                                <label class="flex cursor-pointer items-start gap-2 px-3 py-2 text-sm hover:bg-slate-50">
                                    <input type="checkbox" name="city_ids[]" value="{{ $c['id'] }}"
                                           class="mt-0.5 rounded border-slate-300 text-slate-900 focus:ring-slate-500"
                                           data-city-label="{{ e(($c['name_ar'] ?? '') . ($c['name_en'] ? ' — ' . $c['name_en'] : '')) }}"
                                        @checked(in_array((string) $c['id'], $selectedCityIds, true))>
                                    <span class="text-slate-800" dir="auto">
                                        {{ $c['name_ar'] }}{{ $c['name_en'] ? ' — ' . $c['name_en'] : '' }}
                                    </span>
                                </label>
                            @empty
                                <p class="px-3 py-2 text-sm text-slate-500">No cities in this country.</p>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
            <div class="md:col-span-2 lg:col-span-4">
                <p id="vendor_filter_city_hint" class="text-xs leading-snug text-slate-500">
                    @if ($filterCountryId)
                        City is optional. Vendors matching <span class="font-medium text-slate-600">any</span> selected city are shown; leave empty to include all cities under the chosen country.
                    @else
                        Choose a country to enable the city filter.
                    @endif
                </p>
            </div>
        </div>
        <div class="flex flex-wrap items-center gap-3">
            <button type="submit" class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-slate-800">Apply filters</button>
            <a href="{{ route('vendors.index') }}" class="text-sm font-medium text-slate-600 hover:text-slate-900">Reset</a>
        </div>
    </form>

    <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 text-left text-sm">
                <thead class="bg-slate-50 text-xs font-semibold uppercase tracking-wide text-slate-500">
                <tr>
                    <th class="px-3 py-3">Vendor Code</th>
                    <th class="px-3 py-3">Created by</th>
                    <th class="px-3 py-3">Vendor Name</th>
                    <th class="px-3 py-3">Language</th>
                    <th class="px-3 py-3">Country</th>
                    <th class="px-3 py-3">City</th>
                    <th class="px-3 py-3">Phone</th>
                    <th class="px-3 py-3">Email</th>
                    <th class="px-3 py-3 min-w-[8rem]">RFQ methods</th>
                    <th class="px-3 py-3">Status</th>
                    <th class="px-3 py-3 min-w-[10rem]">Primary Categories</th>
                    <th class="px-3 py-3 min-w-[8rem]">Business Types</th>
                    <th class="px-3 py-3">Brochures</th>
                    <th class="px-3 py-3 text-right">Actions</th>
                </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                @forelse ($vendors as $vendor)
                    @php
                        $primaryAssignments = $vendor->vendorCategories->where('is_primary', true);
                        $primaryLabels = $primaryAssignments->map(function ($vc) {
                            $ar = collect([$vc->category?->name_ar, $vc->subcategory?->name_ar])->filter()->join(' / ');
                            $en = collect([$vc->category?->name_en, $vc->subcategory?->name_en])->filter()->join(' / ');

                            return ['ar' => $ar, 'en' => $en];
                        })->filter(fn ($l) => ($l['ar'] ?? '') !== '' || ($l['en'] ?? '') !== '')->values();
                        $btLabels = $vendor->businessTypes->map(function ($row) {
                            $v = $row->business_type;
                            $val = $v instanceof \BackedEnum ? $v->value : (string) $v;
                            return \Illuminate\Support\Str::headline(str_replace('_', ' ', $val));
                        })->join(', ');
                    @endphp
                    <tr class="hover:bg-slate-50/80">
                        <td class="whitespace-nowrap px-3 py-2 font-mono text-xs text-slate-800">{{ $vendor->vendor_code }}</td>
                        <td class="whitespace-nowrap px-3 py-2 text-slate-700">{{ $vendor->creator?->name ?? '—' }}</td>
                        <td class="max-w-[14rem] truncate px-3 py-2 text-slate-900" title="{{ $vendor->name }}">{{ $vendor->name }}</td>
                        <td class="whitespace-nowrap px-3 py-2 text-slate-700">{{ strtoupper($vendor->language instanceof \BackedEnum ? $vendor->language->value : $vendor->language) }}</td>
                        @php
                            $primaryLoc = $vendor->locations->firstWhere('is_primary', true) ?? $vendor->locations->first();
                        @endphp
                        <td class="px-3 py-2 text-slate-700">{{ $primaryLoc?->country?->name ?? '—' }}</td>
                        <td class="px-3 py-2 text-slate-700">{{ $primaryLoc?->city?->name ?? '' }}</td>
                        <td class="whitespace-nowrap px-3 py-2 text-slate-700">{{ $vendor->phone ?? '—' }}</td>
                        <td class="max-w-[12rem] truncate px-3 py-2 text-slate-700" title="{{ $vendor->email }}">{{ $vendor->email ?? '—' }}</td>
                        <td class="px-3 py-2">
                            @if (is_array($vendor->rfq_method) && count($vendor->rfq_method) > 0)
                                <div class="flex max-w-[14rem] flex-wrap gap-1">
                                    @foreach ($vendor->rfq_method as $m)
                                        <span class="inline-flex rounded-full bg-slate-100 px-2 py-0.5 text-[11px] font-medium leading-tight text-slate-800">{{ \Illuminate\Support\Str::headline(str_replace('_', ' ', (string) $m)) }}</span>
                                    @endforeach
                                </div>
                            @else
                                <span class="text-xs text-slate-700">—</span>
                            @endif
                        </td>
                        <td class="whitespace-nowrap px-3 py-2">
                            @php $st = $vendor->status; $sv = $st instanceof \BackedEnum ? $st->value : $st; @endphp
                            <span class="inline-flex rounded-full bg-slate-100 px-2 py-0.5 text-xs font-medium text-slate-800">{{ \Illuminate\Support\Str::headline(str_replace('_', ' ', $sv)) }}</span>
                        </td>
                        <td class="px-3 py-2 text-xs text-slate-700">
                            @if ($primaryLabels->isEmpty())
                                —
                            @else
                                @foreach ($primaryLabels as $pl)
                                    <div class="@if (! $loop->last) mb-2 @endif">
                                        <div dir="auto">{{ $pl['ar'] ?: '—' }}</div>
                                        @if (filled($pl['en']))
                                            <div class="text-[11px] text-slate-500">{{ $pl['en'] }}</div>
                                        @endif
                                    </div>
                                @endforeach
                            @endif
                        </td>
                        <td class="px-3 py-2 text-xs text-slate-700">{{ $btLabels !== '' ? $btLabels : '—' }}</td>
                        <td class="whitespace-nowrap px-3 py-2 text-slate-700">{{ ($vendor->brochures_count ?? 0) > 0 ? 'Yes' : 'No' }}</td>
                        <td class="whitespace-nowrap px-3 py-2 text-right">
                            <a href="{{ route('vendors.show', ['vendor' => $vendor, 'return' => $vendorListReturn]) }}" class="text-sm font-medium text-slate-700 hover:text-slate-900">View</a>
                            <span class="mx-1 text-slate-300">|</span>
                            <a href="{{ route('vendors.edit', ['vendor' => $vendor, 'return' => $vendorListReturn]) }}" class="text-sm font-medium text-slate-700 hover:text-slate-900">Edit</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="14" class="px-3 py-10 text-center text-sm text-slate-500">No vendors found.</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
        @if ($vendors->total() > 0)
            <div class="border-t border-slate-200 bg-slate-50 px-4 py-3">
                {{ $vendors->links('pagination.vendors') }}
            </div>
        @endif
    </div>
@endsection

@push('scripts')
    <script type="application/json" id="vendor-filter-subcategories-by-category">@json($subcategoriesByCategory)</script>
    <script type="application/json" id="vendor-filter-cities-by-country">@json($citiesByCountry)</script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('[data-per-page-auto]').forEach(function (select) {
                select.addEventListener('change', function () {
                    const url = new URL(window.location.href);
                    url.searchParams.set('per_page', select.value);
                    url.searchParams.delete('page');
                    window.location.href = url.toString();
                });
            });

            const subByCat = JSON.parse(document.getElementById('vendor-filter-subcategories-by-category')?.textContent || '{}');
            const catSelect = document.getElementById('vendor_filter_category_id');
            const wrap = document.getElementById('vendor_filter_subcategory_wrap');
            const btn = document.getElementById('vendor_filter_subcategory_btn');
            const btnLabel = document.getElementById('vendor_filter_subcategory_btn_label');
            const panel = document.getElementById('vendor_filter_subcategory_panel');
            const listEl = document.getElementById('vendor_filter_subcategory_list');
            const hint = document.getElementById('vendor_filter_subcategory_hint');
            if (!catSelect || !wrap || !btn || !btnLabel || !panel || !listEl) {
                return;
            }

            function closePanel() {
                panel.classList.add('hidden');
                btn.setAttribute('aria-expanded', 'false');
            }

            function openPanel() {
                panel.classList.remove('hidden');
                btn.setAttribute('aria-expanded', 'true');
            }

            function updateButtonLabel() {
                if (btn.disabled) {
                    btnLabel.textContent = 'Select a category first';
                    return;
                }
                const boxes = listEl.querySelectorAll('input[type="checkbox"][name="subcategory_ids[]"]');
                const checked = Array.from(boxes).filter(function (c) {
                    return c.checked;
                });
                if (checked.length === 0) {
                    btnLabel.textContent = 'All subcategories';
                } else if (checked.length === 1) {
                    const t = checked[0].getAttribute('data-sub-label') || '1 subcategory selected';
                    btnLabel.textContent = t.length > 48 ? t.slice(0, 45) + '…' : t;
                } else {
                    btnLabel.textContent = checked.length + ' subcategories selected';
                }
            }

            function fillSubcategories() {
                const cid = catSelect.value;
                listEl.innerHTML = '';

                if (!cid) {
                    btn.disabled = true;
                    closePanel();
                    if (hint) {
                        hint.textContent = 'Choose a category to enable the subcategory filter.';
                    }
                    updateButtonLabel();
                    return;
                }

                btn.disabled = false;
                if (hint) {
                    hint.textContent = 'Subcategory is optional. Vendors matching any selected subcategory are shown; leave empty to include all subcategories under the chosen category.';
                }

                const list = subByCat[cid] || subByCat[String(cid)] || [];
                if (list.length === 0) {
                    const empty = document.createElement('p');
                    empty.className = 'px-3 py-2 text-sm text-slate-500';
                    empty.textContent = 'No subcategories in this category.';
                    listEl.appendChild(empty);
                    updateButtonLabel();
                    return;
                }
                list.forEach(function (item) {
                    const label = (item.name_ar || '') + ' — ' + (item.name_en || '');
                    const row = document.createElement('label');
                    row.className = 'flex cursor-pointer items-start gap-2 px-3 py-2 text-sm hover:bg-slate-50';
                    const cb = document.createElement('input');
                    cb.type = 'checkbox';
                    cb.name = 'subcategory_ids[]';
                    cb.value = String(item.id);
                    cb.className = 'mt-0.5 rounded border-slate-300 text-slate-900 focus:ring-slate-500';
                    cb.setAttribute('data-sub-label', label);
                    const span = document.createElement('span');
                    span.className = 'text-slate-800';
                    span.setAttribute('dir', 'auto');
                    span.textContent = label;
                    row.appendChild(cb);
                    row.appendChild(span);
                    listEl.appendChild(row);
                });

                listEl.querySelectorAll('input[type="checkbox"]').forEach(function (c) {
                    c.addEventListener('change', updateButtonLabel);
                });
                updateButtonLabel();
            }

            btn.addEventListener('click', function (e) {
                e.stopPropagation();
                if (btn.disabled) {
                    return;
                }
                if (panel.classList.contains('hidden')) {
                    openPanel();
                } else {
                    closePanel();
                }
            });

            document.addEventListener('click', function (e) {
                if (!wrap.contains(e.target)) {
                    closePanel();
                }
            });

            document.addEventListener('keydown', function (e) {
                if (e.key === 'Escape') {
                    closePanel();
                }
            });

            listEl.querySelectorAll('input[type="checkbox"]').forEach(function (c) {
                c.addEventListener('change', updateButtonLabel);
            });
            updateButtonLabel();

            catSelect.addEventListener('change', fillSubcategories);

            // ── Country / City filter ──────────────────────────────────────
            const citiesByCnt = JSON.parse(document.getElementById('vendor-filter-cities-by-country')?.textContent || '{}');
            const cntSelect   = document.getElementById('vendor_filter_country_id');
            const cityWrap    = document.getElementById('vendor_filter_city_wrap');
            const cityBtn     = document.getElementById('vendor_filter_city_btn');
            const cityBtnLbl  = document.getElementById('vendor_filter_city_btn_label');
            const cityPanel   = document.getElementById('vendor_filter_city_panel');
            const cityList    = document.getElementById('vendor_filter_city_list');
            const cityHint    = document.getElementById('vendor_filter_city_hint');

            if (cntSelect && cityWrap && cityBtn && cityBtnLbl && cityPanel && cityList) {

                function closeCityPanel() {
                    cityPanel.classList.add('hidden');
                    cityBtn.setAttribute('aria-expanded', 'false');
                }

                function openCityPanel() {
                    cityPanel.classList.remove('hidden');
                    cityBtn.setAttribute('aria-expanded', 'true');
                }

                function updateCityButtonLabel() {
                    if (cityBtn.disabled) {
                        cityBtnLbl.textContent = 'Select a country first';
                        return;
                    }
                    const boxes   = cityList.querySelectorAll('input[type="checkbox"][name="city_ids[]"]');
                    const checked = Array.from(boxes).filter(c => c.checked);
                    if (checked.length === 0) {
                        cityBtnLbl.textContent = 'All cities';
                    } else if (checked.length === 1) {
                        const t = checked[0].getAttribute('data-city-label') || '1 city selected';
                        cityBtnLbl.textContent = t.length > 48 ? t.slice(0, 45) + '…' : t;
                    } else {
                        cityBtnLbl.textContent = checked.length + ' cities selected';
                    }
                }

                function fillCities() {
                    const cid = cntSelect.value;
                    cityList.innerHTML = '';

                    if (!cid) {
                        cityBtn.disabled = true;
                        closeCityPanel();
                        if (cityHint) cityHint.innerHTML = 'Choose a country to enable the city filter.';
                        updateCityButtonLabel();
                        return;
                    }

                    cityBtn.disabled = false;
                    if (cityHint) cityHint.innerHTML = 'City is optional. Vendors matching <span class="font-medium text-slate-600">any</span> selected city are shown; leave empty to include all cities under the chosen country.';

                    const list = citiesByCnt[cid] || citiesByCnt[String(cid)] || [];
                    if (list.length === 0) {
                        const empty = document.createElement('p');
                        empty.className = 'px-3 py-2 text-sm text-slate-500';
                        empty.textContent = 'No cities in this country.';
                        cityList.appendChild(empty);
                        updateCityButtonLabel();
                        return;
                    }

                    list.forEach(function (item) {
                        const label = (item.name_ar || '') + (item.name_en ? ' — ' + item.name_en : '');
                        const row   = document.createElement('label');
                        row.className = 'flex cursor-pointer items-start gap-2 px-3 py-2 text-sm hover:bg-slate-50';
                        const cb  = document.createElement('input');
                        cb.type   = 'checkbox';
                        cb.name   = 'city_ids[]';
                        cb.value  = String(item.id);
                        cb.className = 'mt-0.5 rounded border-slate-300 text-slate-900 focus:ring-slate-500';
                        cb.setAttribute('data-city-label', label);
                        const span = document.createElement('span');
                        span.className = 'text-slate-800';
                        span.setAttribute('dir', 'auto');
                        span.textContent = label;
                        row.appendChild(cb);
                        row.appendChild(span);
                        cityList.appendChild(row);
                    });

                    cityList.querySelectorAll('input[type="checkbox"]').forEach(c => {
                        c.addEventListener('change', updateCityButtonLabel);
                    });
                    updateCityButtonLabel();
                }

                cityBtn.addEventListener('click', function (e) {
                    e.stopPropagation();
                    if (cityBtn.disabled) return;
                    cityPanel.classList.contains('hidden') ? openCityPanel() : closeCityPanel();
                });

                document.addEventListener('click', function (e) {
                    if (!cityWrap.contains(e.target)) closeCityPanel();
                });

                document.addEventListener('keydown', function (e) {
                    if (e.key === 'Escape') closeCityPanel();
                });

                cityList.querySelectorAll('input[type="checkbox"]').forEach(c => {
                    c.addEventListener('change', updateCityButtonLabel);
                });
                updateCityButtonLabel();

                cntSelect.addEventListener('change', fillCities);
            }

            const copyRegistrationBtn = document.getElementById('copy-vendor-registration-link');
            if (copyRegistrationBtn) {
                copyRegistrationBtn.addEventListener('click', async function () {
                    const url = copyRegistrationBtn.getAttribute('data-registration-url') || '';
                    if (!url) {
                        return;
                    }

                    const originalLabel = copyRegistrationBtn.textContent;
                    try {
                        await navigator.clipboard.writeText(url);
                        copyRegistrationBtn.textContent = 'Link copied';
                    } catch (error) {
                        window.prompt('Copy this registration link:', url);
                        copyRegistrationBtn.textContent = 'Copy registration link';
                        return;
                    }

                    setTimeout(function () {
                        copyRegistrationBtn.textContent = originalLabel;
                    }, 2000);
                });
            }
        });
    </script>
@endpush
