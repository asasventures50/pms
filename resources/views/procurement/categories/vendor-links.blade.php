@extends('layouts.admin')

@section('title', 'Category Vendors')

@section('content')
    @php
        $isSubcategoryScope = $subcategory !== null;
        $scopeLabel = $isSubcategoryScope
            ? trim($subcategory->name_ar.' — '.$subcategory->name_en, ' —')
            : trim($category->name_ar.' — '.$category->name_en, ' —');
    @endphp

    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <h1 class="text-2xl font-semibold tracking-tight text-slate-900">Linked vendors</h1>
            <p class="mt-1 text-sm text-slate-600" dir="auto">{{ $scopeLabel }}</p>
            @if ($isSubcategoryScope)
                <p class="mt-1 text-xs text-slate-500">Subcategory under {{ $category->name_en }}</p>
            @else
                <p class="mt-1 text-xs text-slate-500">Whole category (no specific subcategory)</p>
            @endif
        </div>
        <div class="flex flex-wrap gap-3">
            <a href="{{ route('categories.show', $category) }}"
               class="inline-flex items-center justify-center rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-800 hover:bg-slate-50">Back to category</a>
        </div>
    </div>

    <p class="mb-4 text-sm text-slate-600">
        Move vendors to another classification, or remove the link from here only.
        If a vendor is already linked to the target subcategory, reassign will remove this duplicate link instead of failing.
        Procurement requests keep their original classification and are not changed.
    </p>

    @if ($vendorLinks->isEmpty())
        <div class="rounded-xl border border-slate-200 bg-white p-10 text-center shadow-sm">
            <p class="text-sm text-slate-500">No vendors linked to this classification.</p>
        </div>
    @else
        <div class="space-y-4">
            @foreach ($vendorLinks as $link)
                @php
                    $brochureCount = (int) ($link->matching_brochures_count ?? 0);
                @endphp
                <section class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm">
                    <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                        <div class="min-w-0">
                            <div class="flex flex-wrap items-center gap-2">
                                <a href="{{ route('vendors.show', $link->vendor) }}"
                                   class="text-base font-semibold text-slate-900 hover:underline">{{ $link->vendor->name }}</a>
                                @if ($link->is_primary)
                                    <span class="inline-flex rounded-full bg-slate-900 px-2 py-0.5 text-xs font-medium text-white">Primary</span>
                                @endif
                            </div>
                            <p class="mt-1 font-mono text-xs text-slate-500">{{ $link->vendor->vendor_code }}</p>
                            @if ($brochureCount > 0)
                                <p class="mt-2 text-xs text-slate-500">{{ $brochureCount }} brochure(s) tagged with this classification.</p>
                            @endif
                            @if ($link->other_links_in_category->isNotEmpty())
                                <p class="mt-2 text-xs text-amber-700">
                                    Also linked under this category:
                                    @foreach ($link->other_links_in_category as $otherLink)
                                        @if ($otherLink->subcategory_id === null)
                                            whole category
                                        @else
                                            {{ $otherLink->subcategory->name_en }}
                                        @endif
                                        @if (! $loop->last), @endif
                                    @endforeach
                                </p>
                            @endif
                        </div>

                        <div class="w-full max-w-xl space-y-3">
                        <form action="{{ route('vendor-categories.reassign', $link) }}" method="post"
                              class="space-y-3 rounded-lg border border-slate-100 bg-slate-50/60 p-4"
                              onsubmit="return confirm('Move this vendor to the selected classification? Procurement requests will not be changed.');">
                            @csrf
                            @method('PUT')
                            <input type="hidden" name="return_url" value="{{ url()->current() }}">

                            <div class="grid gap-3 sm:grid-cols-2">
                                <div>
                                    <label class="block text-xs font-medium uppercase tracking-wide text-slate-500">Target category</label>
                                    <select name="target_category_id" required data-target-category-select
                                            class="admin-filter-control mt-1 @error('target_category_id') border-red-500 @enderror">
                                        <option value="">—</option>
                                        @foreach ($catalogCategories as $cat)
                                            <option value="{{ $cat->id }}" @selected((int) old('target_category_id', $category->id) === (int) $cat->id)>
                                                {{ $cat->name_ar }} — {{ $cat->name_en }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('target_category_id')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                                </div>
                                <div>
                                    <label class="block text-xs font-medium uppercase tracking-wide text-slate-500">Target subcategory</label>
                                    <select name="target_subcategory_id" data-target-subcategory-select
                                            class="admin-filter-control mt-1 @error('target_subcategory_id') border-red-500 @enderror">
                                        <option value="">Whole category</option>
                                    </select>
                                    @error('target_subcategory_id')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                                </div>
                            </div>

                            @if ($brochureCount > 0)
                                <label class="flex items-start gap-2 text-sm text-slate-700">
                                    <input type="checkbox" name="update_brochures" value="1"
                                           class="mt-0.5 rounded border-slate-300 text-slate-900 focus:ring-slate-500"
                                           @checked(old('update_brochures'))>
                                    <span>Also update this vendor's {{ $brochureCount }} brochure(s) to the new classification</span>
                                </label>
                            @endif

                            <button type="submit"
                                    class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-slate-800">
                                Reassign vendor
                            </button>
                        </form>

                        <form action="{{ route('vendor-categories.destroy', $link) }}" method="post"
                              class="rounded-lg border border-red-100 bg-red-50/40 p-4"
                              onsubmit="return confirm('Remove this vendor from this classification only? The vendor record is not deleted.');">
                            @csrf
                            @method('DELETE')
                            <input type="hidden" name="return_url" value="{{ url()->current() }}">
                            @if ($brochureCount > 0)
                                <label class="mb-3 flex items-start gap-2 text-sm text-slate-700">
                                    <input type="checkbox" name="update_brochures" value="1"
                                           class="mt-0.5 rounded border-slate-300 text-slate-900 focus:ring-slate-500">
                                    <span>Clear category tags on {{ $brochureCount }} matching brochure(s)</span>
                                </label>
                            @endif
                            <button type="submit"
                                    class="text-sm font-medium text-red-700 hover:text-red-900">
                                Remove from this classification
                            </button>
                        </form>
                        </div>
                    </div>
                </section>
            @endforeach
        </div>
    @endif
@endsection

@push('scripts')
    <script type="application/json" id="category-vendor-link-config">@json(['subcategoriesByCategory' => $subcategoriesByCategory])</script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const config = JSON.parse(document.getElementById('category-vendor-link-config').textContent);
            const subcategoriesByCategory = config.subcategoriesByCategory || {};

            function populateSubcategories(select, categoryId, selectedSubcategoryId) {
                if (!select) {
                    return;
                }

                const subs = subcategoriesByCategory[String(categoryId)] || subcategoriesByCategory[categoryId] || [];
                select.innerHTML = '';

                const whole = document.createElement('option');
                whole.value = '';
                whole.textContent = 'Whole category';
                select.appendChild(whole);

                subs.forEach(function (sub) {
                    const option = document.createElement('option');
                    option.value = String(sub.id);
                    option.textContent = sub.label;
                    if (selectedSubcategoryId !== null && selectedSubcategoryId !== undefined && String(selectedSubcategoryId) === String(sub.id)) {
                        option.selected = true;
                    }
                    select.appendChild(option);
                });
            }

            document.querySelectorAll('form[action*="vendor-categories"]').forEach(function (form) {
                const categorySelect = form.querySelector('[data-target-category-select]');
                const subcategorySelect = form.querySelector('[data-target-subcategory-select]');
                if (!categorySelect || !subcategorySelect) {
                    return;
                }

                populateSubcategories(subcategorySelect, categorySelect.value, @json(old('target_subcategory_id')));

                categorySelect.addEventListener('change', function () {
                    populateSubcategories(subcategorySelect, categorySelect.value, null);
                });
            });
        });
    </script>
@endpush
