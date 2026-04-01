@php($country = $country ?? null)

<div class="grid gap-4 md:grid-cols-2">
    <div>
        <label for="name_ar" class="block text-xs font-medium uppercase tracking-wide text-slate-500">Arabic Name <span class="text-red-600">*</span></label>
        <input type="text" id="name_ar" name="name_ar" required
               value="{{ old('name_ar', $country?->name_ar ?? '') }}"
               class="admin-filter-control @error('name_ar') border-red-500 @enderror">
        @error('name_ar')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
    </div>

    <div>
        <label for="name_en" class="block text-xs font-medium uppercase tracking-wide text-slate-500">English Name <span class="text-red-600">*</span></label>
        <input type="text" id="name_en" name="name_en" required
               value="{{ old('name_en', $country?->name_en ?? '') }}"
               class="admin-filter-control @error('name_en') border-red-500 @enderror">
        @error('name_en')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
    </div>

    <div>
        <label for="iso_code" class="block text-xs font-medium uppercase tracking-wide text-slate-500">ISO Code</label>
        <input type="text" id="iso_code" name="iso_code" maxlength="8"
               value="{{ old('iso_code', $country?->iso_code ?? '') }}"
               class="admin-filter-control uppercase @error('iso_code') border-red-500 @enderror">
        @error('iso_code')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
    </div>

    <div>
        <label for="flag_emoji" class="block text-xs font-medium uppercase tracking-wide text-slate-500">Flag Emoji</label>
        <input type="text" id="flag_emoji" name="flag_emoji"
               value="{{ old('flag_emoji', $country?->flag_emoji ?? '') }}"
               class="admin-filter-control @error('flag_emoji') border-red-500 @enderror">
        @error('flag_emoji')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
    </div>

    <div>
        <label for="status" class="block text-xs font-medium uppercase tracking-wide text-slate-500">Status <span class="text-red-600">*</span></label>
        <select id="status" name="status" required
                class="admin-filter-control @error('status') border-red-500 @enderror">
            <option value="active" @selected(old('status', $country?->status ?? 'active') === 'active')>Active</option>
            <option value="inactive" @selected(old('status', $country?->status) === 'inactive')>Inactive</option>
        </select>
        @error('status')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
    </div>
</div>

<div class="mt-6 flex items-center gap-3">
    <button type="submit" class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-slate-800">
        Save Country
    </button>
    <a href="{{ route('locations.index') }}" class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-50">
        Cancel
    </a>
</div>
