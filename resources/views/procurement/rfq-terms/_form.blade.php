@php
    use App\Support\Procurement\ProcurementScopeType;

    $term = $term ?? null;
    $scopeTypeOptions = $scopeTypes ?? ProcurementScopeType::options();
    $selectedScopeTypes = ProcurementScopeType::selectedValues(old('scope_types', $term?->scope_types));

    $splitTermBody = function (?string $text): array {
        $raw = trim((string) ($text ?? ''));
        if ($raw === '') {
            return ['key' => '', 'value' => ''];
        }

        $parts = explode(':', $raw, 2);
        if (count($parts) < 2) {
            return ['key' => '', 'value' => $raw];
        }

        return [
            'key' => trim($parts[0]),
            'value' => trim($parts[1]),
        ];
    };

    $bodyArParts = $splitTermBody(old('body_ar', $term?->body_ar));
    $bodyEnParts = $splitTermBody(old('body_en', $term?->body_en));
@endphp

<section class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
    <div class="space-y-4">
        <div>
            <span class="block text-xs font-medium uppercase tracking-wide text-slate-500">Scope types</span>
            <p class="mt-1 text-xs text-slate-500">Leave all unchecked for general terms on every RFQ. Select one or more scope types for extra terms when any of those scopes appear on RFQ lines.</p>
            <div class="mt-3 grid gap-2 sm:grid-cols-2 @error('scope_types') rounded-lg ring-1 ring-red-500 p-2 @enderror @error('scope_types.*') rounded-lg ring-1 ring-red-500 p-2 @enderror">
                @foreach ($scopeTypeOptions as $scopeType)
                    <label class="inline-flex items-center gap-2 text-sm text-slate-800">
                        <input type="checkbox"
                               name="scope_types[]"
                               value="{{ $scopeType }}"
                               class="rounded border-slate-300 text-slate-900 focus:ring-slate-500"
                               @checked(in_array($scopeType, $selectedScopeTypes, true))>
                        <span>{{ $scopeType }}</span>
                    </label>
                @endforeach
            </div>
            @error('scope_types')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            @error('scope_types.*')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
        </div>
        <div class="grid gap-4 md:grid-cols-2">
            <div class="space-y-2">
                <label for="key_ar" class="block text-xs font-medium uppercase tracking-wide text-slate-500">Key (Arabic)</label>
                <input type="text" name="key_ar" id="key_ar" dir="rtl"
                       value="{{ old('key_ar', $bodyArParts['key']) }}"
                       class="admin-filter-control @error('key_ar') border-red-500 @enderror">
                @error('key_ar')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror

                <label for="value_ar" class="block text-xs font-medium uppercase tracking-wide text-slate-500">Value (Arabic)</label>
                <textarea name="value_ar" id="value_ar" rows="4" dir="rtl"
                          class="admin-form-textarea @error('value_ar') border-red-500 @enderror">{{ old('value_ar', $bodyArParts['value']) }}</textarea>
                @error('value_ar')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>

            <div class="space-y-2">
                <label for="key_en" class="block text-xs font-medium uppercase tracking-wide text-slate-500">Key (English)</label>
                <input type="text" name="key_en" id="key_en"
                       value="{{ old('key_en', $bodyEnParts['key']) }}"
                       class="admin-filter-control @error('key_en') border-red-500 @enderror">
                @error('key_en')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror

                <label for="value_en" class="block text-xs font-medium uppercase tracking-wide text-slate-500">Value (English)</label>
                <textarea name="value_en" id="value_en" rows="4"
                          class="admin-form-textarea @error('value_en') border-red-500 @enderror">{{ old('value_en', $bodyEnParts['value']) }}</textarea>
                @error('value_en')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
        </div>
        <div>
            @error('body_ar')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            @error('body_en')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            <p class="mt-1 text-xs text-slate-500">Provide at least one value (Arabic or English). Existing legacy terms are loaded into Value automatically.</p>
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
