@php
    use App\Enums\Procurement\Vendors\CompanyType;
    use App\Enums\Procurement\Vendors\CoverageType;
    use App\Enums\Procurement\Vendors\LeadTimeRange;
    use App\Enums\Procurement\Vendors\PaymentMethod;
    use App\Enums\Procurement\Vendors\PricingFrequency;
    use App\Enums\Procurement\Vendors\RfqMethod;
    use App\Enums\Procurement\Vendors\VendorBusinessType;
    use App\Enums\Procurement\Vendors\VendorLanguage;
    use App\Enums\Procurement\Vendors\VendorStatus;

    $v = $vendor ?? null;
    $isPublicRegister = $mode === 'public_register';
    $formLocale = $isPublicRegister ? app()->getLocale() : 'en';
    $vr = fn (string $key, array $replace = []) => __("vendor_registration.{$key}", $replace, $formLocale);
    $enumVr = function (string $group, $case) use ($vr) {
        $value = $case instanceof \BackedEnum ? $case->value : (string) $case;

        return $vr("enums.{$group}.{$value}");
    };
    $catalogLabel = function ($item) use ($isPublicRegister, $formLocale): string {
        $ar = trim((string) ($item->name_ar ?? ''));
        $en = trim((string) ($item->name_en ?? ''));

        if (! $isPublicRegister) {
            return $en !== '' ? ($ar !== '' ? $ar.' — '.$en : $en) : $ar;
        }

        if ($formLocale === 'ar') {
            return $ar !== '' ? $ar : $en;
        }

        return $en !== '' ? $en : $ar;
    };

    $categories = collect($categories)
        ->sort(fn ($a, $b) => strcasecmp($catalogLabel($a), $catalogLabel($b)))
        ->values();

    if ($mode === 'edit' && $v) {
        $bucket = [];
        foreach ($v->vendorCategories->sortBy('id') as $vc) {
            $cid = (string) $vc->category_id;
            if (! isset($bucket[$cid])) {
                $bucket[$cid] = [
                    'category_id' => $vc->category_id,
                    'subcategory_ids' => [],
                    'is_primary' => false,
                ];
            }
            if ($vc->subcategory_id !== null) {
                $bucket[$cid]['subcategory_ids'][] = $vc->subcategory_id;
            }
            if ($vc->is_primary) {
                $bucket[$cid]['is_primary'] = true;
            }
        }
        foreach ($bucket as &$bRow) {
            $bRow['subcategory_ids'] = array_values(array_unique(array_filter($bRow['subcategory_ids'])));
        }
        unset($bRow);
        $defaultCategoryRows = array_values($bucket);
        if (count($defaultCategoryRows) === 0) {
            $defaultCategoryRows = [['category_id' => '', 'subcategory_ids' => [], 'is_primary' => false]];
        }
    } else {
        $defaultCategoryRows = [['category_id' => '', 'subcategory_ids' => [], 'is_primary' => false]];
    }

    $categoryRows = old('categories', $defaultCategoryRows);

    $countriesCollection = $countries ?? collect();
    $defaultCountryId = $defaultCountryId ?? null;
    $defaultCityId = $defaultCityId ?? null;
    $suggestedVendorCode = $suggestedVendorCode ?? '';

    if ($mode === 'edit' && $v && $v->locations->isNotEmpty()) {
        $defaultLocationRows = $v->locations->sortBy(fn ($loc) => [$loc->is_primary ? 0 : 1, $loc->id])->values()->map(fn ($loc) => [
            'id' => $loc->id,
            'country_id' => $loc->country_id,
            'city_id' => $loc->city_id,
            'address' => $loc->address ?? '',
            'phone' => $loc->phone ?? '',
            'whatsapp' => $loc->whatsapp ?? '',
            'notes' => $loc->notes ?? '',
            'is_primary' => $loc->is_primary,
        ])->all();
    } else {
        $defaultLocationRows = [];
    }

    $locationRows = old('locations', $defaultLocationRows);

    $primaryLocationIdx = old('primary_location_index');
    if ($primaryLocationIdx === null) {
        $locPrimaryFound = false;
        foreach ($locationRows as $i => $lr) {
            if (is_array($lr) && ! empty($lr['is_primary'])) {
                $primaryLocationIdx = $i;
                $locPrimaryFound = true;
                break;
            }
        }
        if (! $locPrimaryFound) {
            $primaryLocationIdx = 0;
        }
    }

    $selectedBusinessTypes = old('business_types', $v?->businessTypes->pluck('business_type')->map(fn ($e) => $e instanceof \BackedEnum ? $e->value : $e)->all() ?? []);

    $selectedRfqMethods = old('rfq_method', $v?->rfq_method);
    if (! is_array($selectedRfqMethods)) {
        $selectedRfqMethods = is_string($selectedRfqMethods) && $selectedRfqMethods !== ''
            ? [$selectedRfqMethods]
            : [];
    }
    $selectedRfqMethods = array_values(array_unique(array_filter(
        $selectedRfqMethods,
        static fn ($item) => is_string($item) && $item !== ''
    )));

    $citiesByCountry = $countriesCollection->mapWithKeys(fn ($c) => [
        $c->id => $c->cities->map(fn ($city) => [
            'id' => $city->id,
            'name' => trim(($city->name_ar ?? '').' — '.($city->name_en ?? '')),
        ])->values(),
    ]);

    $subcategoriesByCategory = $categories->mapWithKeys(fn ($c) => [
        $c->id => $c->subcategories
            ->sort(fn ($a, $b) => strcasecmp($catalogLabel($a), $catalogLabel($b)))
            ->map(fn ($s) => [
                'id' => $s->id,
                'name_ar' => $s->name_ar,
                'name_en' => $s->name_en,
            ])->values(),
    ]);

    $categoryInitialForJs = collect($categoryRows)->map(function ($row, $index) {
        $subIds = old("categories.$index.subcategory_ids", $row['subcategory_ids'] ?? []);
        if (! is_array($subIds)) {
            $subIds = [];
        }

        return [
            'index' => $index,
            'category_id' => old("categories.$index.category_id", $row['category_id'] ?? ''),
            'subcategory_ids' => array_values(array_map('intval', $subIds)),
        ];
    })->values();

    $locationInitialForJs = collect($locationRows)->map(function ($row, $index) {
        return [
            'index' => $index,
            'country_id' => old("locations.$index.country_id", $row['country_id'] ?? ''),
            'city_id' => old("locations.$index.city_id", $row['city_id'] ?? ''),
        ];
    })->values();

    $oldBrochureRows = (array) old('brochure_rows', []);
    $brochureNewRowCount = max(1, count($oldBrochureRows));

    $brochureInitialForJs = collect(range(0, max(0, $brochureNewRowCount - 1)))->map(function ($bi) {
        return [
            'category_id' => old('brochure_rows.'.$bi.'.category_id', ''),
            'subcategory_id' => old('brochure_rows.'.$bi.'.subcategory_id', ''),
        ];
    })->values();
@endphp

<input type="hidden" name="business_types_sync" value="1">
@if ($mode === 'edit')
    <input type="hidden" name="locations_sync" value="1">
    <input type="hidden" name="categories_sync" value="1">
@endif
<div id="brochure-removal-stash" class="hidden" aria-hidden="true"></div>

{{-- 1. Basic Information --}}
<section class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
    <h2 class="border-b border-slate-100 pb-3 text-base font-semibold text-slate-900">{{ $vr('sections.basic_information') }}</h2>
    <div class="mt-4 grid gap-4 md:grid-cols-2">
        @unless ($isPublicRegister)
            <div>
                <label for="vendor_code" class="block text-xs font-medium uppercase tracking-wide text-slate-500">{{ $vr('fields.vendor_code') }}</label>
                <input type="text" name="vendor_code" id="vendor_code"
                       value="{{ old('vendor_code', $mode === 'create' ? $suggestedVendorCode : ($v?->vendor_code ?? '')) }}"
                       autocomplete="off"
                       placeholder="{{ $vr('hints.vendor_code_placeholder') }}"
                       class="admin-filter-control @error('vendor_code') border-red-500 @enderror">
                <p class="mt-1 text-xs text-slate-500">{{ $vr('hints.vendor_code') }}</p>
                @error('vendor_code')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
        @endunless
        <div>
            <label for="name" class="block text-xs font-medium uppercase tracking-wide text-slate-500">{{ $vr('fields.vendor_name') }} <span class="text-red-600">*</span></label>
            <input type="text" name="name" id="name" required
                   value="{{ old('name', $v?->name ?? '') }}"
                   class="admin-filter-control @error('name') border-red-500 @enderror">
            @error('name')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>
        <div>
            <label for="language" class="block text-xs font-medium uppercase tracking-wide text-slate-500">{{ $vr('fields.language') }} <span class="text-red-600">*</span></label>
            <select name="language" id="language" required
                    class="admin-filter-control @error('language') border-red-500 @enderror">
                @foreach (VendorLanguage::cases() as $case)
                    <option value="{{ $case->value }}" @selected(old('language', ($v?->language instanceof \BackedEnum) ? $v->language->value : ($v?->language ?? 'en')) === $case->value)>{{ strtoupper($case->value) }}</option>
                @endforeach
            </select>
            @error('language')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>
        @unless ($isPublicRegister)
            <div>
                <label for="status" class="block text-xs font-medium uppercase tracking-wide text-slate-500">{{ $vr('fields.status') }} <span class="text-red-600">*</span></label>
                <select name="status" id="status" required
                        class="admin-filter-control @error('status') border-red-500 @enderror">
                    @foreach (VendorStatus::cases() as $case)
                        <option value="{{ $case->value }}" @selected(old('status', ($v?->status instanceof \BackedEnum) ? $v->status->value : ($v?->status ?? 'pending_review')) === $case->value)>{{ $enumVr('vendor_status', $case) }}</option>
                    @endforeach
                </select>
                @error('status')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
        @endunless
        <div class="md:col-span-2">
            <label for="description" class="block text-xs font-medium uppercase tracking-wide text-slate-500">{{ $vr('fields.description') }}</label>
            <textarea name="description" id="description" rows="3"
                      class="admin-form-textarea @error('description') border-red-500 @enderror">{{ old('description', $v?->description ?? '') }}</textarea>
            @error('description')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>
        @unless ($isPublicRegister)
            <div class="md:col-span-2">
                <label for="notes" class="block text-xs font-medium uppercase tracking-wide text-slate-500">{{ $vr('fields.notes') }}</label>
                <textarea name="notes" id="notes" rows="2"
                          class="admin-form-textarea @error('notes') border-red-500 @enderror">{{ old('notes', $v?->notes ?? '') }}</textarea>
                @error('notes')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
        @endunless
    </div>
