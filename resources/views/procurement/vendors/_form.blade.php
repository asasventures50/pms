@php
    use App\Enums\Procurement\Vendors\CompanyType;
    use App\Enums\Procurement\Vendors\CoverageType;
    use App\Enums\Procurement\Vendors\PaymentMethod;
    use App\Enums\Procurement\Vendors\PricingFrequency;
    use App\Enums\Procurement\Vendors\RfqMethod;
    use App\Enums\Procurement\Vendors\VendorBusinessType;
    use App\Enums\Procurement\Vendors\VendorLanguage;
    use App\Enums\Procurement\Vendors\VendorStatus;

    $v = $vendor ?? null;

    if ($mode === 'edit' && $v) {
        $orderedVc = $v->vendorCategories->sortBy('id')->values();
        $defaultCategoryRows = $orderedVc->map(fn ($vc) => [
            'category_id' => $vc->category_id,
            'subcategory_id' => $vc->subcategory_id,
        ])->all();
        if (count($defaultCategoryRows) === 0) {
            $defaultCategoryRows = [['category_id' => '', 'subcategory_id' => '']];
        }
    } else {
        $defaultCategoryRows = [['category_id' => '', 'subcategory_id' => '']];
    }

    $categoryRows = old('categories', $defaultCategoryRows);

    $primaryIdx = old('primary_category_index');
    if ($primaryIdx === null) {
        if ($mode === 'edit' && $v) {
            $orderedVc = $v->vendorCategories->sortBy('id')->values();
            $found = $orderedVc->search(fn ($vc) => $vc->is_primary);
            $primaryIdx = $found === false ? 0 : $found;
        } else {
            $primaryIdx = 0;
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

    $countriesCollection = $countries ?? collect();
    $defaultCountryId = $defaultCountryId ?? null;
    $defaultCityId = $defaultCityId ?? null;
    $suggestedVendorCode = $suggestedVendorCode ?? '';

    $citiesByCountry = $countriesCollection->mapWithKeys(fn ($c) => [
        $c->id => $c->cities->map(fn ($city) => ['id' => $city->id, 'name' => $city->name])->values(),
    ]);

    $selectedCountryId = old('country_id', $v?->country_id ?? $defaultCountryId);
    $selectedCityId = old('city_id', $v?->city_id ?? $defaultCityId);

    $subcategoriesByCategory = $categories->mapWithKeys(fn ($c) => [
        $c->id => $c->subcategories->map(fn ($s) => [
            'id' => $s->id,
            'name_ar' => $s->name_ar,
            'name_en' => $s->name_en,
        ])->values(),
    ]);

    $categoryInitialForJs = collect($categoryRows)->map(function ($row, $index) {
        return [
            'index' => $index,
            'category_id' => old("categories.$index.category_id", $row['category_id'] ?? ''),
            'subcategory_id' => old("categories.$index.subcategory_id", $row['subcategory_id'] ?? ''),
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

{{-- 1. Basic Information --}}
<section class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
    <h2 class="border-b border-slate-100 pb-3 text-base font-semibold text-slate-900">Basic Information</h2>
    <div class="mt-4 grid gap-4 md:grid-cols-2">
        <div>
            <label for="vendor_code" class="block text-xs font-medium uppercase tracking-wide text-slate-500">Vendor code</label>
            <input type="text" name="vendor_code" id="vendor_code"
                   value="{{ old('vendor_code', $mode === 'create' ? $suggestedVendorCode : ($v?->vendor_code ?? '')) }}"
                   autocomplete="off"
                   placeholder="e.g. VND-0001"
                   class="admin-filter-control @error('vendor_code') border-red-500 @enderror">
            <p class="mt-1 text-xs text-slate-500">Leave blank to auto-generate the next code. You can edit or replace it manually.</p>
            @error('vendor_code')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>
        <div>
            <label for="name" class="block text-xs font-medium uppercase tracking-wide text-slate-500">Vendor name <span class="text-red-600">*</span></label>
            <input type="text" name="name" id="name" required
                   value="{{ old('name', $v?->name ?? '') }}"
                   class="admin-filter-control @error('name') border-red-500 @enderror">
            @error('name')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>
        <div>
            <label for="language" class="block text-xs font-medium uppercase tracking-wide text-slate-500">Language <span class="text-red-600">*</span></label>
            <select name="language" id="language" required
                    class="admin-filter-control @error('language') border-red-500 @enderror">
                @foreach (VendorLanguage::cases() as $case)
                    <option value="{{ $case->value }}" @selected(old('language', ($v?->language instanceof \BackedEnum) ? $v->language->value : ($v?->language ?? 'en')) === $case->value)>{{ strtoupper($case->value) }}</option>
                @endforeach
            </select>
            @error('language')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>
        <div>
            <label for="status" class="block text-xs font-medium uppercase tracking-wide text-slate-500">Status <span class="text-red-600">*</span></label>
            <select name="status" id="status" required
                    class="admin-filter-control @error('status') border-red-500 @enderror">
                @foreach (VendorStatus::cases() as $case)
                    <option value="{{ $case->value }}" @selected(old('status', ($v?->status instanceof \BackedEnum) ? $v->status->value : ($v?->status ?? 'pending_review')) === $case->value)>{{ \Illuminate\Support\Str::headline(str_replace('_', ' ', $case->value)) }}</option>
                @endforeach
            </select>
            @error('status')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>
        <div class="md:col-span-2">
            <label for="description" class="block text-xs font-medium uppercase tracking-wide text-slate-500">Description</label>
            <textarea name="description" id="description" rows="3"
                      class="admin-form-textarea @error('description') border-red-500 @enderror">{{ old('description', $v?->description ?? '') }}</textarea>
            @error('description')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>
        <div class="md:col-span-2">
            <label for="notes" class="block text-xs font-medium uppercase tracking-wide text-slate-500">Notes</label>
            <textarea name="notes" id="notes" rows="2"
                      class="admin-form-textarea @error('notes') border-red-500 @enderror">{{ old('notes', $v?->notes ?? '') }}</textarea>
            @error('notes')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>
    </div>
</section>

{{-- 2. Location Information --}}
<section class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
    <h2 class="border-b border-slate-100 pb-3 text-base font-semibold text-slate-900">Location Information</h2>
    @if ($countriesCollection->isEmpty())
        <div class="mt-4 rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-950">
            <p class="font-medium">No countries loaded</p>
            <p class="mt-1 text-amber-900/90">Countries and cities are stored in the database and filled by the geo seeder. From your project root run:</p>
            <p class="mt-2 font-mono text-xs text-amber-950/90">php artisan db:seed --class=Database\Seeders\Geo\CountryCitySeeder</p>
            <p class="mt-2 text-xs text-amber-900/80">Ensure <span class="font-mono">.env</span> points to your database (<span class="font-mono">DB_CONNECTION=mysql</span>, etc.), then refresh this page.</p>
        </div>
    @endif
    <div class="mt-4 grid gap-4 md:grid-cols-2">
        <div>
            <label for="country_id" class="block text-xs font-medium uppercase tracking-wide text-slate-500">Country</label>
            <select name="country_id" id="country_id" data-location-country
                    class="admin-filter-control @error('country_id') border-red-500 @enderror">
                <option value="">—</option>
                @foreach ($countriesCollection as $country)
                    <option value="{{ $country->id }}" @selected((string) $selectedCountryId === (string) $country->id)>
                        {{ $country->flag_emoji ? $country->flag_emoji.' ' : '' }}{{ $country->name }}
                    </option>
                @endforeach
            </select>
            @error('country_id')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>
        <div>
            <label for="city_id" class="block text-xs font-medium uppercase tracking-wide text-slate-500">City</label>
            @if ($countriesCollection->isNotEmpty())
                <p class="mb-1 text-xs text-slate-500">Cities depend on the selected country.</p>
            @endif
            <select name="city_id" id="city_id" data-location-city
                    class="admin-filter-control @error('city_id') border-red-500 @enderror">
                <option value="">—</option>
            </select>
            @error('city_id')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>
        <div class="md:col-span-2">
            <label for="address" class="block text-xs font-medium uppercase tracking-wide text-slate-500">Address</label>
            <textarea name="address" id="address" rows="2"
                      class="admin-form-textarea @error('address') border-red-500 @enderror">{{ old('address', $v?->address ?? '') }}</textarea>
            @error('address')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>
    </div>
</section>

{{-- 3. Contact Information --}}
<section class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
    <h2 class="border-b border-slate-100 pb-3 text-base font-semibold text-slate-900">Contact Information</h2>
    <div class="mt-4 grid gap-4 md:grid-cols-2">
        @foreach (['phone' => 'Phone', 'whatsapp' => 'WhatsApp', 'email' => 'Email', 'website' => 'Website'] as $field => $label)
            <div>
                <label for="{{ $field }}" class="block text-xs font-medium uppercase tracking-wide text-slate-500">{{ $label }}</label>
                <input type="{{ $field === 'email' ? 'email' : 'text' }}" name="{{ $field }}" id="{{ $field }}"
                       value="{{ old($field, $v?->{$field} ?? '') }}"
                       class="admin-filter-control @error($field) border-red-500 @enderror">
                @error($field)<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
        @endforeach
    </div>
</section>

{{-- 4. Primary Contact --}}
<section class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
    <h2 class="border-b border-slate-100 pb-3 text-base font-semibold text-slate-900">Primary Contact</h2>
    <div class="mt-4 grid gap-4 md:grid-cols-2">
        @foreach ([
            'primary_contact_name' => 'Name',
            'primary_contact_position' => 'Position',
            'primary_contact_phone' => 'Phone',
            'primary_contact_email' => 'Email',
        ] as $field => $label)
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
    <h2 class="border-b border-slate-100 pb-3 text-base font-semibold text-slate-900">Secondary Contact <span class="text-xs font-normal text-slate-500">(optional)</span></h2>
    <div class="mt-4 grid gap-4 md:grid-cols-2">
        @foreach ([
            'secondary_contact_name' => 'Name',
            'secondary_contact_position' => 'Position',
            'secondary_contact_phone' => 'Phone',
            'secondary_contact_email' => 'Email',
        ] as $field => $label)
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
    <h2 class="border-b border-slate-100 pb-3 text-base font-semibold text-slate-900">Procurement Information</h2>
    <div class="mt-4 grid gap-4 md:grid-cols-2">
        <div>
            <label for="pricing_frequency" class="block text-xs font-medium uppercase tracking-wide text-slate-500">Pricing frequency</label>
            <select name="pricing_frequency" id="pricing_frequency"
                    class="admin-filter-control @error('pricing_frequency') border-red-500 @enderror">
                <option value="">—</option>
                @foreach (PricingFrequency::cases() as $case)
                    <option value="{{ $case->value }}" @selected(old('pricing_frequency', ($v?->pricing_frequency instanceof \BackedEnum) ? $v->pricing_frequency->value : ($v?->pricing_frequency ?? '')) === $case->value)>{{ \Illuminate\Support\Str::headline(str_replace('_', ' ', $case->value)) }}</option>
                @endforeach
            </select>
            @error('pricing_frequency')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>
        <div>
            <label for="delivery_lead_time_days" class="block text-xs font-medium uppercase tracking-wide text-slate-500">Delivery lead time (days)</label>
            <input type="number" name="delivery_lead_time_days" id="delivery_lead_time_days" min="0" step="1"
                   value="{{ old('delivery_lead_time_days', $v?->delivery_lead_time_days ?? '') }}"
                   class="admin-filter-control @error('delivery_lead_time_days') border-red-500 @enderror">
            @error('delivery_lead_time_days')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>
        <div>
            <label for="execution_lead_time_days" class="block text-xs font-medium uppercase tracking-wide text-slate-500">Execution lead time (days)</label>
            <input type="number" name="execution_lead_time_days" id="execution_lead_time_days" min="0" step="1"
                   value="{{ old('execution_lead_time_days', $v?->execution_lead_time_days ?? '') }}"
                   class="admin-filter-control @error('execution_lead_time_days') border-red-500 @enderror">
            @error('execution_lead_time_days')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>
        <div>
            <label for="bulletin_price_validity_days" class="block text-xs font-medium uppercase tracking-wide text-slate-500">Bulletin price validity (days)</label>
            <input type="number" name="bulletin_price_validity_days" id="bulletin_price_validity_days" min="0" step="1"
                   value="{{ old('bulletin_price_validity_days', $v?->bulletin_price_validity_days ?? '') }}"
                   class="admin-filter-control @error('bulletin_price_validity_days') border-red-500 @enderror">
            @error('bulletin_price_validity_days')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>
        <div>
            <label for="currency_code" class="block text-xs font-medium uppercase tracking-wide text-slate-500">Currency code (ISO 4217)</label>
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
        <h3 class="text-sm font-semibold text-slate-900">RFQ method</h3>
        <p class="mt-1 text-xs text-slate-500">Select all that apply.</p>
        <div class="mt-3 grid gap-3 sm:grid-cols-2">
            @foreach (RfqMethod::cases() as $case)
                <label class="flex cursor-pointer items-center gap-2 text-sm text-slate-800">
                    <input type="checkbox" name="rfq_method[]" value="{{ $case->value }}"
                           id="rfq_method_{{ $case->value }}"
                           class="rounded border-slate-300 text-slate-900 focus:ring-slate-500"
                           @checked(in_array($case->value, $selectedRfqMethods, true))>
                    <span>{{ \Illuminate\Support\Str::headline(str_replace('_', ' ', $case->value)) }}</span>
                </label>
            @endforeach
        </div>
        @error('rfq_method')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
        @error('rfq_method.*')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
    </div>
</section>

{{-- 6. Commercial & Technical Information --}}
<section class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
    <h2 class="border-b border-slate-100 pb-3 text-base font-semibold text-slate-900">Commercial &amp; Technical Information</h2>
    <div class="mt-4 grid gap-4 md:grid-cols-2">
        <div>
            <label for="payment_method" class="block text-xs font-medium uppercase tracking-wide text-slate-500">Payment method</label>
            <select name="payment_method" id="payment_method"
                    class="admin-filter-control @error('payment_method') border-red-500 @enderror">
                <option value="">—</option>
                @foreach (PaymentMethod::cases() as $case)
                    <option value="{{ $case->value }}" @selected(old('payment_method', ($v?->payment_method instanceof \BackedEnum) ? $v->payment_method->value : ($v?->payment_method ?? '')) === $case->value)>{{ \Illuminate\Support\Str::headline(str_replace('_', ' ', $case->value)) }}</option>
                @endforeach
            </select>
            @error('payment_method')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>
        <div>
            <label for="rating" class="block text-xs font-medium uppercase tracking-wide text-slate-500">Rating</label>
            <input type="number" name="rating" id="rating" min="1" max="5" step="1"
                   value="{{ old('rating', $v?->rating ?? '') }}"
                   class="admin-filter-control @error('rating') border-red-500 @enderror">
            @error('rating')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>
        @foreach (['payment_terms' => 'Payment terms', 'commercial_terms' => 'Commercial terms', 'technical_capabilities' => 'Technical capabilities'] as $field => $label)
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
    <h2 class="border-b border-slate-100 pb-3 text-base font-semibold text-slate-900">Classification</h2>
    <div class="mt-4 grid gap-4 md:grid-cols-2">
        <div>
            <label for="company_type" class="block text-xs font-medium uppercase tracking-wide text-slate-500">Company type</label>
            <select name="company_type" id="company_type"
                    class="admin-filter-control @error('company_type') border-red-500 @enderror">
                <option value="">—</option>
                @foreach (CompanyType::cases() as $case)
                    <option value="{{ $case->value }}" @selected(old('company_type', ($v?->company_type instanceof \BackedEnum) ? $v->company_type->value : ($v?->company_type ?? '')) === $case->value)>{{ \Illuminate\Support\Str::headline($case->value) }}</option>
                @endforeach
            </select>
            @error('company_type')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>
        <div>
            <label for="coverage_type" class="block text-xs font-medium uppercase tracking-wide text-slate-500">Coverage type</label>
            <select name="coverage_type" id="coverage_type"
                    class="admin-filter-control @error('coverage_type') border-red-500 @enderror">
                <option value="">—</option>
                @foreach (CoverageType::cases() as $case)
                    <option value="{{ $case->value }}" @selected(old('coverage_type', ($v?->coverage_type instanceof \BackedEnum) ? $v->coverage_type->value : ($v?->coverage_type ?? '')) === $case->value)>{{ \Illuminate\Support\Str::headline($case->value) }}</option>
                @endforeach
            </select>
            @error('coverage_type')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>
        @foreach (['tax_number' => 'Tax number', 'registration_number' => 'Registration number', 'license_number' => 'License number'] as $field => $label)
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
        <h3 class="text-sm font-semibold text-slate-900">Business types</h3>
        <p class="mt-1 text-xs text-slate-500">Select all that apply.</p>
        <div class="mt-3 grid gap-3 sm:grid-cols-2">
            @foreach (VendorBusinessType::cases() as $case)
                <label class="flex items-center gap-2 text-sm text-slate-800">
                    <input type="checkbox" name="business_types[]" value="{{ $case->value }}"
                           class="rounded border-slate-300 text-slate-900 focus:ring-slate-500"
                        @checked(in_array($case->value, $selectedBusinessTypes, true))>
                    {{ \Illuminate\Support\Str::headline(str_replace('_', ' ', $case->value)) }}
                </label>
            @endforeach
        </div>
        @error('business_types')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
        @error('business_types.*')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
    </div>

    <div class="mt-8 border-t border-slate-100 pt-6">
        <h3 class="text-sm font-semibold text-slate-900">Categories &amp; subcategories</h3>
        <p class="mt-1 text-xs text-slate-500">Add one or more combinations. Mark exactly one row as primary (required when any category is selected).</p>
        @error('categories')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror

        <div class="mt-3 overflow-x-auto rounded-lg border border-slate-200">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50 text-xs font-semibold uppercase tracking-wide text-slate-500">
                <tr>
                    <th class="px-3 py-2 text-left">Primary</th>
                    <th class="px-3 py-2 text-left">Category</th>
                    <th class="px-3 py-2 text-left">Subcategory</th>
                </tr>
                </thead>
                <tbody id="category-rows" class="divide-y divide-slate-100 bg-white">
                @foreach ($categoryRows as $index => $row)
                    @php
                        $catId = old("categories.$index.category_id", $row['category_id'] ?? '');
                        $subId = old("categories.$index.subcategory_id", $row['subcategory_id'] ?? '');
                    @endphp
                    <tr class="category-row" data-row-index="{{ $index }}">
                        <td class="px-3 py-2 align-top">
                            <input type="radio" name="primary_category_index" value="{{ $index }}"
                                   class="mt-1 border-slate-300 text-slate-900 focus:ring-slate-500"
                                   @checked((int) $primaryIdx === (int) $index)>
                        </td>
                        <td class="px-3 py-2 align-top">
                            <select name="categories[{{ $index }}][category_id]" data-category-select
                                    class="admin-filter-control !mt-0 min-w-[10rem] @error('categories.'.$index.'.category_id') border-red-500 @enderror">
                                <option value="">—</option>
                                @foreach ($categories as $cat)
                                    <option value="{{ $cat->id }}" title="{{ $cat->name_en }}" @selected((string) $catId === (string) $cat->id)>{{ $cat->name_ar }} — {{ $cat->name_en }}</option>
                                @endforeach
                            </select>
                            @error('categories.'.$index.'.category_id')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                        </td>
                        <td class="px-3 py-2 align-top">
                            <select name="categories[{{ $index }}][subcategory_id]" data-subcategory-select
                                    class="admin-filter-control !mt-0 min-w-[10rem] @error('categories.'.$index.'.subcategory_id') border-red-500 @enderror">
                                <option value="">—</option>
                            </select>
                            @error('categories.'.$index.'.subcategory_id')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
        <button type="button" id="add-category-row"
                class="mt-3 rounded-lg border border-slate-300 bg-white px-3 py-1.5 text-sm font-medium text-slate-800 hover:bg-slate-50">
            Add category row
        </button>
    </div>
</section>

<template id="category-row-template">
    <tr class="category-row" data-row-index="__IDX__">
        <td class="px-3 py-2 align-top">
            <input type="radio" name="primary_category_index" value="__IDX__"
                   class="mt-1 border-slate-300 text-slate-900 focus:ring-slate-500">
        </td>
        <td class="px-3 py-2 align-top">
            <select name="categories[__IDX__][category_id]" data-category-select
                    class="admin-filter-control !mt-0 min-w-[10rem]">
                <option value="">—</option>
                @foreach ($categories as $cat)
                    <option value="{{ $cat->id }}" title="{{ $cat->name_en }}">{{ $cat->name_ar }} — {{ $cat->name_en }}</option>
                @endforeach
            </select>
        </td>
        <td class="px-3 py-2 align-top">
            <select name="categories[__IDX__][subcategory_id]" data-subcategory-select
                    class="admin-filter-control !mt-0 min-w-[10rem]">
                <option value="">—</option>
            </select>
        </td>
    </tr>
</template>

{{-- 8. Brochures --}}
<section class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
    <h2 class="border-b border-slate-100 pb-3 text-base font-semibold text-slate-900">Brochures</h2>
    <div class="mt-4 grid gap-4 md:grid-cols-2">
        <div class="md:col-span-2">
            <input type="hidden" name="is_brochure_available" value="0">
            <label class="flex items-center gap-2 text-sm text-slate-800">
                <input type="checkbox" name="is_brochure_available" value="1"
                       class="rounded border-slate-300 text-slate-900 focus:ring-slate-500"
                    @checked(old('is_brochure_available', $v?->is_brochure_available ?? false))>
                Brochure available (flag)
            </label>
            @error('is_brochure_available')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>
        @if ($mode === 'edit' && $v && $v->brochures->isNotEmpty())
            <div class="md:col-span-2">
                <h3 class="text-sm font-medium text-slate-800">Existing files</h3>
                <ul class="mt-2 divide-y divide-slate-100 rounded-lg border border-slate-200 text-sm">
                    @foreach ($v->brochures as $brochure)
                        <li class="flex flex-col gap-2 px-3 py-3">
                            <div class="font-medium text-slate-900">{{ $brochure->file_name }}</div>
                            <div class="break-all font-mono text-xs text-slate-500">{{ $brochure->file_path }}</div>
                            @if ($brochure->notes)
                                <div class="text-xs text-slate-600"><span class="font-medium text-slate-500">Notes:</span> {{ $brochure->notes }}</div>
                            @endif
                            @if ($brochure->category_id || $brochure->subcategory_id)
                                <div class="text-xs text-slate-600">
                                    <span class="font-medium text-slate-500">Linked:</span>
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
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif
        <div class="md:col-span-2 space-y-3">
            <h3 class="text-sm font-medium text-slate-800">Add new brochures</h3>
            <p class="text-xs text-slate-500">Each row is one file. Category and subcategory are optional (general vendor brochure if left empty).</p>
            <div id="brochure-new-rows" class="space-y-4">
                @for ($bi = 0; $bi < $brochureNewRowCount; $bi++)
                    <div class="brochure-upload-row rounded-lg border border-slate-200 bg-slate-50/50 p-4" data-brochure-index="{{ $bi }}">
                        <div class="grid gap-4 md:grid-cols-2">
                            <div class="md:col-span-2">
                                <label class="block text-xs font-medium uppercase tracking-wide text-slate-500">File</label>
                                <input type="file" name="brochure_rows[{{ $bi }}][file]"
                                       class="admin-form-file @error('brochure_rows.'.$bi.'.file') border-red-500 @enderror">
                                @error('brochure_rows.'.$bi.'.file')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                            </div>
                            <div class="md:col-span-2">
                                <label class="block text-xs font-medium uppercase tracking-wide text-slate-500">Notes</label>
                                <textarea name="brochure_rows[{{ $bi }}][notes]" rows="2"
                                          class="admin-form-textarea @error('brochure_rows.'.$bi.'.notes') border-red-500 @enderror">{{ old('brochure_rows.'.$bi.'.notes') }}</textarea>
                                @error('brochure_rows.'.$bi.'.notes')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label class="block text-xs font-medium uppercase tracking-wide text-slate-500">Category (optional)</label>
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
                                <label class="block text-xs font-medium uppercase tracking-wide text-slate-500">Subcategory (optional)</label>
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
                Add another brochure row
            </button>
            <p class="text-xs text-slate-500">PDF, images, Word, or Excel. Max 20 MB per file.</p>
            @error('brochure_rows')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            @error('brochures')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            @error('brochures.*')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>
    </div>
</section>

<template id="brochure-row-template">
    <div class="brochure-upload-row rounded-lg border border-slate-200 bg-slate-50/50 p-4" data-brochure-index="__BIDX__">
        <div class="grid gap-4 md:grid-cols-2">
            <div class="md:col-span-2">
                <label class="block text-xs font-medium uppercase tracking-wide text-slate-500">File</label>
                <input type="file" name="brochure_rows[__BIDX__][file]"
                       class="admin-form-file">
            </div>
            <div class="md:col-span-2">
                <label class="block text-xs font-medium uppercase tracking-wide text-slate-500">Notes</label>
                <textarea name="brochure_rows[__BIDX__][notes]" rows="2"
                          class="admin-form-textarea"></textarea>
            </div>
            <div>
                <label class="block text-xs font-medium uppercase tracking-wide text-slate-500">Category (optional)</label>
                <select name="brochure_rows[__BIDX__][category_id]" data-brochure-category
                        class="admin-filter-control">
                    <option value="">—</option>
                    @foreach ($categories as $cat)
                        <option value="{{ $cat->id }}" title="{{ $cat->name_en }}">{{ $cat->name_ar }} — {{ $cat->name_en }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium uppercase tracking-wide text-slate-500">Subcategory (optional)</label>
                <select name="brochure_rows[__BIDX__][subcategory_id]" data-brochure-subcategory
                        class="admin-filter-control">
                    <option value="">—</option>
                </select>
            </div>
        </div>
    </div>
</template>

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const subData = @json($subcategoriesByCategory);
            const initial = @json($categoryInitialForJs);
            const citiesByCountry = @json($citiesByCountry);
            const selectedCityIdInit = @json($selectedCityId);
            const brochureInitial = @json($brochureInitialForJs);

            function subcategoryList(categoryId) {
                if (!categoryId) {
                    return [];
                }
                return subData[categoryId] || subData[String(categoryId)] || [];
            }

            function optionsForProcurementCategory(categoryId) {
                const list = subcategoryList(categoryId);
                const frag = document.createDocumentFragment();
                const empty = document.createElement('option');
                empty.value = '';
                empty.textContent = '—';
                frag.appendChild(empty);
                list.forEach(function (item) {
                    const opt = document.createElement('option');
                    opt.value = String(item.id);
                    const ar = item.name_ar || '';
                    const en = item.name_en || '';
                    opt.textContent = en ? (ar + ' — ' + en) : ar;
                    if (en) {
                        opt.title = en;
                    }
                    frag.appendChild(opt);
                });
                return frag;
            }

            function refreshVendorCategorySubSelect(row, selectedSubId) {
                const catSelect = row.querySelector('[data-category-select]');
                const subSelect = row.querySelector('[data-subcategory-select]');
                if (!catSelect || !subSelect) {
                    return;
                }
                const categoryId = catSelect.value;
                subSelect.innerHTML = '';
                subSelect.appendChild(optionsForProcurementCategory(categoryId));
                if (selectedSubId) {
                    subSelect.value = String(selectedSubId);
                    if (subSelect.value !== String(selectedSubId)) {
                        subSelect.value = '';
                    }
                }
            }

            function wireVendorCategoryRow(row) {
                const catSelect = row.querySelector('[data-category-select]');
                if (!catSelect) {
                    return;
                }
                catSelect.addEventListener('change', function () {
                    refreshVendorCategorySubSelect(row, null);
                });
            }

            const tbody = document.getElementById('category-rows');
            const template = document.getElementById('category-row-template');
            const addBtn = document.getElementById('add-category-row');
            if (tbody && template && addBtn) {
                const rows = tbody.querySelectorAll('tr.category-row');
                rows.forEach(function (row, i) {
                    wireVendorCategoryRow(row);
                    const meta = initial[i] || {};
                    refreshVendorCategorySubSelect(row, meta.subcategory_id || '');
                });

                let nextIndex = rows.length;

                addBtn.addEventListener('click', function () {
                    const html = template.innerHTML.replaceAll('__IDX__', String(nextIndex));
                    tbody.insertAdjacentHTML('beforeend', html);
                    const row = tbody.lastElementChild;
                    wireVendorCategoryRow(row);
                    refreshVendorCategorySubSelect(row, '');
                    const radio = row.querySelector('input[type="radio"][name="primary_category_index"]');
                    if (radio) {
                        radio.checked = true;
                    }
                    nextIndex += 1;
                });
            }

            const countrySelect = document.querySelector('[data-location-country]');
            const citySelect = document.querySelector('[data-location-city]');
            if (countrySelect && citySelect) {
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

                function refreshCities(selectedCityId) {
                    const cid = countrySelect.value;
                    citySelect.innerHTML = '';
                    citySelect.appendChild(cityOptionsForCountry(cid));
                    if (selectedCityId) {
                        citySelect.value = String(selectedCityId);
                        if (citySelect.value !== String(selectedCityId)) {
                            citySelect.value = '';
                        }
                    }
                }

                countrySelect.addEventListener('change', function () {
                    refreshCities(null);
                });
                refreshCities(selectedCityIdInit || '');
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

                let nextBrochureIndex = brochureContainer.querySelectorAll('.brochure-upload-row').length;
                addBrochureBtn.addEventListener('click', function () {
                    const html = brochureTemplate.innerHTML.replaceAll('__BIDX__', String(nextBrochureIndex));
                    brochureContainer.insertAdjacentHTML('beforeend', html);
                    const row = brochureContainer.lastElementChild;
                    wireBrochureRow(row, '');
                    nextBrochureIndex += 1;
                });
            }
        });
    </script>
@endpush
