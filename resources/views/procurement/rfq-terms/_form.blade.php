@php
    use App\Support\Procurement\ProcurementScopeType;

    $term = $term ?? null;
    $scopeTypes = $scopeTypes ?? ProcurementScopeType::options();
@endphp

<section class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
    <div class="space-y-4">
        <div>
            <label for="scope_type" class="block text-xs font-medium uppercase tracking-wide text-slate-500">Scope type <span class="text-red-600">*</span></label>
            <select name="scope_type" id="scope_type"
                    class="admin-filter-control @error('scope_type') border-red-500 @enderror">
                <option value="" @selected(old('scope_type', $term?->scope_type) === null || old('scope_type', $term?->scope_type) === '')>General (all RFQs)</option>
                @foreach ($scopeTypes as $scopeType)
                    <option value="{{ $scopeType }}" @selected(old('scope_type', $term?->scope_type) === $scopeType)>{{ $scopeType }}</option>
                @endforeach
            </select>
            <p class="mt-1 text-xs text-slate-500">General applies to every RFQ. Choose a scope type only for extra terms when that scope appears on a line.</p>
            @error('scope_type')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>
        <div>
            <label for="body_ar" class="block text-xs font-medium uppercase tracking-wide text-slate-500">Arabic text</label>
            <textarea name="body_ar" id="body_ar" rows="4" dir="rtl"
                      class="admin-form-textarea @error('body_ar') border-red-500 @enderror">{{ old('body_ar', $term?->body_ar ?? '') }}</textarea>
            @error('body_ar')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>
        <div>
            <label for="body_en" class="block text-xs font-medium uppercase tracking-wide text-slate-500">English text</label>
            <textarea name="body_en" id="body_en" rows="4"
                      class="admin-form-textarea @error('body_en') border-red-500 @enderror">{{ old('body_en', $term?->body_en ?? '') }}</textarea>
            @error('body_en')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            <p class="mt-1 text-xs text-slate-500">Provide at least one language. On the RFQ, users pick Arabic or English to display terms.</p>
        </div>
        <div class="grid gap-4 sm:grid-cols-2">
            <div>
                <label for="sort_order" class="block text-xs font-medium uppercase tracking-wide text-slate-500">Sort order</label>
                <input type="number" name="sort_order" id="sort_order" min="0" max="65535"
                       value="{{ old('sort_order', $term?->sort_order ?? 0) }}"
                       class="admin-filter-control @error('sort_order') border-red-500 @enderror">
                @error('sort_order')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <span class="block text-xs font-medium uppercase tracking-wide text-slate-500">Active</span>
                <label class="mt-2 inline-flex items-center gap-2 text-sm text-slate-800">
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" name="is_active" value="1"
                           @checked(old('is_active', $term?->is_active ?? true))
                           class="rounded border-slate-300 text-slate-900 focus:ring-slate-500">
                    Include on new RFQs
                </label>
                @error('is_active')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
        </div>
    </div>
</section>