</section>

{{-- 2. Branch locations --}}
<section class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
    <h2 class="border-b border-slate-100 pb-3 text-base font-semibold text-slate-900">{{ $vr('sections.branch_locations') }}</h2>
    @if ($countriesCollection->isEmpty())
        <div class="mt-4 rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-950">
            <p class="font-medium">{{ $vr('hints.no_countries') }}</p>
            <p class="mt-1 text-amber-900/90">{{ $vr('hints.no_countries_help') }}</p>
            <p class="mt-2 font-mono text-xs text-amber-950/90">php artisan db:seed --class=Database\Seeders\Geo\CountryCitySeeder</p>
            <p class="mt-2 text-xs text-amber-900/80">{!! $vr('hints.no_countries_env', ['env' => '<span class="font-mono">.env</span>', 'connection' => '<span class="font-mono">DB_CONNECTION=mysql</span>']) !!}</p>
        </div>
    @endif
    <p class="mt-4 text-xs text-slate-500">{{ $vr('hints.branch_locations') }}</p>
    @error('locations')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
    <div id="vendor-location-rows" class="mt-4 space-y-4">
        @foreach ($locationRows as $li => $locRow)
            @php
                $locCountryId = old("locations.$li.country_id", $locRow['country_id'] ?? '');
                $locCityId = old("locations.$li.city_id", $locRow['city_id'] ?? '');
            @endphp
            <div class="vendor-location-row rounded-lg border border-slate-200 bg-slate-50/50 p-4" data-location-index="{{ $li }}"
                @if (! empty($locRow['id'] ?? null)) data-persisted-location-id="{{ $locRow['id'] }}" @endif>
                <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-200/80 pb-3">
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="vendor-location-branch-label text-xs font-semibold uppercase tracking-wide text-slate-500">{{ $vr('buttons.branch', ['num' => $li + 1]) }}</span>
                        <button type="button" data-remove-vendor-location
                                class="rounded border border-red-200 px-2 py-0.5 text-xs font-medium text-red-600 hover:bg-red-50">
                            {{ $vr('buttons.remove') }}
                        </button>
                    </div>
                    <label class="inline-flex cursor-pointer items-center gap-2 text-sm text-slate-800">
                        <input type="radio" name="primary_location_index" value="{{ $li }}"
                               class="border-slate-300 text-slate-900 focus:ring-slate-500"
                               @checked((int) $primaryLocationIdx === (int) $li)>
                        {{ $vr('buttons.primary_branch') }}
                    </label>
                </div>
                <div class="mt-4 grid gap-4 md:grid-cols-2">
                    <div>
                        <label class="block text-xs font-medium uppercase tracking-wide text-slate-500">{{ $vr('fields.country') }}</label>
                        <select name="locations[{{ $li }}][country_id]" data-vendor-location-country
                                class="admin-filter-control !mt-1 @error('locations.'.$li.'.country_id') border-red-500 @enderror">
                            <option value="">—</option>
                            @foreach ($countriesCollection as $country)
                                <option value="{{ $country->id }}" @selected((string) $locCountryId === (string) $country->id)>
                                    {{ $country->flag_emoji ? $country->flag_emoji.' ' : '' }}{{ $country->name_ar }} — {{ $country->name_en }}
                                </option>
                            @endforeach
                        </select>
                        @error('locations.'.$li.'.country_id')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-xs font-medium uppercase tracking-wide text-slate-500">{{ $vr('fields.city_optional') }}</label>
                        <select name="locations[{{ $li }}][city_id]" data-vendor-location-city
                                class="admin-filter-control !mt-0 @error('locations.'.$li.'.city_id') border-red-500 @enderror">
                            <option value="">{{ $vr('hints.select_city_optional') }}</option>
                        </select>
                        @error('locations.'.$li.'.city_id')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-xs font-medium uppercase tracking-wide text-slate-500">{{ $vr('fields.address') }}</label>
                        <textarea name="locations[{{ $li }}][address]" rows="2"
                                  class="admin-form-textarea @error('locations.'.$li.'.address') border-red-500 @enderror">{{ old("locations.$li.address", $locRow['address'] ?? '') }}</textarea>
                        @error('locations.'.$li.'.address')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-xs font-medium uppercase tracking-wide text-slate-500">{{ $vr('fields.phone') }}</label>
                        <input type="text" name="locations[{{ $li }}][phone]"
                               value="{{ old("locations.$li.phone", $locRow['phone'] ?? '') }}"
                               class="admin-filter-control @error('locations.'.$li.'.phone') border-red-500 @enderror">
                        @error('locations.'.$li.'.phone')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-xs font-medium uppercase tracking-wide text-slate-500">{{ $vr('fields.whatsapp') }}</label>
                        <input type="text" name="locations[{{ $li }}][whatsapp]"
                               value="{{ old("locations.$li.whatsapp", $locRow['whatsapp'] ?? '') }}"
                               class="admin-filter-control @error('locations.'.$li.'.whatsapp') border-red-500 @enderror">
                        @error('locations.'.$li.'.whatsapp')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-xs font-medium uppercase tracking-wide text-slate-500">{{ $vr('fields.notes') }}</label>
                        <textarea name="locations[{{ $li }}][notes]" rows="2"
                                  class="admin-form-textarea @error('locations.'.$li.'.notes') border-red-500 @enderror">{{ old("locations.$li.notes", $locRow['notes'] ?? '') }}</textarea>
                        @error('locations.'.$li.'.notes')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>
                </div>
            </div>
        @endforeach
    </div>
    <button type="button" id="add-vendor-location-row"
            class="mt-3 rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-sm font-medium text-slate-800 hover:bg-slate-50">
        {{ $vr('buttons.add_branch') }}
    </button>
</section>

