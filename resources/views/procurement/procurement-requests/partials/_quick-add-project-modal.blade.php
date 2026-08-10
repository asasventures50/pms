<div id="pr-add-project-modal"
     class="fixed inset-0 z-50 hidden"
     role="dialog"
     aria-modal="true"
     aria-labelledby="pr-add-project-modal-title"
     aria-hidden="true">
    <div class="absolute inset-0 bg-slate-900/40"></div>
    <div class="relative z-10 flex min-h-full items-center justify-center p-4">
        <div class="w-[calc(100%-2rem)] max-w-lg rounded-2xl border border-slate-200 bg-white p-6 shadow-2xl ring-1 ring-black/5">
            <h3 id="pr-add-project-modal-title" class="text-lg font-semibold tracking-tight text-slate-900">Add project</h3>
            <p class="mt-1 text-sm text-slate-600">Creates an active project and selects it on this line.</p>

            <div class="mt-4 space-y-4">
                <div>
                    <label for="pr-add-project-name" class="block text-xs font-medium uppercase tracking-wide text-slate-500">
                        Name <span class="text-red-600">*</span>
                    </label>
                    <input type="text" id="pr-add-project-name" class="admin-filter-control !mt-1">
                    <p id="pr-add-project-error-name" class="mt-1 hidden text-sm text-red-600"></p>
                </div>
                <p id="pr-add-project-error-general" class="hidden text-sm text-red-600"></p>
                <div class="flex flex-wrap justify-end gap-2 border-t border-slate-100 pt-4">
                    <button type="button" id="pr-add-project-cancel"
                            class="rounded-lg border border-slate-200 bg-white px-4 py-2 text-sm font-medium text-slate-700 shadow-sm hover:bg-slate-50">
                        Cancel
                    </button>
                    <button type="button" id="pr-add-project-save"
                            class="rounded-lg bg-brand px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-brand-hover">
                        Save
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
