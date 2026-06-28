<script>
(function () {
    const quickStoreUrls = JSON.parse(document.getElementById('pr-quick-store-urls')?.textContent || '{}');
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';

    function projectSelects() {
        return Array.from(document.querySelectorAll('[data-pr-project-select]'));
    }

    function zoneSelects() {
        return Array.from(document.querySelectorAll('[data-pr-zone-select]'));
    }

    function subcategorySelects() {
        return Array.from(document.querySelectorAll('[data-pr-subcategory-select]'));
    }

    function contextProjectSelect(context) {
        return context?.querySelector?.('[data-pr-project-select]')
            || document.getElementById('pr_project_id');
    }

    function contextZoneSelect(context) {
        return context?.querySelector?.('[data-pr-zone-select]')
            || document.getElementById('pr_zone_id');
    }

    function contextCategorySelect(context) {
        return context?.querySelector?.('[data-pr-category-select]')
            || document.getElementById('pr_category_id');
    }

    function contextSubcategorySelect(context) {
        return context?.querySelector?.('[data-pr-subcategory-select]')
            || document.getElementById('pr_subcategory_id');
    }

    function appendProjectOption(select, payload) {
        if (!select || select.querySelector('option[value="' + payload.id + '"]')) return;
        const opt = document.createElement('option');
        opt.value = payload.id;
        opt.textContent = payload.code + ' — ' + payload.name;
        select.appendChild(opt);
    }

    function appendZoneOption(select, payload) {
        if (!select || select.querySelector('option[value="' + payload.id + '"]')) return;
        const opt = document.createElement('option');
        opt.value = payload.id;
        opt.dataset.projectId = String(payload.project_id);
        opt.textContent = payload.code + ' — ' + payload.name;
        select.appendChild(opt);
    }

    function appendSubcategoryOption(select, payload) {
        if (!select || select.querySelector('option[value="' + payload.id + '"]')) return;
        const opt = document.createElement('option');
        opt.value = String(payload.id);
        opt.dataset.categoryId = String(payload.category_id);
        opt.textContent = payload.name_ar + ' — ' + payload.name_en;
        select.appendChild(opt);
    }

    function clearSubcategoryModalErrors() {
        ['pr-add-subcategory-error-name-ar', 'pr-add-subcategory-error-name-en', 'pr-add-subcategory-error-general'].forEach(function (id) {
            const el = document.getElementById(id);
            if (!el) return;
            el.classList.add('hidden');
            el.textContent = '';
        });
        ['pr-add-subcategory-name-ar', 'pr-add-subcategory-name-en'].forEach(function (id) {
            document.getElementById(id)?.classList.remove('border-red-500');
        });
    }

    function setSubcategoryFieldError(inputId, errorId, message) {
        const input = document.getElementById(inputId);
        const error = document.getElementById(errorId);
        if (!input || !error) return;
        error.textContent = message;
        error.classList.remove('hidden');
        input.classList.add('border-red-500');
    }

    window.prQuickAddProject = function (context) {
        const modal = document.getElementById('pr-add-project-modal');
        const input = document.getElementById('pr-add-project-name');
        if (!modal || !input || !quickStoreUrls.project) return;
        modal.dataset.prContext = context ? '1' : '';
        modal._prContextEl = context || null;
        input.value = '';
        modal.classList.remove('hidden');
        input.focus();
    };

    window.prQuickAddZone = function (context) {
        const modal = document.getElementById('pr-add-zone-modal');
        const projectSelect = contextProjectSelect(context);
        const projectId = projectSelect?.value || '';
        const projectInput = document.getElementById('pr-add-zone-project-id');
        if (!modal || !projectId || !projectInput || !quickStoreUrls.zone) return;
        modal._prContextEl = context || null;
        projectInput.value = projectId;
        document.getElementById('pr-add-zone-name').value = '';
        modal.classList.remove('hidden');
        document.getElementById('pr-add-zone-name')?.focus();
    };

    window.prQuickAddSubcategory = function (context) {
        const modal = document.getElementById('pr-add-subcategory-modal');
        const categorySelect = contextCategorySelect(context);
        const categoryId = categorySelect?.value || '';
        const categoryInput = document.getElementById('pr-add-subcategory-category-id');
        if (!modal || !categoryId || !categoryInput || !quickStoreUrls.subcategory) return;
        modal._prContextEl = context || null;
        categoryInput.value = categoryId;
        document.getElementById('pr-add-subcategory-name-ar').value = '';
        document.getElementById('pr-add-subcategory-name-en').value = '';
        clearSubcategoryModalErrors();
        modal.classList.remove('hidden');
        document.getElementById('pr-add-subcategory-name-ar')?.focus();
    };

    document.getElementById('pr-add-project-cancel')?.addEventListener('click', function () {
        document.getElementById('pr-add-project-modal')?.classList.add('hidden');
    });
    document.getElementById('pr-add-zone-cancel')?.addEventListener('click', function () {
        document.getElementById('pr-add-zone-modal')?.classList.add('hidden');
    });
    document.getElementById('pr-add-subcategory-cancel')?.addEventListener('click', function () {
        document.getElementById('pr-add-subcategory-modal')?.classList.add('hidden');
    });

    document.getElementById('pr-add-project-save')?.addEventListener('click', async function () {
        const modal = document.getElementById('pr-add-project-modal');
        const context = modal?._prContextEl || null;
        const nameInput = document.getElementById('pr-add-project-name');
        const name = (nameInput?.value || '').trim();
        if (!name || !quickStoreUrls.project) return;
        const btn = document.getElementById('pr-add-project-save');
        btn.disabled = true;
        try {
            const formData = new FormData();
            formData.append('name', name);
            const res = await fetch(quickStoreUrls.project, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
                body: formData,
            });
            const payload = await res.json();
            if (!res.ok) return;
            projectSelects().forEach(function (select) {
                appendProjectOption(select, payload);
            });
            const targetSelect = contextProjectSelect(context);
            if (targetSelect) {
                targetSelect.value = String(payload.id);
                targetSelect.dispatchEvent(new Event('change'));
            }
            modal?.classList.add('hidden');
        } finally {
            btn.disabled = false;
        }
    });

    document.getElementById('pr-add-zone-save')?.addEventListener('click', async function () {
        const modal = document.getElementById('pr-add-zone-modal');
        const context = modal?._prContextEl || null;
        const projectId = document.getElementById('pr-add-zone-project-id')?.value || '';
        const name = (document.getElementById('pr-add-zone-name')?.value || '').trim();
        if (!projectId || !name || !quickStoreUrls.zone) return;
        const btn = document.getElementById('pr-add-zone-save');
        btn.disabled = true;
        try {
            const formData = new FormData();
            formData.append('project_id', projectId);
            formData.append('name', name);
            const res = await fetch(quickStoreUrls.zone, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
                body: formData,
            });
            const payload = await res.json();
            if (!res.ok) return;
            zoneSelects().forEach(function (select) {
                appendZoneOption(select, payload);
            });
            const targetSelect = contextZoneSelect(context);
            if (targetSelect) {
                targetSelect.value = String(payload.id);
            }
            modal?.classList.add('hidden');
        } finally {
            btn.disabled = false;
        }
    });

    document.getElementById('pr-add-subcategory-save')?.addEventListener('click', async function () {
        const modal = document.getElementById('pr-add-subcategory-modal');
        const context = modal?._prContextEl || null;
        const categoryId = document.getElementById('pr-add-subcategory-category-id')?.value || '';
        const nameAr = (document.getElementById('pr-add-subcategory-name-ar')?.value || '').trim();
        const nameEn = (document.getElementById('pr-add-subcategory-name-en')?.value || '').trim();
        if (!categoryId || !quickStoreUrls.subcategory) return;

        clearSubcategoryModalErrors();
        if (!nameAr) {
            setSubcategoryFieldError('pr-add-subcategory-name-ar', 'pr-add-subcategory-error-name-ar', 'Arabic name is required.');
            return;
        }
        if (!nameEn) {
            setSubcategoryFieldError('pr-add-subcategory-name-en', 'pr-add-subcategory-error-name-en', 'English name is required.');
            return;
        }

        const btn = document.getElementById('pr-add-subcategory-save');
        btn.disabled = true;
        try {
            const formData = new FormData();
            formData.append('category_id', categoryId);
            formData.append('name_ar', nameAr);
            formData.append('name_en', nameEn);
            const res = await fetch(quickStoreUrls.subcategory, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
                body: formData,
            });
            const payload = await res.json().catch(function () { return null; });
            if (!res.ok) {
                const errors = payload?.errors || {};
                if (errors.name_ar?.[0]) {
                    setSubcategoryFieldError('pr-add-subcategory-name-ar', 'pr-add-subcategory-error-name-ar', errors.name_ar[0]);
                }
                if (errors.name_en?.[0]) {
                    setSubcategoryFieldError('pr-add-subcategory-name-en', 'pr-add-subcategory-error-name-en', errors.name_en[0]);
                }
                if (!errors.name_ar?.[0] && !errors.name_en?.[0]) {
                    const general = document.getElementById('pr-add-subcategory-error-general');
                    if (general) {
                        general.textContent = 'Unable to create subcategory. Please check the fields and try again.';
                        general.classList.remove('hidden');
                    }
                }
                return;
            }

            const categoryMap = window.prCategorySubcategoryMap || {};
            const catKey = String(categoryId);
            if (!Array.isArray(categoryMap[catKey])) {
                categoryMap[catKey] = [];
            }
            const label = payload.name_ar + ' — ' + payload.name_en;
            const alreadyExists = categoryMap[catKey].some(function (item) {
                return String(item.id) === String(payload.id);
            });
            if (!alreadyExists) {
                categoryMap[catKey].push({ id: payload.id, label: label });
            }

            const optionPayload = {
                id: payload.id,
                category_id: categoryId,
                name_ar: payload.name_ar,
                name_en: payload.name_en,
            };
            subcategorySelects().forEach(function (select) {
                appendSubcategoryOption(select, optionPayload);
            });

            const targetSelect = contextSubcategorySelect(context);
            if (targetSelect) {
                targetSelect.value = String(payload.id);
            }
            window.prSyncSubcategories?.();
            modal?.classList.add('hidden');
        } finally {
            btn.disabled = false;
        }
    });
})();
</script>