<template id="vendor-location-row-template">
    <div class="vendor-location-row rounded-lg border border-slate-200 bg-slate-50/50 p-4" data-location-index="__LIDX__">
        <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-200/80 pb-3">
            <div class="flex flex-wrap items-center gap-2">
                <span class="vendor-location-branch-label text-xs font-semibold uppercase tracking-wide text-slate-500">{{ $vr('buttons.branch', ['num' => '__NUM__']) }}</span>
                <button type="button" data-remove-vendor-location
                        class="rounded border border-red-200 px-2 py-0.5 text-xs font-medium text-red-600 hover:bg-red-50">
                    {{ $vr('buttons.remove') }}
                </button>
            </div>
            <label class="inline-flex cursor-pointer items-center gap-2 text-sm text-slate-800">
                <input type="radio" name="primary_location_index" value="__LIDX__"
                       class="border-slate-300 text-slate-900 focus:ring-slate-500">
                {{ $vr('buttons.primary_branch') }}
            </label>
        </div>
        <div class="mt-4 grid gap-4 md:grid-cols-2">
            <div>
                <label class="block text-xs font-medium uppercase tracking-wide text-slate-500">{{ $vr('fields.country') }}</label>
                <select name="locations[__LIDX__][country_id]" data-vendor-location-country class="admin-filter-control !mt-1">
                    <option value="">—</option>
                    @foreach ($countriesCollection as $country)
                        <option value="{{ $country->id }}">{{ $country->flag_emoji ? $country->flag_emoji.' ' : '' }}{{ $country->name_ar }} — {{ $country->name_en }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium uppercase tracking-wide text-slate-500">{{ $vr('fields.city_optional') }}</label>
                <select name="locations[__LIDX__][city_id]" data-vendor-location-city class="admin-filter-control !mt-0">
                    <option value="">{{ $vr('hints.select_city_optional') }}</option>
                </select>
            </div>
            <div class="md:col-span-2">
                <label class="block text-xs font-medium uppercase tracking-wide text-slate-500">{{ $vr('fields.address') }}</label>
                <textarea name="locations[__LIDX__][address]" rows="2" class="admin-form-textarea"></textarea>
            </div>
            <div>
                <label class="block text-xs font-medium uppercase tracking-wide text-slate-500">{{ $vr('fields.phone') }}</label>
                <input type="text" name="locations[__LIDX__][phone]" class="admin-filter-control">
            </div>
            <div>
                <label class="block text-xs font-medium uppercase tracking-wide text-slate-500">{{ $vr('fields.whatsapp') }}</label>
                <input type="text" name="locations[__LIDX__][whatsapp]" class="admin-filter-control">
            </div>
            <div class="md:col-span-2">
                <label class="block text-xs font-medium uppercase tracking-wide text-slate-500">{{ $vr('fields.notes') }}</label>
                <textarea name="locations[__LIDX__][notes]" rows="2" class="admin-form-textarea"></textarea>
            </div>
        </div>
    </div>
</template>

{{-- 3. Contact Information --}}
<section class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
    <h2 class="border-b border-slate-100 pb-3 text-base font-semibold text-slate-900">{{ $vr('sections.contact_information') }}</h2>
    <div class="mt-4 grid gap-4 md:grid-cols-2">
        @foreach (['phone', 'whatsapp', 'telegram', 'email', 'website'] as $field)
            @php $label = $vr('fields.'.$field); @endphp
            <div>
                <label for="{{ $field }}" class="block text-xs font-medium uppercase tracking-wide text-slate-500">{{ $label }}</label>
                <input type="{{ $field === 'email' ? 'email' : 'text' }}" name="{{ $field }}" id="{{ $field }}"
                       value="{{ old($field, $v?->{$field} ?? '') }}"
                       class="admin-filter-control @error($field) border-red-500 @enderror">
                @error($field)<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
        @endforeach
    </div>
    <div class="mt-8 border-t border-slate-100 pt-6">
        <h3 class="text-sm font-semibold text-slate-900">{{ $vr('sections.social_media') }}</h3>
        <p class="mt-1 text-xs text-slate-500">{{ $vr('hints.social_media') }}</p>
        <div class="mt-3 grid gap-4 md:grid-cols-2">
            <div>
                <label for="facebook_url" class="block text-xs font-medium uppercase tracking-wide text-slate-500">{{ $vr('fields.facebook_url') }}</label>
                <input type="url" name="facebook_url" id="facebook_url"
                       value="{{ old('facebook_url', $v?->facebook_url ?? '') }}"
                       class="admin-filter-control @error('facebook_url') border-red-500 @enderror">
                @error('facebook_url')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="instagram_url" class="block text-xs font-medium uppercase tracking-wide text-slate-500">{{ $vr('fields.instagram_url') }}</label>
                <input type="url" name="instagram_url" id="instagram_url"
                       value="{{ old('instagram_url', $v?->instagram_url ?? '') }}"
                       class="admin-filter-control @error('instagram_url') border-red-500 @enderror">
                @error('instagram_url')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
        </div>
    </div>
</section>

{{-- 4. Primary Contact --}}
<section class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
    <h2 class="border-b border-slate-100 pb-3 text-base font-semibold text-slate-900">{{ $vr('sections.primary_contact') }}</h2>
    <div class="mt-4 grid gap-4 md:grid-cols-2">
        @foreach ([
            'primary_contact_name' => 'name',
            'primary_contact_position' => 'position',
            'primary_contact_phone' => 'phone',
            'primary_contact_email' => 'email',
        ] as $field => $labelKey)
            @php $label = $vr('fields.'.$labelKey); @endphp
            <div>
                <label for="{{ $field }}" class="block text-xs font-medium uppercase tracking-wide text-slate-500">{{ $label }}</label>
                <input type="{{ str_contains($field, 'email') ? 'email' : 'text' }}" name="{{ $field }}" id="{{ $field }}"
                       value="{{ old($field, $v?->{$field} ?? '') }}"
                       class="admin-filter-control @error($field) border-red-500 @enderror">
                @error($field)<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
        @endforeach
    </div>
</section>

{{-- 4b. Secondary Contact --}}
<section class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
    <h2 class="border-b border-slate-100 pb-3 text-base font-semibold text-slate-900">{{ $vr('sections.secondary_contact') }} <span class="text-xs font-normal text-slate-500">{{ $vr('sections.secondary_contact_optional') }}</span></h2>
    <div class="mt-4 grid gap-4 md:grid-cols-2">
        @foreach ([
            'secondary_contact_name' => 'name',
            'secondary_contact_position' => 'position',
            'secondary_contact_phone' => 'phone',
            'secondary_contact_email' => 'email',
        ] as $field => $labelKey)
            @php $label = $vr('fields.'.$labelKey); @endphp
            <div>
                <label for="{{ $field }}" class="block text-xs font-medium uppercase tracking-wide text-slate-500">{{ $label }}</label>
                <input type="{{ str_contains($field, 'email') ? 'email' : 'text' }}" name="{{ $field }}" id="{{ $field }}"
                       value="{{ old($field, $v?->{$field} ?? '') }}"
                       class="admin-filter-control @error($field) border-red-500 @enderror">
                @error($field)<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
        @endforeach
    </div>
</section>

{{-- 5. Procurement Information --}}
<section class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
    <h2 class="border-b border-slate-100 pb-3 text-base font-semibold text-slate-900">{{ $vr('sections.procurement_information') }}</h2>
    <div class="mt-4 grid gap-4 md:grid-cols-2">
        <div>
            <label for="pricing_frequency" class="block text-xs font-medium uppercase tracking-wide text-slate-500">{{ $vr('fields.pricing_frequency') }}</label>
            <select name="pricing_frequency" id="pricing_frequency"
                    class="admin-filter-control @error('pricing_frequency') border-red-500 @enderror">
                <option value="">—</option>
                @foreach (PricingFrequency::cases() as $case)
                    <option value="{{ $case->value }}" @selected(old('pricing_frequency', ($v?->pricing_frequency instanceof \BackedEnum) ? $v->pricing_frequency->value : ($v?->pricing_frequency ?? '')) === $case->value)>{{ $enumVr('pricing_frequency', $case) }}</option>
                @endforeach
            </select>
            @error('pricing_frequency')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>
        <div>
            <label for="delivery_lead_time" class="block text-xs font-medium uppercase tracking-wide text-slate-500">{{ $vr('fields.delivery_lead_time') }}</label>
            <select name="delivery_lead_time" id="delivery_lead_time"
                    class="admin-filter-control @error('delivery_lead_time') border-red-500 @enderror">
                <option value="">—</option>
                @foreach (LeadTimeRange::cases() as $case)
                    <option value="{{ $case->value }}" @selected(old('delivery_lead_time', ($v?->delivery_lead_time instanceof \BackedEnum) ? $v->delivery_lead_time->value : ($v?->delivery_lead_time ?? '')) === $case->value)>{{ $enumVr('lead_time', $case) }}</option>
                @endforeach
            </select>
            @error('delivery_lead_time')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>
        <div>
            <label for="execution_lead_time" class="block text-xs font-medium uppercase tracking-wide text-slate-500">{{ $vr('fields.execution_lead_time') }}</label>
            <select name="execution_lead_time" id="execution_lead_time"
                    class="admin-filter-control @error('execution_lead_time') border-red-500 @enderror">
                <option value="">—</option>
                @foreach (LeadTimeRange::cases() as $case)
                    <option value="{{ $case->value }}" @selected(old('execution_lead_time', ($v?->execution_lead_time instanceof \BackedEnum) ? $v->execution_lead_time->value : ($v?->execution_lead_time ?? '')) === $case->value)>{{ $enumVr('lead_time', $case) }}</option>
                @endforeach
            </select>
            @error('execution_lead_time')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>
        <div>
            <label for="bulletin_price_validity_days" class="block text-xs font-medium uppercase tracking-wide text-slate-500">{{ $vr('fields.bulletin_price_validity_days') }}</label>
            <input type="number" name="bulletin_price_validity_days" id="bulletin_price_validity_days" min="0" step="1"
                   value="{{ old('bulletin_price_validity_days', $v?->bulletin_price_validity_days ?? '') }}"
                   class="admin-filter-control @error('bulletin_price_validity_days') border-red-500 @enderror">
            @error('bulletin_price_validity_days')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>
        <div>
            <label for="currency_code" class="block text-xs font-medium uppercase tracking-wide text-slate-500">{{ $vr('fields.currency_code') }}</label>
            <input type="text" name="currency_code" id="currency_code" maxlength="3"
                   value="{{ old('currency_code', $v?->currency_code ?? '') }}"
                   class="admin-filter-control uppercase @error('currency_code') border-red-500 @enderror">
            @error('currency_code')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>
    </div>

    <div class="mt-8 border-t border-slate-100 pt-6">
        @if ($mode === 'edit')
            <input type="hidden" name="rfq_method_sync" value="1">
        @endif
        <h3 class="text-sm font-semibold text-slate-900">{{ $vr('subsections.rfq_method') }}</h3>
        <p class="mt-1 text-xs text-slate-500">{{ $vr('hints.rfq_method') }}</p>
        <div class="mt-3 grid gap-3 sm:grid-cols-2">
            @foreach (RfqMethod::cases() as $case)
                <label class="flex cursor-pointer items-center gap-2 text-sm text-slate-800">
                    <input type="checkbox" name="rfq_method[]" value="{{ $case->value }}"
                           id="rfq_method_{{ $case->value }}"
                           class="rounded border-slate-300 text-slate-900 focus:ring-slate-500"
                           @checked(in_array($case->value, $selectedRfqMethods, true))>
                    <span>{{ $enumVr('rfq_method', $case) }}</span>
                </label>
            @endforeach
        </div>
        @error('rfq_method')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
        @error('rfq_method.*')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
    </div>
</section>

{{-- 6. Commercial & Technical Information --}}
<section class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
    <h2 class="border-b border-slate-100 pb-3 text-base font-semibold text-slate-900">{{ $vr('sections.commercial_technical') }}</h2>
    <div class="mt-4 grid gap-4 md:grid-cols-2">
        <div>
            <label for="payment_method" class="block text-xs font-medium uppercase tracking-wide text-slate-500">{{ $vr('fields.payment_method') }}</label>
            <select name="payment_method" id="payment_method"
                    class="admin-filter-control @error('payment_method') border-red-500 @enderror">
                <option value="">—</option>
                @foreach (PaymentMethod::cases() as $case)
                    <option value="{{ $case->value }}" @selected(old('payment_method', ($v?->payment_method instanceof \BackedEnum) ? $v->payment_method->value : ($v?->payment_method ?? '')) === $case->value)>{{ $enumVr('payment_method', $case) }}</option>
                @endforeach
            </select>
            @error('payment_method')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>
        @unless ($isPublicRegister)
            <div>
                <label for="rating" class="block text-xs font-medium uppercase tracking-wide text-slate-500">{{ $vr('fields.rating') }}</label>
                <input type="number" name="rating" id="rating" min="1" max="5" step="1"
                       value="{{ old('rating', $v?->rating ?? '') }}"
                       class="admin-filter-control @error('rating') border-red-500 @enderror">
                @error('rating')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
        @endunless
        @foreach (['payment_terms', 'commercial_terms', 'technical_capabilities'] as $field)
            @php $label = $vr('fields.'.$field); @endphp
            <div class="md:col-span-2">
                <label for="{{ $field }}" class="block text-xs font-medium uppercase tracking-wide text-slate-500">{{ $label }}</label>
                <textarea name="{{ $field }}" id="{{ $field }}" rows="3"
                          class="admin-form-textarea @error($field) border-red-500 @enderror">{{ old($field, $v?->{$field} ?? '') }}</textarea>
                @error($field)<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
        @endforeach
    </div>
</section>

{{-- 7. Classification --}}
<section class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
    <h2 class="border-b border-slate-100 pb-3 text-base font-semibold text-slate-900">{{ $vr('sections.classification') }}</h2>
    <div class="mt-4 grid gap-4 md:grid-cols-2">
        <div>
            <label for="company_type" class="block text-xs font-medium uppercase tracking-wide text-slate-500">{{ $vr('fields.company_type') }}</label>
            <select name="company_type" id="company_type"
                    class="admin-filter-control @error('company_type') border-red-500 @enderror">
                <option value="">—</option>
                @foreach (CompanyType::cases() as $case)
                    <option value="{{ $case->value }}" @selected(old('company_type', ($v?->company_type instanceof \BackedEnum) ? $v->company_type->value : ($v?->company_type ?? '')) === $case->value)>{{ $enumVr('company_type', $case) }}</option>
                @endforeach
            </select>
            @error('company_type')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>
        <div>
            <label for="coverage_type" class="block text-xs font-medium uppercase tracking-wide text-slate-500">{{ $vr('fields.coverage_type') }}</label>
            <select name="coverage_type" id="coverage_type"
                    class="admin-filter-control @error('coverage_type') border-red-500 @enderror">
                <option value="">—</option>
                @foreach (CoverageType::cases() as $case)
                    <option value="{{ $case->value }}" @selected(old('coverage_type', ($v?->coverage_type instanceof \BackedEnum) ? $v->coverage_type->value : ($v?->coverage_type ?? '')) === $case->value)>{{ $enumVr('coverage_type', $case) }}</option>
                @endforeach
            </select>
            @error('coverage_type')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>
        @foreach (['tax_number', 'registration_number', 'license_number'] as $field)
            @php $label = $vr('fields.'.$field); @endphp
            <div>
                <label for="{{ $field }}" class="block text-xs font-medium uppercase tracking-wide text-slate-500">{{ $label }}</label>
                <input type="text" name="{{ $field }}" id="{{ $field }}"
                       value="{{ old($field, $v?->{$field} ?? '') }}"
                       class="admin-filter-control @error($field) border-red-500 @enderror">
                @error($field)<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
        @endforeach
    </div>

    <div class="mt-8 border-t border-slate-100 pt-6">
        <h3 class="text-sm font-semibold text-slate-900">{{ $vr('subsections.business_types') }}</h3>
        <p class="mt-1 text-xs text-slate-500">{{ $vr('hints.business_types') }}</p>
        <div class="mt-3 grid gap-3 sm:grid-cols-2">
            @foreach (VendorBusinessType::cases() as $case)
                <label class="flex items-center gap-2 text-sm text-slate-800">
                    <input type="checkbox" name="business_types[]" value="{{ $case->value }}"
                           class="rounded border-slate-300 text-slate-900 focus:ring-slate-500"
                        @checked(in_array($case->value, $selectedBusinessTypes, true))>
                    {{ $enumVr('business_type', $case) }}
                </label>
            @endforeach
        </div>
        @error('business_types')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
        @error('business_types.*')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
    </div>

    <div class="mt-8 border-t border-slate-100 pt-6">
        <h3 class="text-sm font-semibold text-slate-900">{{ $vr('subsections.categories_subcategories') }}</h3>
        <p class="mt-1 text-xs text-slate-500">{{ $vr('hints.categories') }}</p>
        @error('categories')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror

        <div class="category-rows-wrap mt-3 overflow-x-auto rounded-lg border border-slate-200 md:overflow-x-auto">
            <table class="category-rows-table min-w-full divide-y divide-slate-200 text-sm">
                <thead class="category-rows-thead bg-slate-50 text-xs font-semibold uppercase tracking-wide text-slate-500">
                <tr>
                    <th class="px-3 py-2 text-left">{{ $vr('table.primary_category') }}</th>
                    <th class="px-3 py-2 text-left w-[min(14rem,40vw)]">{{ $vr('table.category') }}</th>
                    <th class="px-3 py-2 text-left min-w-[12rem]">{{ $vr('table.subcategories') }}</th>
                    <th class="px-3 py-2 text-right w-[5rem]">{{ $vr('table.actions') }}</th>
                </tr>
                </thead>
                <tbody id="category-rows" class="category-rows-tbody divide-y divide-slate-100 bg-white">
                @foreach ($categoryRows as $index => $row)
                    @php
                        $catId = old("categories.$index.category_id", $row['category_id'] ?? '');
                        $subIdsSel = old("categories.$index.subcategory_ids", $row['subcategory_ids'] ?? []);
                        if (! is_array($subIdsSel)) {
                            $subIdsSel = [];
                        }
                        $subIdsSel = array_map('intval', $subIdsSel);
                        $subsForRow = $catId !== '' && $catId !== null
                            ? ($categories->firstWhere('id', (int) $catId)?->subcategories
                                ?->sort(fn ($a, $b) => strcasecmp($catalogLabel($a), $catalogLabel($b)))
                                ?->values() ?? collect())
                            : collect();
                    @endphp
                    <tr class="category-row" data-row-index="{{ $index }}" data-category-row-persisted="{{ $mode === 'edit' ? '1' : '0' }}">
                        <td class="category-row-cell category-row-cell-primary px-3 py-2 align-top" data-label="{{ $vr('table.primary_category') }}">
                            <label class="inline-flex cursor-pointer items-center gap-2 text-sm text-slate-800 md:block md:cursor-default">
                                <input type="checkbox" name="categories[{{ $index }}][is_primary]" value="1"
                                       class="rounded border-slate-300 text-slate-900 focus:ring-slate-500 md:mt-1"
                                       @checked((bool) old("categories.$index.is_primary", $row['is_primary'] ?? false))>
                                <span class="md:hidden">{{ $vr('table.primary_category') }}</span>
                            </label>
                        </td>
                        <td class="category-row-cell category-row-cell-category px-3 py-2 align-top" data-label="{{ $vr('table.category') }}">
                            <select name="categories[{{ $index }}][category_id]" data-category-select
                                    class="admin-filter-control !mt-0 w-full max-w-full min-w-0 md:min-w-[10rem] @error('categories.'.$index.'.category_id') border-red-500 @enderror">
                                <option value="">—</option>
                                @foreach ($categories as $cat)
                                    <option value="{{ $cat->id }}" title="{{ $catalogLabel($cat) }}" @selected((string) $catId === (string) $cat->id)>{{ $catalogLabel($cat) }}</option>
                                @endforeach
                            </select>
                            @unless ($isPublicRegister)
                                <div class="mt-2">
                                    <button type="button"
                                            data-add-category
                                            class="inline-flex items-center rounded border border-slate-300 px-2 py-0.5 text-xs font-medium text-slate-700 shadow-sm hover:bg-slate-50">
                                        {{ $vr('buttons.add_category') }}
                                    </button>
                                </div>
                            @endunless
                            @error('categories.'.$index.'.category_id')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                        </td>
                        <td class="category-row-cell category-row-cell-subcategories px-3 py-2 align-top" data-label="{{ $vr('table.subcategories') }}">
                            <div data-category-sub-checkboxes
                                 class="max-h-44 space-y-2 overflow-y-auto rounded-lg border border-slate-200 bg-white p-2 @error('categories.'.$index.'.subcategory_ids') border-red-500 @enderror">
                                @forelse ($subsForRow as $sub)
                                    <label class="flex cursor-pointer items-start gap-2 text-sm text-slate-800">
                                        <input type="checkbox" name="categories[{{ $index }}][subcategory_ids][]" value="{{ $sub->id }}"
                                               class="mt-0.5 rounded border-slate-300 text-slate-900 focus:ring-slate-500"
                                               @checked(in_array((int) $sub->id, $subIdsSel, true))>
                                        <span dir="auto" class="min-w-0 break-words">{{ $catalogLabel($sub) }}</span>
                                    </label>
                                @empty
                                    <p class="text-xs text-slate-500">{{ $vr('hints.choose_category_subcategories') }}</p>
                                @endforelse
                            </div>
                            @error('categories.'.$index.'.subcategory_ids')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                            @unless ($isPublicRegister)
                                <div class="mt-2">
                                    <button type="button"
                                            data-add-subcategory
                                            @disabled($catId === '' || $catId === null)
                                            class="inline-flex items-center rounded border border-slate-300 px-2 py-0.5 text-xs font-medium text-slate-700 shadow-sm hover:bg-slate-50 disabled:cursor-not-allowed disabled:opacity-50">
                                        {{ $vr('buttons.add_subcategory') }}
                                    </button>
                                </div>
                            @endunless
                        </td>
                        <td class="category-row-cell category-row-cell-actions px-3 py-2 align-top text-right" data-label="">
                            <div class="category-row-mobile-header mb-0 flex items-center justify-between gap-2 border-b border-slate-200/80 pb-3 md:hidden">
                                <span class="category-row-card-label text-xs font-semibold uppercase tracking-wide text-slate-500">{{ $vr('buttons.category_row_label', ['num' => $index + 1]) }}</span>
                                <button type="button" data-remove-category-row
                                        class="rounded border border-red-200 px-2 py-0.5 text-xs font-medium text-red-600 hover:bg-red-50">
                                    {{ $vr('buttons.remove') }}
                                </button>
                            </div>
                            <button type="button" data-remove-category-row
                                    class="hidden rounded border border-red-200 px-2 py-0.5 text-xs font-medium text-red-600 hover:bg-red-50 md:inline-block">
                                {{ $vr('buttons.remove') }}
                            </button>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
        <button type="button" id="add-category-row"
                class="mt-3 rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-sm font-medium text-slate-800 hover:bg-slate-50">
            {{ $vr('buttons.add_category_row') }}
        </button>
    </div>
</section>

<template id="category-row-template">
    <tr class="category-row" data-row-index="__IDX__" data-category-row-persisted="0">
        <td class="category-row-cell category-row-cell-primary px-3 py-2 align-top" data-label="{{ $vr('table.primary_category') }}">
            <label class="inline-flex cursor-pointer items-center gap-2 text-sm text-slate-800 md:block md:cursor-default">
                <input type="checkbox" name="categories[__IDX__][is_primary]" value="1"
                       class="rounded border-slate-300 text-slate-900 focus:ring-slate-500 md:mt-1">
                <span class="md:hidden">{{ $vr('table.primary_category') }}</span>
            </label>
        </td>
        <td class="category-row-cell category-row-cell-category px-3 py-2 align-top" data-label="{{ $vr('table.category') }}">
            <select name="categories[__IDX__][category_id]" data-category-select
                    class="admin-filter-control !mt-0 w-full max-w-full min-w-0 md:min-w-[10rem]">
                <option value="">—</option>
                @foreach ($categories as $cat)
                    <option value="{{ $cat->id }}" title="{{ $catalogLabel($cat) }}">{{ $catalogLabel($cat) }}</option>
                @endforeach
            </select>
            @unless ($isPublicRegister)
                <div class="mt-2">
                    <button type="button"
                            data-add-category
                            class="inline-flex items-center rounded border border-slate-300 px-2 py-0.5 text-xs font-medium text-slate-700 shadow-sm hover:bg-slate-50">
                        {{ $vr('buttons.add_category') }}
                    </button>
                </div>
            @endunless
        </td>
        <td class="category-row-cell category-row-cell-subcategories px-3 py-2 align-top" data-label="{{ $vr('table.subcategories') }}">
            <div data-category-sub-checkboxes
                 class="max-h-44 space-y-2 overflow-y-auto rounded-lg border border-slate-200 bg-white p-2">
                <p class="text-xs text-slate-500">{{ $vr('hints.choose_category_subcategories') }}</p>
            </div>
            @unless ($isPublicRegister)
                <div class="mt-2">
                    <button type="button"
                            data-add-subcategory
                            disabled
                            class="inline-flex items-center rounded border border-slate-300 px-2 py-0.5 text-xs font-medium text-slate-700 shadow-sm hover:bg-slate-50 disabled:cursor-not-allowed disabled:opacity-50">
                        {{ $vr('buttons.add_subcategory') }}
                    </button>
                </div>
            @endunless
        </td>
        <td class="category-row-cell category-row-cell-actions px-3 py-2 align-top text-right" data-label="">
            <div class="category-row-mobile-header mb-0 flex items-center justify-between gap-2 border-b border-slate-200/80 pb-3 md:hidden">
                <span class="category-row-card-label text-xs font-semibold uppercase tracking-wide text-slate-500">{{ $vr('buttons.category_row_label', ['num' => '__NUM__']) }}</span>
                <button type="button" data-remove-category-row
                        class="rounded border border-red-200 px-2 py-0.5 text-xs font-medium text-red-600 hover:bg-red-50">
                    {{ $vr('buttons.remove') }}
                </button>
            </div>
            <button type="button" data-remove-category-row
                    class="hidden rounded border border-red-200 px-2 py-0.5 text-xs font-medium text-red-600 hover:bg-red-50 md:inline-block">
                {{ $vr('buttons.remove') }}
            </button>
        </td>
    </tr>
</template>

{{-- 7b. NDA --}}
<section class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
    <h2 class="border-b border-slate-100 pb-3 text-base font-semibold text-slate-900">{{ $vr('sections.signed_nda') }}</h2>
    <p class="mt-2 text-xs text-slate-500">{{ $vr('hints.nda') }}</p>
    <div class="mt-4 space-y-3">
        @if ($mode === 'edit' && $v && $v->hasNda())
            <div class="rounded-lg border border-slate-200 bg-slate-50/50 p-4 text-sm">
                <div class="font-medium text-slate-900">{{ $v->nda_file_name }}</div>
                @if ($v->nda_file_type)
                    <div class="mt-1 text-xs text-slate-500">{{ $v->nda_file_type }}</div>
                @endif
                <a href="{{ $v->nda_url }}" target="_blank" rel="noopener"
                   class="mt-2 inline-block text-sm font-medium text-slate-700 hover:text-slate-900">{{ $vr('hints.open_current_file') }}</a>
                <label class="mt-3 flex items-center gap-2 text-sm text-red-700">
                    <input type="checkbox" name="remove_nda" value="1" class="rounded border-slate-300"
                           @checked(old('remove_nda'))>
                    {{ $vr('hints.remove_nda_on_save') }}
                </label>
            </div>
        @endif
        <div>
            <label for="nda" class="block text-xs font-medium uppercase tracking-wide text-slate-500">
                @if ($mode === 'edit' && $v && $v->hasNda())
                    {{ $vr('fields.replace_nda') }}
                @else
                    {{ $vr('fields.upload_nda') }}
                @endif
            </label>
            <input type="file" name="nda" id="nda"
                   class="admin-form-file @error('nda') border-red-500 @enderror">
            @error('nda')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>
    </div>
</section>

{{-- 8. Brochures --}}
<section class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
    <h2 class="border-b border-slate-100 pb-3 text-base font-semibold text-slate-900">{{ $vr('sections.brochures') }}</h2>
    <div class="mt-4 grid gap-4 md:grid-cols-2">
        @if ($mode === 'edit' && $v && $v->brochures->isNotEmpty())
            <div class="md:col-span-2">
                <h3 class="text-sm font-medium text-slate-800">{{ $vr('hints.existing_files') }}</h3>
                <ul class="mt-2 divide-y divide-slate-100 rounded-lg border border-slate-200 text-sm">
                    @foreach ($v->brochures as $brochure)
                        <li class="flex flex-col gap-3 px-3 py-3 sm:flex-row sm:items-start sm:justify-between sm:gap-4" data-existing-brochure-id="{{ $brochure->id }}">
                            <div class="min-w-0 flex-1 space-y-2">
                            <div class="font-medium text-slate-900">{{ $brochure->file_name }}</div>
                            <div class="break-all font-mono text-xs text-slate-500">{{ $brochure->file_path }}</div>
                            @if ($brochure->notes)
                                <div class="text-xs text-slate-600"><span class="font-medium text-slate-500">{{ $vr('fields.notes') }}:</span> {{ $brochure->notes }}</div>
                            @endif
                            @if ($brochure->category_id || $brochure->subcategory_id)
                                <div class="text-xs text-slate-600">
                                    <span class="font-medium text-slate-500">{{ $vr('hints.linked') }}</span>
                                    @if ($brochure->category)
                                        <span dir="auto">{{ $brochure->category->name_ar }}</span>
                                        <span class="text-slate-400"> — </span>
                                        <span class="text-slate-500">{{ $brochure->category->name_en }}</span>
                                    @else
                                        —
                                    @endif
                                    @if ($brochure->subcategory)
                                        <span class="text-slate-400"> / </span>
                                        <span dir="auto">{{ $brochure->subcategory->name_ar }}</span>
                                        <span class="text-slate-400"> — </span>
                                        <span class="text-slate-500">{{ $brochure->subcategory->name_en }}</span>
                                    @endif
                                </div>
                            @endif
                            </div>
                            <button type="button" data-remove-existing-brochure data-brochure-id="{{ $brochure->id }}"
                                    class="shrink-0 self-start rounded border border-red-200 px-2 py-0.5 text-xs font-medium text-red-600 hover:bg-red-50">
                                {{ $vr('buttons.remove') }}
                            </button>
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif
        <div class="md:col-span-2 space-y-3">
            <h3 class="text-sm font-medium text-slate-800">{{ $vr('hints.add_new_brochures') }}</h3>
            <p class="text-xs text-slate-500">{{ $vr('hints.brochures_new') }}</p>
            <div id="brochure-new-rows" class="space-y-4">
                @for ($bi = 0; $bi < $brochureNewRowCount; $bi++)
                    <div class="brochure-upload-row rounded-lg border border-slate-200 bg-slate-50/50 p-4" data-brochure-index="{{ $bi }}">
                        <div class="mb-3 flex flex-wrap items-center justify-between gap-2 border-b border-slate-200/80 pb-2">
                            <span class="brochure-upload-heading text-xs font-semibold uppercase tracking-wide text-slate-500">{{ $vr('buttons.new_brochure', ['num' => $bi + 1]) }}</span>
                            <button type="button" data-remove-brochure-upload-row
                                    class="rounded border border-red-200 px-2 py-0.5 text-xs font-medium text-red-600 hover:bg-red-50">
                                {{ $vr('buttons.remove') }}
                            </button>
                        </div>
                        <div class="grid gap-4 md:grid-cols-2">
                            <div class="md:col-span-2">
                                <label class="block text-xs font-medium uppercase tracking-wide text-slate-500">{{ $vr('fields.file') }}</label>
                                <input type="file" name="brochure_rows[{{ $bi }}][file]"
                                       class="admin-form-file @error('brochure_rows.'.$bi.'.file') border-red-500 @enderror">
                                @error('brochure_rows.'.$bi.'.file')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                            </div>
                            <div class="md:col-span-2">
                                <label class="block text-xs font-medium uppercase tracking-wide text-slate-500">{{ $vr('fields.notes') }}</label>
                                <textarea name="brochure_rows[{{ $bi }}][notes]" rows="2"
                                          class="admin-form-textarea @error('brochure_rows.'.$bi.'.notes') border-red-500 @enderror">{{ old('brochure_rows.'.$bi.'.notes') }}</textarea>
                                @error('brochure_rows.'.$bi.'.notes')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label class="block text-xs font-medium uppercase tracking-wide text-slate-500">{{ $vr('fields.category_optional') }}</label>
                                <select name="brochure_rows[{{ $bi }}][category_id]" data-brochure-category
                                        class="admin-filter-control @error('brochure_rows.'.$bi.'.category_id') border-red-500 @enderror">
                                    <option value="">—</option>
                                    @foreach ($categories as $cat)
                                        <option value="{{ $cat->id }}" title="{{ $cat->name_en }}" @selected((string) old('brochure_rows.'.$bi.'.category_id') === (string) $cat->id)>{{ $cat->name_ar }} — {{ $cat->name_en }}</option>
                                    @endforeach
                                </select>
                                @error('brochure_rows.'.$bi.'.category_id')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label class="block text-xs font-medium uppercase tracking-wide text-slate-500">{{ $vr('fields.subcategory_optional') }}</label>
                                <select name="brochure_rows[{{ $bi }}][subcategory_id]" data-brochure-subcategory
                                        class="admin-filter-control @error('brochure_rows.'.$bi.'.subcategory_id') border-red-500 @enderror">
                                    <option value="">—</option>
                                </select>
                                @error('brochure_rows.'.$bi.'.subcategory_id')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                            </div>
                        </div>
                    </div>
                @endfor
            </div>
            <button type="button" id="add-brochure-row"
                    class="rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-sm font-medium text-slate-800 hover:bg-slate-50">
                {{ $vr('buttons.add_brochure_row') }}
            </button>
            <p class="text-xs text-slate-500">{{ $vr('hints.brochures_formats') }}</p>
            @error('brochure_rows')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            @error('brochures')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            @error('brochures.*')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>
    </div>
</section>

<template id="brochure-row-template">
    <div class="brochure-upload-row rounded-lg border border-slate-200 bg-slate-50/50 p-4" data-brochure-index="__BIDX__">
        <div class="mb-3 flex flex-wrap items-center justify-between gap-2 border-b border-slate-200/80 pb-2">
            <span class="brochure-upload-heading text-xs font-semibold uppercase tracking-wide text-slate-500">{{ $vr('buttons.new_brochure', ['num' => '__BNUM__']) }}</span>
            <button type="button" data-remove-brochure-upload-row
                    class="rounded border border-red-200 px-2 py-0.5 text-xs font-medium text-red-600 hover:bg-red-50">
                {{ $vr('buttons.remove') }}
            </button>
        </div>
        <div class="grid gap-4 md:grid-cols-2">
            <div class="md:col-span-2">
                <label class="block text-xs font-medium uppercase tracking-wide text-slate-500">{{ $vr('fields.file') }}</label>
                <input type="file" name="brochure_rows[__BIDX__][file]"
                       class="admin-form-file">
            </div>
            <div class="md:col-span-2">
                <label class="block text-xs font-medium uppercase tracking-wide text-slate-500">{{ $vr('fields.notes') }}</label>
                <textarea name="brochure_rows[__BIDX__][notes]" rows="2"
                          class="admin-form-textarea"></textarea>
            </div>
            <div>
                <label class="block text-xs font-medium uppercase tracking-wide text-slate-500">{{ $vr('fields.category_optional') }}</label>
                <select name="brochure_rows[__BIDX__][category_id]" data-brochure-category
                        class="admin-filter-control">
                    <option value="">—</option>
                    @foreach ($categories as $cat)
                        <option value="{{ $cat->id }}" title="{{ $cat->name_en }}">{{ $cat->name_ar }} — {{ $cat->name_en }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium uppercase tracking-wide text-slate-500">{{ $vr('fields.subcategory_optional') }}</label>
                <select name="brochure_rows[__BIDX__][subcategory_id]" data-brochure-subcategory
                        class="admin-filter-control">
                    <option value="">—</option>
                </select>
            </div>
        </div>
    </div>
</template>

@unless ($isPublicRegister)
    @include('procurement.vendors.partials.add-subcategory-modal')
    @include('procurement.vendors.partials.add-category-modal')
@endunless

@php
    $vendorFormConfig = [
        'subData' => $subcategoriesByCategory,
        'categoryInitial' => $categoryInitialForJs,
        'citiesByCountry' => $citiesByCountry,
        'locationInitial' => $locationInitialForJs,
        'brochureInitial' => $brochureInitialForJs,
        'catalogLocale' => $formLocale,
        'bilingualCatalogLabels' => ! $isPublicRegister,
        'i18n' => [
            'branch' => $vr('js.branch'),
            'new_brochure' => $vr('js.new_brochure'),
            'select_city_optional' => $vr('js.select_city_optional'),
            'choose_category_subcategories' => $vr('js.choose_category_subcategories'),
            'no_subcategories' => $vr('js.no_subcategories'),
            'select_category_first' => $vr('js.select_category_first'),
            'category_required' => $vr('js.category_required'),
            'arabic_name_required' => $vr('js.arabic_name_required'),
            'english_name_required' => $vr('js.english_name_required'),
            'fix_highlighted_fields' => $vr('js.fix_highlighted_fields'),
            'failed_create_subcategory' => $vr('js.failed_create_subcategory'),
            'failed_create_category' => $vr('js.failed_create_category'),
            'server_no_subcategory' => $vr('js.server_no_subcategory'),
            'server_no_category' => $vr('js.server_no_category'),
            'confirm_remove_category_row' => $vr('js.confirm_remove_category_row'),
            'category_row_label' => $vr('buttons.category_row_label'),
            'confirm_remove_branch' => $vr('js.confirm_remove_branch'),
            'confirm_remove_brochure' => $vr('js.confirm_remove_brochure'),
        ],
    ];
@endphp

@push('scripts')
    <script type="application/json" id="vendor-form-config">@json($vendorFormConfig)</script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const {
                subData,
                categoryInitial,
                citiesByCountry,
                locationInitial,
                brochureInitial,
                i18n,
                catalogLocale,
                bilingualCatalogLabels,
            } = JSON.parse(document.getElementById('vendor-form-config').textContent);

            function catalogOptionLabel(item) {
                const ar = (item && item.name_ar) ? String(item.name_ar).trim() : '';
                const en = (item && item.name_en) ? String(item.name_en).trim() : '';
                if (bilingualCatalogLabels) {
                    return en ? (ar ? ar + ' — ' + en : en) : ar;
                }
                if (catalogLocale === 'ar') {
                    return ar || en;
                }
                return en || ar;
            }

            function t(key, replace) {
                let text = (i18n && i18n[key]) ? i18n[key] : key;
                if (replace) {
                    Object.keys(replace).forEach(function (placeholder) {
                        text = text.split(':' + placeholder).join(String(replace[placeholder]));
                    });
                }
                return text;
            }

            function subcategoryList(categoryId) {
                if (!categoryId) {
                    return [];
                }
                const list = subData[categoryId] || subData[String(categoryId)] || [];

                return list.slice().sort(function (a, b) {
                    return catalogOptionLabel(a).localeCompare(catalogOptionLabel(b), undefined, { sensitivity: 'base' });
                });
            }

            function optionsForProcurementCategory(categoryId) {
                const list = subcategoryList(categoryId);
                const frag = document.createDocumentFragment();
                const empty = document.createElement('option');
                empty.value = '';
                empty.textContent = t('select_city_optional');
                frag.appendChild(empty);
                list.forEach(function (item) {
                    const opt = document.createElement('option');
                    opt.value = String(item.id);
                    const label = catalogOptionLabel(item);
                    opt.textContent = label;
                    opt.title = label;
                    frag.appendChild(opt);
                });
                return frag;
            }

            function refreshCategorySubCheckboxes(row, selectedIds) {
                const catSelect = row.querySelector('[data-category-select]');
                const wrap = row.querySelector('[data-category-sub-checkboxes]');
                if (!catSelect || !wrap) {
                    return;
                }
                const categoryId = catSelect.value;
                const list = subcategoryList(categoryId);
                const rowIndex = row.getAttribute('data-row-index') || '';
                wrap.innerHTML = '';
                if (!list.length) {
                    const p = document.createElement('p');
                    p.className = 'text-xs text-slate-500';
                    p.textContent = categoryId ? t('no_subcategories') : t('choose_category_subcategories');
                    wrap.appendChild(p);

                    return;
                }

                const sel = new Set((selectedIds || []).map(String));
                list.forEach(function (item) {
                    const label = document.createElement('label');
                    label.className = 'flex cursor-pointer items-start gap-2 text-sm text-slate-800';
                    const cb = document.createElement('input');
                    cb.type = 'checkbox';
                    cb.name = 'categories[' + rowIndex + '][subcategory_ids][]';
                    cb.value = String(item.id);
                    cb.className = 'mt-0.5 rounded border-slate-300 text-slate-900 focus:ring-slate-500';
                    if (sel.has(String(item.id))) {
                        cb.checked = true;
                    }
                    const span = document.createElement('span');
                    span.setAttribute('dir', 'auto');
                    span.className = 'min-w-0 break-words';
                    span.textContent = catalogOptionLabel(item);
                    label.appendChild(cb);
                    label.appendChild(span);
                    wrap.appendChild(label);
                });
            }

            function wireVendorCategoryRow(row) {
                const catSelect = row.querySelector('[data-category-select]');
                if (!catSelect) {
                    return;
                }
                const addSubcategoryBtn = row.querySelector('[data-add-subcategory]');
                function syncAddSubcategoryButton() {
                    if (!addSubcategoryBtn) {
                        return;
                    }
                    addSubcategoryBtn.disabled = !catSelect.value;
                }
                syncAddSubcategoryButton();
                catSelect.addEventListener('change', function () {
                    refreshCategorySubCheckboxes(row, []);
                    syncAddSubcategoryButton();
                });
            }

            const quickStoreUrl = "{{ route('subcategories.quick-store') }}";
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
            let quickAddTargetRow = null;

            const quickAddModal = document.getElementById('add-subcategory-modal');
            const quickAddForm = document.getElementById('add-subcategory-form');
            const quickAddCategoryInput = document.getElementById('add-subcategory-category-id');
            const quickAddNameArInput = document.getElementById('add-subcategory-name-ar');
            const quickAddNameEnInput = document.getElementById('add-subcategory-name-en');
            const quickAddErrNameAr = document.getElementById('add-subcategory-error-name-ar');
            const quickAddErrNameEn = document.getElementById('add-subcategory-error-name-en');
            const quickAddErrGeneral = document.getElementById('add-subcategory-error-general');
            const quickAddCancelBtn = document.getElementById('add-subcategory-cancel');
            const quickAddSaveBtn = document.getElementById('add-subcategory-save');

            function clearQuickAddErrors() {
                [quickAddErrNameAr, quickAddErrNameEn, quickAddErrGeneral].forEach(function (el) {
                    if (!el) {
                        return;
                    }
                    el.classList.add('hidden');
                    el.textContent = '';
                });
                [quickAddNameArInput, quickAddNameEnInput].forEach(function (el) {
                    if (!el) {
                        return;
                    }
                    el.classList.remove('border-red-500');
                });
            }

            function setFieldError(inputEl, errEl, message) {
                if (!inputEl || !errEl) {
                    return;
                }
                errEl.textContent = message;
                errEl.classList.remove('hidden');
                inputEl.classList.add('border-red-500');
            }

            function openQuickAddModal(row) {
                if (!quickAddModal || !quickAddForm || !quickAddCategoryInput) {
                    return false;
                }
                const catSelect = row.querySelector('[data-category-select]');
                const categoryId = catSelect ? catSelect.value : '';
                if (!categoryId) {
                    quickAddErrGeneral.textContent = t('select_category_first');
                    quickAddErrGeneral.classList.remove('hidden');
                    return false;
                }

                quickAddTargetRow = row;
                quickAddCategoryInput.value = categoryId;
                quickAddNameArInput.value = '';
                quickAddNameEnInput.value = '';
                clearQuickAddErrors();

                quickAddModal.classList.remove('hidden');
                quickAddModal.setAttribute('aria-hidden', 'false');
                document.body.classList.add('overflow-hidden');
                setTimeout(function () {
                    quickAddNameArInput.focus();
                }, 0);

                return true;
            }

            function closeQuickAddModal() {
                quickAddTargetRow = null;
                if (quickAddModal) {
                    quickAddModal.classList.add('hidden');
                    quickAddModal.setAttribute('aria-hidden', 'true');
                }
                document.body.classList.remove('overflow-hidden');
                clearQuickAddErrors();
            }

            if (quickAddCancelBtn) {
                quickAddCancelBtn.addEventListener('click', function () {
                    closeQuickAddModal();
                });
            }

            if (quickAddModal) {
                const panel = quickAddModal.querySelector('.relative');
                quickAddModal.addEventListener('click', function (e) {
                    if (panel && !panel.contains(e.target)) {
                        closeQuickAddModal();
                    }
                });
            }

            if (quickAddSaveBtn) {
                quickAddSaveBtn.addEventListener('click', async function () {
                    if (!quickAddTargetRow) {
                        return;
                    }

                    const categoryId = (quickAddCategoryInput.value || '').trim();
                    const nameAr = (quickAddNameArInput.value || '').trim();
                    const nameEn = (quickAddNameEnInput.value || '').trim();

                    clearQuickAddErrors();
                    if (!categoryId) {
                        quickAddErrGeneral.textContent = t('category_required');
                        quickAddErrGeneral.classList.remove('hidden');
                        return;
                    }
                    if (!nameAr) {
                        setFieldError(quickAddNameArInput, quickAddErrNameAr, t('arabic_name_required'));
                        return;
                    }
                    if (!nameEn) {
                        setFieldError(quickAddNameEnInput, quickAddErrNameEn, t('english_name_required'));
                        return;
                    }

                    if (quickAddSaveBtn) {
                        quickAddSaveBtn.disabled = true;
                    }

                    const formData = new FormData();
                    formData.append('category_id', categoryId);
                    formData.append('name_ar', nameAr);
                    formData.append('name_en', nameEn);

                    try {
                        const res = await fetch(quickStoreUrl, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': csrfToken,
                                'Accept': 'application/json',
                            },
                            body: formData
                        });

                        const payload = await res.json().catch(function () {
                            return null;
                        });

                        if (!res.ok) {
                            const errors = payload && payload.errors ? payload.errors : null;
                            clearQuickAddErrors();
                            if (errors) {
                                if (errors.name_ar && errors.name_ar[0]) {
                                    setFieldError(quickAddNameArInput, quickAddErrNameAr, errors.name_ar[0]);
                                }
                                if (errors.name_en && errors.name_en[0]) {
                                    setFieldError(quickAddNameEnInput, quickAddErrNameEn, errors.name_en[0]);
                                }
                                if ((!errors.name_ar || !errors.name_ar[0]) && (!errors.name_en || !errors.name_en[0])) {
                                    quickAddErrGeneral.textContent = t('fix_highlighted_fields');
                                    quickAddErrGeneral.classList.remove('hidden');
                                }
                            } else {
                                quickAddErrGeneral.textContent = t('failed_create_subcategory');
                                quickAddErrGeneral.classList.remove('hidden');
                            }
                            return;
                        }

                        const created = payload;
                        if (!created || !created.id) {
                            quickAddErrGeneral.textContent = t('server_no_subcategory');
                            quickAddErrGeneral.classList.remove('hidden');
                            return;
                        }

                        const newId = created.id;
                        const catKey = String(categoryId);

                        if (!Array.isArray(subData[catKey])) {
                            subData[catKey] = [];
                        }
                        const alreadyExists = subData[catKey].some(function (it) {
                            return String(it.id) === String(newId);
                        });
                        if (!alreadyExists) {
                            subData[catKey].push({
                                id: newId,
                                name_ar: created.name_ar || '',
                                name_en: created.name_en || '',
                            });
                        }

                        const targetRow = quickAddTargetRow;
                        closeQuickAddModal();

                        const existingSelected = [];
                        const wrap = targetRow ? targetRow.querySelector('[data-category-sub-checkboxes]') : null;
                        if (wrap) {
                            wrap.querySelectorAll('input[type="checkbox"]:checked').forEach(function (cb) {
                                existingSelected.push(parseInt(cb.value, 10));
                            });
                        }
                        const nextSelected = Array.from(new Set(existingSelected.concat([parseInt(newId, 10)])));
                        refreshCategorySubCheckboxes(targetRow, nextSelected);
                        const addBtn = targetRow ? targetRow.querySelector('[data-add-subcategory]') : null;
                        if (addBtn && targetRow) {
                            addBtn.disabled = !targetRow.querySelector('[data-category-select]')?.value;
                        }
                    } finally {
                        if (quickAddSaveBtn) {
                            quickAddSaveBtn.disabled = false;
                        }
                    }
                });
            }

            document.addEventListener('click', function (e) {
                const btn = e.target.closest('[data-add-subcategory]');
                if (!btn) {
                    return;
                }
                const row = btn.closest('tr.category-row');
                if (!row) {
                    return;
                }
                openQuickAddModal(row);
            });

            const categoryQuickStoreUrl = "{{ route('categories.quick-store') }}";
            let categoryQuickAddTargetRow = null;

            const categoryQuickAddModal = document.getElementById('add-category-modal');
            const categoryQuickAddForm = document.getElementById('add-category-form');
            const categoryQuickAddNameArInput = document.getElementById('add-category-name-ar');
            const categoryQuickAddNameEnInput = document.getElementById('add-category-name-en');
            const categoryQuickAddErrNameAr = document.getElementById('add-category-error-name-ar');
            const categoryQuickAddErrNameEn = document.getElementById('add-category-error-name-en');
            const categoryQuickAddErrGeneral = document.getElementById('add-category-error-general');
            const categoryQuickAddCancelBtn = document.getElementById('add-category-cancel');
            const categoryQuickAddSaveBtn = document.getElementById('add-category-save');

            function createCategoryOption(item) {
                const opt = document.createElement('option');
                opt.value = String(item.id);
                opt.textContent = catalogOptionLabel(item);
                if (item.name_en) {
                    opt.title = item.name_en;
                }
                return opt;
            }

            function appendCategoryOptionToSelect(selectEl, category) {
                if (!selectEl) {
                    return;
                }
                const id = String(category.id);
                if (selectEl.querySelector('option[value="' + CSS.escape(id) + '"]')) {
                    return;
                }

                const opt = createCategoryOption(category);
                const label = catalogOptionLabel(category).toLowerCase();
                const existingOptions = Array.from(selectEl.options).filter(function (optionEl) {
                    return optionEl.value !== '';
                });
                let inserted = false;
                for (let i = 0; i < existingOptions.length; i++) {
                    const existing = existingOptions[i];
                    if (existing.textContent.trim().toLowerCase().localeCompare(label, undefined, { sensitivity: 'base' }) > 0) {
                        selectEl.insertBefore(opt, existing);
                        inserted = true;
                        break;
                    }
                }
                if (!inserted) {
                    selectEl.appendChild(opt);
                }
            }

            function appendCategoryToAllSelects(category) {
                document.querySelectorAll('[data-category-select], [data-brochure-category]').forEach(function (selectEl) {
                    appendCategoryOptionToSelect(selectEl, category);
                });
                const rowTemplate = document.getElementById('category-row-template');
                if (rowTemplate) {
                    const tplSelect = rowTemplate.content.querySelector('[data-category-select]');
                    appendCategoryOptionToSelect(tplSelect, category);
                }
                const brochureTemplate = document.getElementById('brochure-row-template');
                if (brochureTemplate) {
                    const tplSelect = brochureTemplate.content.querySelector('[data-brochure-category]');
                    appendCategoryOptionToSelect(tplSelect, category);
                }
            }

            function clearCategoryQuickAddErrors() {
                [categoryQuickAddErrNameAr, categoryQuickAddErrNameEn, categoryQuickAddErrGeneral].forEach(function (el) {
                    if (!el) {
                        return;
                    }
                    el.classList.add('hidden');
                    el.textContent = '';
                });
                [categoryQuickAddNameArInput, categoryQuickAddNameEnInput].forEach(function (el) {
                    if (!el) {
                        return;
                    }
                    el.classList.remove('border-red-500');
                });
            }

            function setCategoryFieldError(inputEl, errEl, message) {
                if (!inputEl || !errEl) {
                    return;
                }
                errEl.textContent = message;
                errEl.classList.remove('hidden');
                inputEl.classList.add('border-red-500');
            }

            function openCategoryQuickAddModal(row) {
                if (!categoryQuickAddModal || !categoryQuickAddForm) {
                    return false;
                }

                categoryQuickAddTargetRow = row;
                categoryQuickAddNameArInput.value = '';
                categoryQuickAddNameEnInput.value = '';
                clearCategoryQuickAddErrors();

                categoryQuickAddModal.classList.remove('hidden');
                categoryQuickAddModal.setAttribute('aria-hidden', 'false');
                document.body.classList.add('overflow-hidden');
                setTimeout(function () {
                    categoryQuickAddNameArInput.focus();
                }, 0);

                return true;
            }

            function closeCategoryQuickAddModal() {
                categoryQuickAddTargetRow = null;
                if (categoryQuickAddModal) {
                    categoryQuickAddModal.classList.add('hidden');
                    categoryQuickAddModal.setAttribute('aria-hidden', 'true');
                }
                document.body.classList.remove('overflow-hidden');
                clearCategoryQuickAddErrors();
            }

            if (categoryQuickAddCancelBtn) {
                categoryQuickAddCancelBtn.addEventListener('click', function () {
                    closeCategoryQuickAddModal();
                });
            }

            if (categoryQuickAddModal) {
                const categoryPanel = categoryQuickAddModal.querySelector('.relative');
                categoryQuickAddModal.addEventListener('click', function (e) {
                    if (categoryPanel && !categoryPanel.contains(e.target)) {
                        closeCategoryQuickAddModal();
                    }
                });
            }

            if (categoryQuickAddSaveBtn) {
                categoryQuickAddSaveBtn.addEventListener('click', async function () {
                    if (!categoryQuickAddTargetRow) {
                        return;
                    }

                    const nameAr = (categoryQuickAddNameArInput.value || '').trim();
                    const nameEn = (categoryQuickAddNameEnInput.value || '').trim();

                    clearCategoryQuickAddErrors();
                    if (!nameAr) {
                        setCategoryFieldError(categoryQuickAddNameArInput, categoryQuickAddErrNameAr, t('arabic_name_required'));
                        return;
                    }
                    if (!nameEn) {
                        setCategoryFieldError(categoryQuickAddNameEnInput, categoryQuickAddErrNameEn, t('english_name_required'));
                        return;
                    }

                    categoryQuickAddSaveBtn.disabled = true;

                    const formData = new FormData();
                    formData.append('name_ar', nameAr);
                    formData.append('name_en', nameEn);

                    try {
                        const res = await fetch(categoryQuickStoreUrl, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': csrfToken,
                                'Accept': 'application/json',
                            },
                            body: formData
                        });

                        const payload = await res.json().catch(function () {
                            return null;
                        });

                        if (!res.ok) {
                            const errors = payload && payload.errors ? payload.errors : null;
                            clearCategoryQuickAddErrors();
                            if (errors) {
                                if (errors.name_ar && errors.name_ar[0]) {
                                    setCategoryFieldError(categoryQuickAddNameArInput, categoryQuickAddErrNameAr, errors.name_ar[0]);
                                }
                                if (errors.name_en && errors.name_en[0]) {
                                    setCategoryFieldError(categoryQuickAddNameEnInput, categoryQuickAddErrNameEn, errors.name_en[0]);
                                }
                                if ((!errors.name_ar || !errors.name_ar[0]) && (!errors.name_en || !errors.name_en[0])) {
                                    categoryQuickAddErrGeneral.textContent = t('fix_highlighted_fields');
                                    categoryQuickAddErrGeneral.classList.remove('hidden');
                                }
                            } else {
                                categoryQuickAddErrGeneral.textContent = t('failed_create_category');
                                categoryQuickAddErrGeneral.classList.remove('hidden');
                            }
                            return;
                        }

                        const created = payload;
                        if (!created || !created.id) {
                            categoryQuickAddErrGeneral.textContent = t('server_no_category');
                            categoryQuickAddErrGeneral.classList.remove('hidden');
                            return;
                        }

                        const newId = created.id;
                        const catKey = String(newId);

                        if (!Array.isArray(subData[catKey])) {
                            subData[catKey] = [];
                        }

                        appendCategoryToAllSelects(created);

                        const targetRow = categoryQuickAddTargetRow;
                        closeCategoryQuickAddModal();

                        const catSelect = targetRow ? targetRow.querySelector('[data-category-select]') : null;
                        if (catSelect) {
                            catSelect.value = String(newId);
                            refreshCategorySubCheckboxes(targetRow, []);
                            const addBtn = targetRow.querySelector('[data-add-subcategory]');
                            if (addBtn) {
                                addBtn.disabled = false;
                            }
                        }
                    } finally {
                        categoryQuickAddSaveBtn.disabled = false;
                    }
                });
            }

            document.addEventListener('click', function (e) {
                const btn = e.target.closest('[data-add-category]');
                if (!btn) {
                    return;
                }
                const row = btn.closest('tr.category-row');
                if (!row) {
                    return;
                }
                openCategoryQuickAddModal(row);
            });

            function reindexVendorLocationRows() {
                const container = document.getElementById('vendor-location-rows');
                if (!container) {
                    return;
                }
                container.querySelectorAll('.vendor-location-row').forEach(function (row, i) {
                    row.setAttribute('data-location-index', String(i));
                    const label = row.querySelector('.vendor-location-branch-label');
                    if (label) {
                        label.textContent = t('branch', { num: i + 1 });
                    }
                    row.querySelectorAll('[name]').forEach(function (el) {
                        if (!el.name || el.name.indexOf('locations[') !== 0) {
                            return;
                        }
                        el.name = el.name.replace(/locations\[\d+\]/, 'locations[' + i + ']');
                    });
                    const radio = row.querySelector('input[name="primary_location_index"]');
                    if (radio) {
                        radio.value = String(i);
                    }
                });
            }

            function ensurePrimaryLocationRadios() {
                const container = document.getElementById('vendor-location-rows');
                if (!container) {
                    return;
                }
                if (container.querySelectorAll('.vendor-location-row').length === 0) {
                    return;
                }
                if (!container.querySelector('input[name="primary_location_index"]:checked')) {
                    const first = container.querySelector('input[name="primary_location_index"]');
                    if (first) {
                        first.checked = true;
                    }
                }
            }

            function reindexCategoryRows() {
                const tbodyEl = document.getElementById('category-rows');
                if (!tbodyEl) {
                    return;
                }
                tbodyEl.querySelectorAll('tr.category-row').forEach(function (row, i) {
                    row.setAttribute('data-row-index', String(i));
                    const select = row.querySelector('[data-category-select]');
                    if (select) {
                        select.name = 'categories[' + i + '][category_id]';
                    }
                    row.querySelectorAll('input[name^="categories["]').forEach(function (el) {
                        el.name = el.name.replace(/categories\[\d+\]/, 'categories[' + i + ']');
                    });
                    const selected = [];
                    const wrap = row.querySelector('[data-category-sub-checkboxes]');
                    if (wrap) {
                        wrap.querySelectorAll('input[type="checkbox"]:checked').forEach(function (cb) {
                            selected.push(parseInt(cb.value, 10));
                        });
                    }
                    refreshCategorySubCheckboxes(row, selected);
                    const cardLabel = row.querySelector('.category-row-card-label');
                    if (cardLabel) {
                        cardLabel.textContent = t('category_row_label', { num: i + 1 });
                    }
                });
            }

            function reindexBrochureUploadRows() {
                const container = document.getElementById('brochure-new-rows');
                if (!container) {
                    return;
                }
                container.querySelectorAll('.brochure-upload-row').forEach(function (row, i) {
                    row.setAttribute('data-brochure-index', String(i));
                    const heading = row.querySelector('.brochure-upload-heading');
                    if (heading) {
                        heading.textContent = t('new_brochure', { num: i + 1 });
                    }
                    row.querySelectorAll('[name]').forEach(function (el) {
                        if (!el.name || el.name.indexOf('brochure_rows[') !== 0) {
                            return;
                        }
                        el.name = el.name.replace(/brochure_rows\[\d+\]/, 'brochure_rows[' + i + ']');
                    });
                });
            }

            const vendorForm = document.getElementById('vendor-form');
            if (vendorForm) {
                vendorForm.addEventListener('submit', function () {
                    reindexVendorLocationRows();
                    reindexCategoryRows();
                    reindexBrochureUploadRows();
                });
            }

            const tbody = document.getElementById('category-rows');
            const template = document.getElementById('category-row-template');
            const addBtn = document.getElementById('add-category-row');
            if (tbody && template && addBtn) {
                const rows = tbody.querySelectorAll('tr.category-row');
                rows.forEach(function (row, i) {
                    wireVendorCategoryRow(row);
                    const meta = categoryInitial[i] || {};
                    refreshCategorySubCheckboxes(row, meta.subcategory_ids || []);
                });

                tbody.addEventListener('click', function (e) {
                    const btn = e.target.closest('[data-remove-category-row]');
                    if (!btn) {
                        return;
                    }
                    const row = btn.closest('tr.category-row');
                    if (!row) {
                        return;
                    }
                    if (row.getAttribute('data-category-row-persisted') === '1') {
                        if (!window.confirm(t('confirm_remove_category_row'))) {
                            return;
                        }
                    }
                    row.remove();
                    reindexCategoryRows();
                });

                addBtn.addEventListener('click', function () {
                    const idx = tbody.querySelectorAll('tr.category-row').length;
                    let html = template.innerHTML.replaceAll('__IDX__', String(idx));
                    html = html.replaceAll('__NUM__', String(idx + 1));
                    tbody.insertAdjacentHTML('beforeend', html);
                    const row = tbody.lastElementChild;
                    wireVendorCategoryRow(row);
                    refreshCategorySubCheckboxes(row, []);
                });
            }

            function cityOptionsForCountry(countryId) {
                const list = citiesByCountry[countryId] || citiesByCountry[String(countryId)] || [];
                const frag = document.createDocumentFragment();
                const empty = document.createElement('option');
                empty.value = '';
                empty.textContent = '—';
                frag.appendChild(empty);
                list.forEach(function (item) {
                    const opt = document.createElement('option');
                    opt.value = String(item.id);
                    opt.textContent = item.name;
                    frag.appendChild(opt);
                });
                return frag;
            }

            function refreshVendorLocationCities(row, selectedCityId) {
                const countryEl = row.querySelector('[data-vendor-location-country]');
                const cityEl = row.querySelector('[data-vendor-location-city]');
                if (!countryEl || !cityEl) {
                    return;
                }
                const cid = countryEl.value;
                cityEl.innerHTML = '';
                cityEl.appendChild(cityOptionsForCountry(cid));
                if (selectedCityId) {
                    cityEl.value = String(selectedCityId);
                    if (cityEl.value !== String(selectedCityId)) {
                        cityEl.value = '';
                    }
                }
            }

            function wireVendorLocationRow(row) {
                const countryEl = row.querySelector('[data-vendor-location-country]');
                if (!countryEl) {
                    return;
                }
                countryEl.addEventListener('change', function () {
                    refreshVendorLocationCities(row, null);
                });
            }

            const locContainer = document.getElementById('vendor-location-rows');
            const locTemplate = document.getElementById('vendor-location-row-template');
            const addLocBtn = document.getElementById('add-vendor-location-row');
            if (locContainer && locTemplate && addLocBtn) {
                locContainer.querySelectorAll('.vendor-location-row').forEach(function (row, i) {
                    wireVendorLocationRow(row);
                    const meta = locationInitial[i] || {};
                    refreshVendorLocationCities(row, meta.city_id || '');
                });

                locContainer.addEventListener('click', function (e) {
                    const btn = e.target.closest('[data-remove-vendor-location]');
                    if (!btn) {
                        return;
                    }
                    const row = btn.closest('.vendor-location-row');
                    if (!row) {
                        return;
                    }
                    if (row.getAttribute('data-persisted-location-id')) {
                        if (!window.confirm(t('confirm_remove_branch'))) {
                            return;
                        }
                    }
                    row.remove();
                    reindexVendorLocationRows();
                    ensurePrimaryLocationRadios();
                });

                addLocBtn.addEventListener('click', function () {
                    const nextLoc = locContainer.querySelectorAll('.vendor-location-row').length;
                    let html = locTemplate.innerHTML.replaceAll('__LIDX__', String(nextLoc));
                    html = html.replaceAll('__NUM__', String(nextLoc + 1));
                    locContainer.insertAdjacentHTML('beforeend', html);
                    const row = locContainer.lastElementChild;
                    wireVendorLocationRow(row);
                    refreshVendorLocationCities(row, '');
                    const radio = row.querySelector('input[type="radio"][name="primary_location_index"]');
                    if (radio) {
                        radio.checked = true;
                    }
                });
            }

            function wireBrochureRow(row, selectedSubId) {
                const cat = row.querySelector('[data-brochure-category]');
                const sub = row.querySelector('[data-brochure-subcategory]');
                if (!cat || !sub) {
                    return;
                }
                function refill(sel) {
                    const cid = cat.value;
                    sub.innerHTML = '';
                    sub.appendChild(optionsForProcurementCategory(cid));
                    if (sel) {
                        sub.value = String(sel);
                        if (sub.value !== String(sel)) {
                            sub.value = '';
                        }
                    }
                }
                cat.addEventListener('change', function () {
                    refill(null);
                });
                refill(selectedSubId || '');
            }

            const brochureContainer = document.getElementById('brochure-new-rows');
            const brochureTemplate = document.getElementById('brochure-row-template');
            const addBrochureBtn = document.getElementById('add-brochure-row');
            if (brochureContainer && brochureTemplate && addBrochureBtn) {
                brochureContainer.querySelectorAll('.brochure-upload-row').forEach(function (row, i) {
                    const meta = brochureInitial[i] || {};
                    wireBrochureRow(row, meta.subcategory_id || '');
                });

                brochureContainer.addEventListener('click', function (e) {
                    const btn = e.target.closest('[data-remove-brochure-upload-row]');
                    if (!btn) {
                        return;
                    }
                    const row = btn.closest('.brochure-upload-row');
                    if (!row) {
                        return;
                    }
                    row.remove();
                    reindexBrochureUploadRows();
                });

                addBrochureBtn.addEventListener('click', function () {
                    const nextBrochureIndex = brochureContainer.querySelectorAll('.brochure-upload-row').length;
                    let html = brochureTemplate.innerHTML.replaceAll('__BIDX__', String(nextBrochureIndex));
                    html = html.replaceAll('__BNUM__', String(nextBrochureIndex + 1));
                    brochureContainer.insertAdjacentHTML('beforeend', html);
                    const row = brochureContainer.lastElementChild;
                    wireBrochureRow(row, '');
                });
            }

            document.addEventListener('click', function (e) {
                const btn = e.target.closest('[data-remove-existing-brochure]');
                if (!btn) {
                    return;
                }
                const id = btn.getAttribute('data-brochure-id');
                if (!id) {
                    return;
                }
                if (!window.confirm(t('confirm_remove_brochure'))) {
                    return;
                }
                const stash = document.getElementById('brochure-removal-stash');
                if (stash) {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'remove_brochure_ids[]';
                    input.value = id;
                    stash.appendChild(input);
                }
                const li = btn.closest('li[data-existing-brochure-id]');
                if (li) {
                    li.remove();
                }
            });
        });
    </script>
@endpush
