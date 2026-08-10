@php($city = $city ?? null)

<div class="grid gap-4 md:grid-cols-2">
    <div class="md:col-span-2">
        <label for="country_id" class="block text-xs font-medium uppercase tracking-wide text-slate-500">Country <span class="text-red-600">*</span></label>
        <select id="country_id" name="country_id" required class="admin-filter-control @error('country_id') border-red-500 @enderror">
            <option value="">Select country</option>
            @foreach ($countries as $country)
                <option value="{{ $country->id }}" @selected((string) old('country_id', $city?->country_id) === (string) $country->id)>
                    {{ $country->name_ar }} — {{ $country->name_en }}
                </option>
            @endforeach
        </select>
        @error('country_id')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
    </div>

    <div>
        <label for="name_ar" class="block text-xs font-medium uppercase tracking-wide text-slate-500">Arabic Name <span class="text-red-600">*</span></label>
        <input type="text" id="name_ar" name="name_ar" required value="{{ old('name_ar', $city?->name_ar ?? '') }}"
               class="admin-filter-control @error('name_ar') border-red-500 @enderror">
        @error('name_ar')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
    </div>

    <div>
        <label for="name_en" class="block text-xs font-medium uppercase tracking-wide text-slate-500">English Name <span class="text-red-600">*</span></label>
        <input type="text" id="name_en" name="name_en" required value="{{ old('name_en', $city?->name_en ?? '') }}"
               class="admin-filter-control @error('name_en') border-red-500 @enderror">
        @error('name_en')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
    </div>

    <div>
        <label for="status" class="block text-xs font-medium uppercase tracking-wide text-slate-500">Status <span class="text-red-600">*</span></label>
        <select id="status" name="status" required class="admin-filter-control @error('status') border-red-500 @enderror">
            <option value="active" @selected(old('status', $city?->status ?? 'active') === 'active')>Active</option>
            <option value="inactive" @selected(old('status', $city?->status) === 'inactive')>Inactive</option>
        </select>
        @error('status')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
    </div>
</div>

<div class="mt-6 flex items-center gap-3">
    <button type="submit" class="rounded-lg bg-brand px-4 py-2 text-sm font-medium text-white hover:bg-brand-hover">
        Save City
    </button>
    <a href="{{ route(\Illuminate\Support\Facades\Route::has('locations.index') ? 'locations.index' : 'countries.index') }}" class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">
        Cancel
    </a>
</div>
