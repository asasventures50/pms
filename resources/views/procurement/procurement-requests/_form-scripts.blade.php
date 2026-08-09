@php
    $prQuickStoreUrls = [
        'project' => auth()->user()->hasPermission('projects.create') ? route('projects.quick-store') : null,
        'zone' => auth()->user()->hasPermission('projects.update') ? route('zones.quick-store') : null,
        'subcategory' => (auth()->user()->hasPermission('categories.create') || auth()->user()->hasPermission('procurement-requests.create'))
            ? route('subcategories.quick-store')
            : null,
    ];
    $categorySubcategories = ($categories ?? collect())->mapWithKeys(fn ($cat) => [
        $cat->id => $cat->subcategories->map(fn ($sub) => [
            'id' => $sub->id,
            'label' => $sub->name_ar.' — '.$sub->name_en,
        ])->values()->all(),
    ])->all();
@endphp
<script type="application/json" id="pr-quick-store-urls">@json($prQuickStoreUrls)</script>
<script type="application/json" id="pr-category-subcategories">@json($categorySubcategories)</script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const quickStoreUrls = JSON.parse(document.getElementById('pr-quick-store-urls')?.textContent || '{}');
    const categoryMap = JSON.parse(document.getElementById('pr-category-subcategories')?.textContent || '{}');

    const projectSelect = document.getElementById('pr_project_id');
    const currencyInput = document.getElementById('currency_code');

    function reindexTableRows(tbody, rowSelector, prefix, fields) {
        if (!tbody) return;
        tbody.querySelectorAll(rowSelector).forEach(function (row, index) {
            fields.forEach(function (field) {
                const input = row.querySelector('[data-name="' + field + '"]');
                if (input) input.setAttribute('name', prefix + '[' + index + '][' + field + ']');
            });
            row.querySelectorAll('[data-name]').forEach(function (input) {
                const field = input.getAttribute('data-name');
                input.setAttribute('name', prefix + '[' + index + '][' + field + ']');
            });
        });
    }

    function updateBoqZoneHint(row) {
        const zoneSelect = row?.querySelector?.('[data-pr-zone-select]');
        const hint = row?.querySelector?.('[data-pr-zone-hint]');
        if (!zoneSelect || !hint) return;
        const hasProject = Boolean(projectSelect?.value);
        const selected = zoneSelect.selectedOptions[0];
        const label = selected?.dataset?.zoneLabel || (selected?.value ? selected.textContent.trim() : '');
        if (label && zoneSelect.value) {
            hint.textContent = label;
            hint.className = 'mt-1 text-xs font-medium leading-snug text-slate-800';
            zoneSelect.title = label;
        } else if (!hasProject) {
            hint.textContent = 'Project first';
            hint.className = 'mt-1 text-xs leading-snug text-slate-500';
            zoneSelect.title = '';
        } else {
            hint.textContent = '';
            hint.className = 'mt-1 text-xs leading-snug text-slate-500';
            zoneSelect.title = '';
        }
        const placeholder = zoneSelect.querySelector('option[value=""]');
        if (placeholder) {
            placeholder.textContent = hasProject ? '— Zone —' : '— Project first —';
        }
    }

    function syncBoqRowZones(row) {
        const projectId = projectSelect?.value || '';
        const hasProject = Boolean(projectId);
        const zoneSelect = row?.querySelector?.('[data-pr-zone-select]');
        if (!zoneSelect) return;
        zoneSelect.disabled = !hasProject;
        if (!hasProject) zoneSelect.value = '';
        zoneSelect.querySelectorAll('option').forEach(function (option) {
            if (!option.value) return;
            const matches = hasProject && option.dataset.projectId === projectId;
            option.hidden = !matches;
            option.disabled = !matches;
        });
        const selected = zoneSelect.selectedOptions[0];
        if (selected && (selected.disabled || selected.hidden)) zoneSelect.value = '';
        row.querySelector('[data-pr-add-zone]')?.toggleAttribute('disabled', !hasProject);
        updateBoqZoneHint(row);
    }

    function syncAllBoqZones() {
        boqBody?.querySelectorAll('.pr-boq-row').forEach(syncBoqRowZones);
    }

    function syncBoqRowSubcategories(row) {
        const categorySelect = row?.querySelector?.('[data-pr-category-select]');
        const subcategorySelect = row?.querySelector?.('[data-pr-subcategory-select]');
        if (!categorySelect || !subcategorySelect) return;

        const categoryId = categorySelect.value;
        const hasCategory = Boolean(categoryId);
        subcategorySelect.disabled = !hasCategory;

        subcategorySelect.querySelectorAll('option').forEach(function (option) {
            if (!option.value) return;
            const matches = hasCategory && option.dataset.categoryId === categoryId;
            option.hidden = !matches;
            option.disabled = !matches;
        });

        const selected = subcategorySelect.selectedOptions[0];
        if (!hasCategory || (selected && (selected.disabled || selected.hidden))) {
            subcategorySelect.value = '';
        }

        row.querySelector('[data-pr-add-subcategory]')?.toggleAttribute('disabled', !hasCategory);
    }

    function syncAllBoqSubcategories() {
        boqBody?.querySelectorAll('.pr-boq-row').forEach(syncBoqRowSubcategories);
    }

    window.prCategorySubcategoryMap = categoryMap;
    window.prSyncBoqRowZones = syncBoqRowZones;
    window.prSyncBoqRowSubcategories = syncBoqRowSubcategories;

    function recalcBoqRow(row) {
        const qty = parseFloat(row.querySelector('[data-pr-boq-qty]')?.value || '0') || 0;
        const unitPrice = parseFloat(row.querySelector('[data-pr-boq-unit-price]')?.value || '0') || 0;
        const totalInput = row.querySelector('[data-pr-boq-total]');
        if (totalInput) totalInput.value = (qty * unitPrice).toFixed(4);
    }

    const boqBody = document.getElementById('pr-boq-body');
    const boqTemplate = document.getElementById('pr-boq-row-template');

    function bindBoqRow(row) {
        row.querySelectorAll('[data-pr-boq-qty], [data-pr-boq-unit-price]').forEach(function (input) {
            input.addEventListener('input', function () { recalcBoqRow(row); });
        });
        row.querySelector('.pr-remove-boq-row')?.addEventListener('click', function () {
            if (boqBody.querySelectorAll('.pr-boq-row').length <= 1) return;
            row.remove();
            reindexTableRows(boqBody, '.pr-boq-row', 'items', []);
        });
        row.querySelector('[data-pr-add-zone]')?.addEventListener('click', function () {
            if (quickStoreUrls.zone) window.prQuickAddZone?.(row);
        });
        row.querySelector('[data-pr-zone-select]')?.addEventListener('change', function () {
            updateBoqZoneHint(row);
        });
        row.querySelector('[data-pr-category-select]')?.addEventListener('change', function () {
            syncBoqRowSubcategories(row);
        });
        row.querySelector('[data-pr-add-subcategory]')?.addEventListener('click', function () {
            if (quickStoreUrls.subcategory) window.prQuickAddSubcategory?.(row);
        });
        syncBoqRowZones(row);
        syncBoqRowSubcategories(row);
        recalcBoqRow(row);
    }

    document.getElementById('pr-add-boq-line')?.addEventListener('click', function () {
        const row = boqTemplate.content.firstElementChild.cloneNode(true);
        boqBody.appendChild(row);
        reindexTableRows(boqBody, '.pr-boq-row', 'items', []);
        bindBoqRow(row);
    });
    boqBody?.querySelectorAll('.pr-boq-row').forEach(bindBoqRow);

    const paymentBody = document.getElementById('pr-payment-terms-body');
    const paymentTemplate = document.getElementById('pr-payment-term-template');
    document.getElementById('pr-add-payment-term')?.addEventListener('click', function () {
        paymentBody.appendChild(paymentTemplate.content.firstElementChild.cloneNode(true));
        reindexTableRows(paymentBody, '.pr-payment-term-row', 'payment_terms', []);
        paymentBody.querySelectorAll('.pr-remove-payment-term').forEach(function (btn) {
            btn.onclick = function () {
                btn.closest('tr')?.remove();
                reindexTableRows(paymentBody, '.pr-payment-term-row', 'payment_terms', []);
            };
        });
    });
    paymentBody?.querySelectorAll('.pr-remove-payment-term').forEach(function (btn) {
        btn.addEventListener('click', function () {
            btn.closest('tr')?.remove();
            reindexTableRows(paymentBody, '.pr-payment-term-row', 'payment_terms', []);
        });
    });

    const retentionBody = document.getElementById('pr-retentions-body');
    const retentionTemplate = document.getElementById('pr-retention-template');
    document.getElementById('pr-add-retention')?.addEventListener('click', function () {
        retentionBody.appendChild(retentionTemplate.content.firstElementChild.cloneNode(true));
        reindexTableRows(retentionBody, '.pr-retention-row', 'retentions', []);
        retentionBody.querySelectorAll('.pr-remove-retention').forEach(function (btn) {
            btn.onclick = function () {
                btn.closest('tr')?.remove();
                reindexTableRows(retentionBody, '.pr-retention-row', 'retentions', []);
            };
        });
    });
    retentionBody?.querySelectorAll('.pr-remove-retention').forEach(function (btn) {
        btn.addEventListener('click', function () {
            btn.closest('tr')?.remove();
            reindexTableRows(retentionBody, '.pr-retention-row', 'retentions', []);
        });
    });

    const docRows = document.getElementById('pr-document-rows');
    const docTemplate = document.getElementById('pr-document-row-template');
    document.getElementById('pr-add-document-row')?.addEventListener('click', function () {
        docRows.appendChild(docTemplate.content.firstElementChild.cloneNode(true));
        reindexDocumentRows();
    });
    function reindexDocumentRows() {
        docRows?.querySelectorAll('.pr-document-row').forEach(function (row, index) {
            row.querySelectorAll('[data-name]').forEach(function (input) {
                const field = input.getAttribute('data-name');
                input.setAttribute('name', 'supporting_document_rows[' + index + '][' + field + ']');
            });
        });
        docRows?.querySelectorAll('.pr-remove-document-row').forEach(function (btn) {
            btn.onclick = function () {
                btn.closest('.pr-document-row')?.remove();
                reindexDocumentRows();
            };
        });
    }
    reindexDocumentRows();

    projectSelect?.addEventListener('change', syncAllBoqZones);
    syncAllBoqZones();
    syncAllBoqSubcategories();

    currencyInput?.addEventListener('input', function () {
        currencyInput.value = currencyInput.value.toUpperCase().replace(/[^A-Z]/g, '').slice(0, 3);
    });

    const prSection = document.querySelector('.pr-document');
    document.querySelector('[data-pr-add-project]')?.addEventListener('click', function () {
        if (quickStoreUrls.project) window.prQuickAddProject?.(prSection);
    });

    const companySelect = document.getElementById('company_key');
    const companyLogo = document.querySelector('[data-pr-company-logo]');
    const companyLogoFallback = document.querySelector('[data-pr-company-logo-fallback]');

    function syncCompanyLogo() {
        if (!companySelect || !companyLogo) return;
        const option = companySelect.options[companySelect.selectedIndex];
        const logoUrl = option?.dataset.logoUrl;
        const fallbackHtml = option?.dataset.logoFallback || '';
        if (logoUrl) {
            companyLogo.src = logoUrl;
            companyLogo.style.display = '';
        }
        if (companyLogoFallback) {
            companyLogoFallback.innerHTML = fallbackHtml;
            companyLogoFallback.style.display = 'none';
        }
        companyLogo.onerror = function () {
            companyLogo.style.display = 'none';
            if (companyLogoFallback) companyLogoFallback.style.display = 'block';
        };
    }

    companySelect?.addEventListener('change', syncCompanyLogo);

    const prequalLevelWrap = document.getElementById('pr-prequal-level-wrap');
    const prequalLevelSelect = document.getElementById('compliance_prequalification_level');

    function syncPrequalLevelVisibility() {
        if (!prequalLevelWrap) return;
        const yesSelected = document.querySelector('input[name="compliance_prequalification_required"][value="1"]')?.checked;
        prequalLevelWrap.classList.toggle('hidden', !yesSelected);
        if (!yesSelected && prequalLevelSelect) {
            prequalLevelSelect.value = '';
        }
    }

    document.querySelectorAll('input[name="compliance_prequalification_required"]').forEach(function (radio) {
        radio.addEventListener('change', syncPrequalLevelVisibility);
    });
    syncPrequalLevelVisibility();
});
</script>
@include('procurement.procurement-requests._form-scripts-quick-add')
