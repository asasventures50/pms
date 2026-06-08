@php
    $prQuickStoreUrls = [
        'project' => auth()->user()->hasPermission('projects.create') ? route('projects.quick-store') : null,
        'zone' => auth()->user()->hasPermission('projects.update') ? route('zones.quick-store') : null,
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
    const zoneSelect = document.getElementById('pr_zone_id');
    const categorySelect = document.getElementById('pr_category_id');
    const subcategorySelect = document.getElementById('pr_subcategory_id');
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

    function syncZones() {
        if (!projectSelect || !zoneSelect) return;
        const projectId = projectSelect.value;
        const hasProject = Boolean(projectId);
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
        document.querySelector('[data-pr-add-zone]')?.toggleAttribute('disabled', !hasProject);
    }

    function syncSubcategories() {
        if (!categorySelect || !subcategorySelect) return;
        const categoryId = categorySelect.value;
        const hasCategory = Boolean(categoryId);
        subcategorySelect.disabled = !hasCategory;
        const allowed = categoryMap[categoryId] || [];
        const allowedIds = allowed.map(function (s) { return String(s.id); });
        subcategorySelect.querySelectorAll('option').forEach(function (option) {
            if (!option.value) return;
            const matches = hasCategory && allowedIds.includes(option.value);
            option.hidden = !matches;
            option.disabled = !matches;
        });
        if (!hasCategory || !allowedIds.includes(subcategorySelect.value)) subcategorySelect.value = '';
    }

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

    projectSelect?.addEventListener('change', syncZones);
    categorySelect?.addEventListener('change', syncSubcategories);
    syncZones();
    syncSubcategories();

    currencyInput?.addEventListener('input', function () {
        currencyInput.value = currencyInput.value.toUpperCase().replace(/[^A-Z]/g, '').slice(0, 3);
    });

    const prSection = document.querySelector('.pr-document');
    document.querySelector('[data-pr-add-project]')?.addEventListener('click', function () {
        if (quickStoreUrls.project) window.prQuickAddProject?.(prSection);
    });
    document.querySelector('[data-pr-add-zone]')?.addEventListener('click', function () {
        if (quickStoreUrls.zone) window.prQuickAddZone?.(prSection);
    });
});
</script>
@include('procurement.procurement-requests._form-scripts-quick-add')
