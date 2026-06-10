<div id="add-category-modal"
     class="fixed inset-0 z-50 hidden"
     role="dialog"
     aria-modal="true"
     aria-labelledby="add-category-modal-title"
     aria-hidden="true">
    <div class="absolute inset-0 bg-slate-900/40"></div>
    <div class="relative z-10 flex min-h-full items-center justify-center p-4">
        <div class="w-[calc(100%-2rem)] max-w-lg rounded-2xl border border-slate-200 bg-white p-6 shadow-2xl ring-1 ring-black/5">
        <div class="flex items-start justify-between gap-4">
            <div class="min-w-0">
                <h3 id="add-category-modal-title" class="text-lg font-semibold tracking-tight text-slate-900">Add Category</h3>
                <p class="mt-1 text-sm text-slate-600">Create a new category without leaving this form.</p>
            </div>
        </div>

        <div id="add-category-form" class="mt-4 space-y-4">
            <div>
                <label for="add-category-name-ar" class="block text-xs font-medium uppercase tracking-wide text-slate-500">
                    Arabic Name <span class="text-red-600">*</span>
                </label>
                <input type="text"
                       id="add-category-name-ar"
                       name="name_ar"
                       dir="auto"
                       class="admin-filter-control !mt-1">
                <p id="add-category-error-name-ar" class="mt-1 text-sm text-red-600 hidden"></p>
            </div>

            <div>
                <label for="add-category-name-en" class="block text-xs font-medium uppercase tracking-wide text-slate-500">
                    English Name <span class="text-red-600">*</span>
                </label>
                <input type="text"
                       id="add-category-name-en"
                       name="name_en"
                       class="admin-filter-control !mt-1">
                <p class="mt-1 text-xs text-slate-500">Slug is generated automatically from the English name.</p>
                <p id="add-category-error-name-en" class="mt-1 text-sm text-red-600 hidden"></p>
            </div>

            <p id="add-category-error-general" class="text-sm text-red-600 hidden"></p>

            <div class="flex flex-wrap justify-end gap-2 border-t border-slate-100 pt-4">
                <button type="button"
                        id="add-category-cancel"
                        class="rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm font-medium text-slate-700 shadow-sm hover:bg-slate-50">
                    Cancel
                </button>
                <button type="button"
                        id="add-category-save"
                        class="rounded-lg bg-slate-900 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-slate-800 focus:outline-none focus:ring-2 focus:ring-slate-900 focus:ring-offset-2">
                    Save
                </button>
            </div>
        </div>
        </div>
    </div>
</div>
