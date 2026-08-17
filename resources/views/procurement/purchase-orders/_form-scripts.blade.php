@include('procurement.partials._vendor-search-scripts')

<script>
(function () {
document.addEventListener('DOMContentLoaded', function () {
    const linesBody = document.getElementById('po-lines-body');
    const template = document.getElementById('po-line-template');
    const addBtn = document.getElementById('po-add-line');
    const subtotalEl = document.getElementById('po-lines-subtotal');
    const totalPriceEl = document.getElementById('po-total-price');
    const deliveryFeeInput = document.getElementById('delivery_fee');
    const discountInput = document.getElementById('discount');
    const procurementRequestSelect = document.getElementById('procurement_request_id');
    const currencyInput = document.getElementById('currency_code');
    const userDefaultCurrency = (currencyInput?.dataset.userDefaultCurrency || '').trim().toUpperCase();

    function applyBilingualDirection(field) {
        const text = field.value || '';
        const hasArabic = /[\u0600-\u06FF\u0750-\u077F\u08A0-\u08FF]/.test(text);
        if (hasArabic) {
            field.setAttribute('dir', 'rtl');
            field.setAttribute('lang', 'ar');
        } else {
            field.removeAttribute('dir');
            field.removeAttribute('lang');
        }
    }

    document.querySelectorAll('.po-bilingual-text').forEach(function (field) {
        applyBilingualDirection(field);
        field.addEventListener('input', function () {
            applyBilingualDirection(field);
        });
    });

    document.querySelectorAll('.po-terms-locale').forEach(function (radio) {
        radio.addEventListener('change', function () {
            if (!radio.checked) {
                return;
            }
            document.querySelectorAll('.po-bilingual-text').forEach(function (field) {
                if (radio.value === 'ar') {
                    field.setAttribute('dir', 'rtl');
                    field.setAttribute('lang', 'ar');
                } else if (radio.value === 'en') {
                    applyBilingualDirection(field);
                }
            });
        });
    });

    const poVendorSnapshotFields = [
        'vendor_company_name',
        'vendor_email',
        'vendor_phone',
        'vendor_whatsapp',
        'vendor_primary_contact_position',
        'vendor_classification',
        'currency_code',
    ];

    async function loadVendorSnapshot(vendorId) {
        if (!vendorId) {
            if (typeof window.applyVendorSnapshotFields === 'function') {
                window.applyVendorSnapshotFields({}, {
                    fields: poVendorSnapshotFields,
                    resolveCurrency: function () {
                        return userDefaultCurrency || '';
                    },
                    applyBilingualDirection: applyBilingualDirection,
                    onApplied: updateCurrencyLabels,
                });
            }
            return;
        }

        const data = typeof window.fetchVendorSnapshot === 'function'
            ? await window.fetchVendorSnapshot(vendorId, 'purchase-order-snapshot')
            : null;

        if (!data || typeof window.applyVendorSnapshotFields !== 'function') {
            return;
        }

        window.applyVendorSnapshotFields(data, {
            fields: poVendorSnapshotFields,
            resolveCurrency: currencyFromVendor,
            applyBilingualDirection: applyBilingualDirection,
            onApplied: updateCurrencyLabels,
        });
    }

    if (typeof window.initVendorSearchSelect === 'function') {
        window.initVendorSearchSelect({ onChange: loadVendorSnapshot });

        const preselectedVendorId = document.getElementById('vendor_id')?.value;
        if (preselectedVendorId && typeof window.fetchVendorSnapshot === 'function') {
            window.fetchVendorSnapshot(preselectedVendorId, 'purchase-order-snapshot').then(function (data) {
                if (!data || typeof window.applyVendorSnapshotFields !== 'function') {
                    return;
                }

                window.applyVendorSnapshotFields(data, {
                    fields: poVendorSnapshotFields,
                    onlyIfEmpty: true,
                    resolveCurrency: currencyFromVendor,
                    applyBilingualDirection: applyBilingualDirection,
                    onApplied: updateCurrencyLabels,
                });
            });
        }
    }

    if (!linesBody || !template) {
        return;
    }

    function currencyFromVendor(vendorCode) {
        const fromVendor = (vendorCode || '').trim().toUpperCase();
        return fromVendor || userDefaultCurrency || '';
    }

    function formatMoney(value) {
        return (Math.round(value * 100) / 100).toFixed(2);
    }

    function effectiveCurrencyCode() {
        const typed = (currencyInput?.value || '').trim().toUpperCase();
        if (typed) {
            return typed;
        }
        return userDefaultCurrency || '';
    }

    function updateCurrencyLabels() {
        const code = effectiveCurrencyCode();
        const suffix = code ? ' (' + code + ')' : '';

        document.querySelectorAll('[data-po-price-label]').forEach(function (el) {
            const base = el.getAttribute('data-po-price-label-base') || '';
            el.textContent = base + suffix;
        });
    }

    function applyUserDefaultIfEmpty() {
        if (!currencyInput || currencyInput.value.trim() || !userDefaultCurrency) {
            return;
        }
        currencyInput.value = userDefaultCurrency;
        updateCurrencyLabels();
    }

    function reindexRows() {
        linesBody.querySelectorAll('.po-line-row').forEach(function (row, index) {
            row.querySelectorAll('[data-name]').forEach(function (input) {
                const field = input.getAttribute('data-name');
                input.setAttribute('name', 'items[' + index + '][' + field + ']');
            });
            row.querySelectorAll('[name^="items["]').forEach(function (input) {
                const match = input.getAttribute('name').match(/items\[\d+]\[(\w+)]/);
                if (match) {
                    input.setAttribute('name', 'items[' + index + '][' + match[1] + ']');
                }
            });
        });
    }

    function recalcRow(row) {
        const qty = parseFloat(row.querySelector('.po-qty')?.value || '0');
        const unit = parseFloat(row.querySelector('.po-unit')?.value || '0');
        const total = qty * unit;
        const totalEl = row.querySelector('.po-line-total');
        if (totalEl) {
            totalEl.textContent = formatMoney(total);
        }
        return total;
    }

    function recalcAll() {
        let subtotal = 0;
        linesBody.querySelectorAll('.po-line-row').forEach(function (row) {
            subtotal += recalcRow(row);
        });

        const deliveryFee = parseFloat(deliveryFeeInput?.value || '0');
        const discount = parseFloat(discountInput?.value || '0');
        const totalPrice = Math.max(0, subtotal + deliveryFee - discount);

        if (subtotalEl) {
            subtotalEl.textContent = formatMoney(subtotal);
        }
        if (totalPriceEl) {
            totalPriceEl.textContent = formatMoney(totalPrice);
        }

        if (typeof window.poRecalcPaymentTermsFromTotal === 'function') {
            window.poRecalcPaymentTermsFromTotal(totalPrice);
        }
    }

    function bindRow(row) {
        row.querySelectorAll('.po-qty, .po-unit').forEach(function (input) {
            input.addEventListener('input', recalcAll);
        });
        const removeBtn = row.querySelector('.po-remove-line');
        if (removeBtn) {
            removeBtn.addEventListener('click', function () {
                const rows = linesBody.querySelectorAll('.po-line-row');
                if (rows.length <= 1) {
                    return;
                }
                row.remove();
                reindexRows();
                recalcAll();
            });
        }
    }

    function addRow() {
        const clone = template.content.cloneNode(true);
        const row = clone.querySelector('tr');
        linesBody.appendChild(row);
        reindexRows();
        bindRow(row);
        recalcAll();
    }

    function setItemFieldLocked(row, locked) {
        const input = row.querySelector('[data-name="item"], [name$="[item]"]');
        if (!input) {
            return;
        }

        const hasValue = (input.value || '').trim() !== '';
        const shouldLock = Boolean(locked && hasValue);

        input.readOnly = shouldLock;
        input.classList.toggle('bg-slate-50', shouldLock);
        input.classList.toggle('text-slate-600', shouldLock);
        input.classList.toggle('cursor-not-allowed', shouldLock);

        if (shouldLock) {
            input.setAttribute('data-item-locked', '1');
        } else {
            input.removeAttribute('data-item-locked');
        }
    }

    function addRowFromData(rowData) {
        const clone = template.content.cloneNode(true);
        const row = clone.querySelector('tr');
        if (!row) {
            return;
        }

        row.querySelector('[data-name="item"]').value = rowData.item || '';
        row.querySelector('[data-name="description"]').value = rowData.description || '';
        row.querySelector('[data-name="quantity"]').value = rowData.quantity ?? 1;
        row.querySelector('[data-name="unit"]').value = rowData.unit || '';
        row.querySelector('[data-name="unit_price"]').value = rowData.unit_price ?? 0;

        linesBody.appendChild(row);
        bindRow(row);
        setItemFieldLocked(row, true);
    }

    function replaceRowsFromProcurementRequest(rows) {
        linesBody.innerHTML = '';

        if (!Array.isArray(rows) || rows.length === 0) {
            addRow();
            return;
        }

        rows.forEach(function (rowData) {
            addRowFromData(rowData);
        });
        reindexRows();
        recalcAll();
    }

    function appendRowsFromProcurementRequest(rows) {
        if (!Array.isArray(rows) || rows.length === 0) {
            return;
        }

        const existingItems = new Set();
        linesBody.querySelectorAll('.po-line-row').forEach(function (row) {
            const itemCode = row.querySelector('[name$="[item]"]')?.value?.trim();
            if (itemCode) {
                existingItems.add(itemCode);
            }
        });

        let added = false;
        rows.forEach(function (rowData) {
            const itemCode = (rowData.item || '').trim();
            if (itemCode && existingItems.has(itemCode)) {
                return;
            }
            if (itemCode) {
                existingItems.add(itemCode);
            }
            addRowFromData(rowData);
            added = true;
        });

        if (added) {
            reindexRows();
            recalcAll();
        }
    }

    function linesBodyHasContent() {
        return Array.from(linesBody.querySelectorAll('.po-line-row')).some(function (row) {
            const item = row.querySelector('[name$="[item]"]')?.value?.trim();
            const description = row.querySelector('[name$="[description]"]')?.value?.trim();
            return Boolean(item || description);
        });
    }

    const prImportModal = document.getElementById('po-pr-import-modal');
    const prImportSubtitle = document.getElementById('po-pr-import-subtitle');
    const prImportEmpty = document.getElementById('po-pr-import-empty');
    const prImportTable = document.getElementById('po-pr-import-table');
    const prImportBody = document.getElementById('po-pr-import-body');
    const prImportSelectAll = document.getElementById('po-pr-import-select-all');
    const prImportConfirm = document.getElementById('po-pr-import-confirm');
    const poPrContextPanel = document.getElementById('po-pr-context');
    const poPrContextNumber = document.getElementById('po-pr-context-number');
    const poPrContextCompany = document.getElementById('po-pr-context-company');
    const poPrContextProcurementType = document.getElementById('po-pr-context-procurement-type');
    const poPrContextGeographicScope = document.getElementById('po-pr-context-geographic-scope');
    const poPrContextScopeType = document.getElementById('po-pr-context-scope-type');
    const poPrContextCategory = document.getElementById('po-pr-context-category');
    const poPrContextProject = document.getElementById('po-pr-context-project');
    const poPackageInput = document.getElementById('package');
    const poCompanyLogo = document.querySelector('[data-po-company-logo]');
    const poCompanyLogoFallback = document.querySelector('[data-po-company-logo-fallback]');
    const poCompanyLabel = document.querySelector('[data-po-company-label]');
    const poCompanyNameField = document.querySelector('[data-po-company-name]');
    const poCompanyPhoneField = document.querySelector('[data-po-company-phone]');
    const poCompanyEmailField = document.querySelector('[data-po-company-email]');
    const poCompanyAddressField = document.querySelector('[data-po-company-address]');
    const poCompanyFaxField = document.querySelector('[data-po-company-fax]');

    let poDefaultBuyerCompany = {};
    let poDefaultCompany = null;
    try {
        poDefaultBuyerCompany = JSON.parse(document.getElementById('po-default-buyer-company')?.textContent || '{}');
    } catch (e) {
        poDefaultBuyerCompany = {};
    }
    try {
        poDefaultCompany = JSON.parse(document.getElementById('po-default-company')?.textContent || 'null');
    } catch (e) {
        poDefaultCompany = null;
    }

    function displayValue(value) {
        const text = (value || '').trim();
        return text !== '' ? text : '—';
    }

    function applyPoCompanyPreview(company) {
        if (!company || typeof company !== 'object') {
            return;
        }

        if (poCompanyLabel) {
            poCompanyLabel.textContent = company.label || '—';
        }

        if (poCompanyLogo && company.logo_url) {
            poCompanyLogo.src = company.logo_url;
            poCompanyLogo.style.display = '';
            poCompanyLogo.onerror = function () {
                poCompanyLogo.style.display = 'none';
                if (poCompanyLogoFallback) {
                    poCompanyLogoFallback.innerHTML = company.logo_fallback_html || '';
                    poCompanyLogoFallback.style.display = 'block';
                }
            };
        }

        if (poCompanyLogoFallback) {
            poCompanyLogoFallback.innerHTML = company.logo_fallback_html || '';
            poCompanyLogoFallback.style.display = company.logo_exists ? 'none' : 'block';
        }

        const buyer = company.buyer || {};
        if (poCompanyNameField) {
            poCompanyNameField.textContent = displayValue(buyer.name);
        }
        if (poCompanyPhoneField) {
            poCompanyPhoneField.textContent = displayValue(buyer.phone);
        }
        if (poCompanyEmailField) {
            poCompanyEmailField.textContent = displayValue(buyer.email);
        }
        if (poCompanyAddressField) {
            poCompanyAddressField.textContent = displayValue(buyer.address);
        }
        if (poCompanyFaxField) {
            poCompanyFaxField.textContent = displayValue(buyer.fax);
        }
    }

    function resetPoCompanyPreview() {
        applyPoCompanyPreview(poDefaultCompany);
        if (poCompanyNameField) {
            poCompanyNameField.textContent = displayValue(poDefaultBuyerCompany.name);
        }
        if (poCompanyPhoneField) {
            poCompanyPhoneField.textContent = displayValue(poDefaultBuyerCompany.phone);
        }
        if (poCompanyEmailField) {
            poCompanyEmailField.textContent = displayValue(poDefaultBuyerCompany.email);
        }
        if (poCompanyAddressField) {
            poCompanyAddressField.textContent = displayValue(poDefaultBuyerCompany.address);
        }
        if (poCompanyFaxField) {
            poCompanyFaxField.textContent = displayValue(poDefaultBuyerCompany.fax);
        }
    }

    let poPrScopeTypeKeys = [];
    try {
        poPrScopeTypeKeys = JSON.parse(poPrContextPanel?.dataset.initialScopeTypeKeys || '[]');
    } catch (e) {
        poPrScopeTypeKeys = [];
    }

    function aggregateContextFromLines(lines) {
        const categories = new Set();
        const projects = new Set();
        const scopeKeys = new Set();
        const scopeLabels = new Set();

        (lines || []).forEach(function (line) {
            if (line.category) {
                categories.add(line.category);
            }
            if (line.project) {
                projects.add(line.project);
            }
            if (Array.isArray(line.scope_type_keys)) {
                line.scope_type_keys.forEach(function (key) {
                    if (key) {
                        scopeKeys.add(key);
                    }
                });
            } else if (line.scope_type_key) {
                scopeKeys.add(line.scope_type_key);
            }
            if (line.scope_type) {
                scopeLabels.add(line.scope_type);
            }
        });

        return {
            category: Array.from(categories).join('; '),
            project: Array.from(projects).join('; '),
            scopeType: Array.from(scopeLabels).join(', '),
            scopeTypeKeys: Array.from(scopeKeys),
        };
    }

    function updatePoPrContextPanel(requestNumber, context) {
        const payload = context || {};
        const hasContent = Boolean(
            requestNumber
            || payload.company
            || payload.procurementType
            || payload.geographicScope
            || payload.category
            || payload.scopeType
            || payload.project
        );

        if (poPrContextPanel) {
            poPrContextPanel.classList.toggle('hidden', !hasContent);
        }
        if (poPrContextNumber) {
            poPrContextNumber.textContent = requestNumber || '—';
        }
        if (poPrContextCompany) {
            poPrContextCompany.textContent = payload.company || '—';
        }
        if (poPrContextProcurementType) {
            poPrContextProcurementType.textContent = payload.procurementType || '—';
        }
        if (poPrContextGeographicScope) {
            poPrContextGeographicScope.textContent = payload.geographicScope || '—';
        }
        if (poPrContextScopeType) {
            poPrContextScopeType.textContent = payload.scopeType || '—';
        }
        if (poPrContextCategory) {
            poPrContextCategory.textContent = payload.category || '—';
        }
        if (poPrContextProject) {
            poPrContextProject.textContent = payload.project || '—';
        }

        maybePrefillPackageFromPr(payload.package || '');

        poPrScopeTypeKeys = Array.isArray(payload.scopeTypeKeys) ? payload.scopeTypeKeys : [];
        if (typeof window.poSyncGeneralTerms === 'function') {
            window.poSyncGeneralTerms();
        }
    }

    function maybePrefillPackageFromPr(prPackage) {
        if (!poPackageInput || poPackageInput.dataset.userEdited === '1') {
            return;
        }

        const current = (poPackageInput.value || '').trim();
        const incoming = (prPackage || '').trim();

        if (current === '' && incoming !== '') {
            poPackageInput.value = incoming;
        }
    }

    if (poPackageInput) {
        poPackageInput.addEventListener('input', function () {
            poPackageInput.dataset.userEdited = '1';
        });
    }

    function clearPoPrContextPanel() {
        updatePoPrContextPanel('', {
            company: '',
            procurementType: '',
            geographicScope: '',
            category: '',
            project: '',
            scopeType: '',
            scopeTypeKeys: [],
        });
        resetPoCompanyPreview();
    }

    function contextFromProcurementRequestResponse(data, selectedLines) {
        const aggregated = aggregateContextFromLines(selectedLines);
        const requestContext = data?.context || {};

        return {
            company: requestContext.company || '',
            category: aggregated.category || requestContext.category || '',
            project: aggregated.project || requestContext.project || '',
            scopeType: aggregated.scopeType || requestContext.scope_type || '',
            scopeTypeKeys: aggregated.scopeTypeKeys.length > 0
                ? aggregated.scopeTypeKeys
                : (Array.isArray(data?.scope_type_keys) ? data.scope_type_keys : []),
            procurementType: requestContext.procurement_type || '',
            geographicScope: requestContext.geographic_scope || '',
            package: requestContext.package || '',
        };
    }

    let prImportPendingLines = [];
    let prImportOnConfirm = null;
    let prImportRevertSelect = null;

    function updatePrImportConfirmState() {
        if (!prImportConfirm || !prImportBody) {
            return;
        }
        const anyChecked = prImportBody.querySelector('input[type="checkbox"][data-pr-line]:checked');
        prImportConfirm.disabled = !anyChecked;
        if (prImportSelectAll) {
            const boxes = prImportBody.querySelectorAll('input[type="checkbox"][data-pr-line]');
            prImportSelectAll.checked = boxes.length > 0 && boxes.length === prImportBody.querySelectorAll('input[type="checkbox"][data-pr-line]:checked').length;
            prImportSelectAll.indeterminate = Boolean(anyChecked) && !prImportSelectAll.checked;
        }
    }

    function closePrImportModal() {
        if (!prImportModal) {
            return;
        }
        prImportModal.classList.add('hidden');
        prImportPendingLines = [];
        prImportOnConfirm = null;
        if (prImportRevertSelect && procurementRequestSelect) {
            procurementRequestSelect.value = prImportRevertSelect;
            prImportRevertSelect = null;
        }
        updatePrImportButtonState();
    }

    function openPrImportModal(requestNumber, lines, onConfirm) {
        if (!prImportModal || !prImportBody) {
            return;
        }

        prImportPendingLines = Array.isArray(lines) ? lines : [];
        prImportOnConfirm = onConfirm;
        prImportSubtitle.textContent = requestNumber
            ? 'P.R. ' + requestNumber + ' — select the lines to add to this purchase order.'
            : 'Select the lines to add to this purchase order.';

        prImportBody.innerHTML = '';
        const hasLines = prImportPendingLines.length > 0;
        prImportEmpty.classList.toggle('hidden', hasLines);
        prImportTable.classList.toggle('hidden', !hasLines);

        prImportPendingLines.forEach(function (line, index) {
            const tr = document.createElement('tr');
            tr.className = 'align-top';

            const checkTd = document.createElement('td');
            checkTd.className = 'py-3 pr-2';
            const checkbox = document.createElement('input');
            checkbox.type = 'checkbox';
            checkbox.className = 'rounded border-slate-300';
            checkbox.setAttribute('data-pr-line', String(index));
            checkbox.addEventListener('change', updatePrImportConfirmState);
            checkTd.appendChild(checkbox);
            tr.appendChild(checkTd);

            const lineTd = document.createElement('td');
            lineTd.className = 'py-3 pr-3 font-mono text-xs text-slate-800';
            lineTd.textContent = line.item || '—';
            tr.appendChild(lineTd);

            const projectTd = document.createElement('td');
            projectTd.className = 'py-3 pr-3 text-slate-700';
            projectTd.textContent = line.project || '—';
            tr.appendChild(projectTd);

            const scopeTd = document.createElement('td');
            scopeTd.className = 'py-3 pr-3 text-slate-700';
            scopeTd.textContent = line.scope_type || '—';
            tr.appendChild(scopeTd);

            const categoryTd = document.createElement('td');
            categoryTd.className = 'py-3 pr-3 text-slate-700';
            categoryTd.textContent = line.category || '—';
            tr.appendChild(categoryTd);

            const descTd = document.createElement('td');
            descTd.className = 'py-3 text-slate-600';
            descTd.textContent = line.summary || line.description || '—';
            tr.appendChild(descTd);

            prImportBody.appendChild(tr);
        });

        if (prImportSelectAll) {
            prImportSelectAll.checked = false;
            prImportSelectAll.indeterminate = false;
        }
        updatePrImportConfirmState();
        prImportModal.classList.remove('hidden');
    }

    function prImportBtn() {
        return document.getElementById('po-import-pr-lines');
    }

    function updatePrImportButtonState() {
        const importBtn = prImportBtn();
        if (!importBtn || !procurementRequestSelect) {
            return;
        }
        importBtn.disabled = !procurementRequestSelect.value;
    }

    const paymentTermsBody = document.getElementById('po-payment-terms-body');
    const paymentTermTemplate = document.getElementById('po-payment-term-template');
    const showPaymentTermsInput = document.getElementById('show_payment_terms');
    const showRetentionInput = document.getElementById('show_retention');
    const showMaintenanceInput = document.getElementById('show_maintenance');
    const retentionBody = document.getElementById('po-retentions-body');
    const retentionTemplate = document.getElementById('po-retention-template');
    const afterSaleServiceYes = document.querySelector('input[name="after_sale_service_applicable"][value="1"]');
    const afterSaleServiceNo = document.querySelector('input[name="after_sale_service_applicable"][value="0"]');
    const warrantyYearsInput = document.getElementById('warranty_years');
    const warrantyCoverageInput = document.getElementById('warranty_coverage');

    function reindexRetentionRows() {
        if (!retentionBody) {
            return;
        }

        retentionBody.querySelectorAll('.po-retention-row').forEach(function (row, index) {
            row.querySelectorAll('[data-name]').forEach(function (input) {
                const field = input.getAttribute('data-name');
                input.setAttribute('name', 'retentions[' + index + '][' + field + ']');
            });
        });
    }

    function bindRetentionRow(row) {
        row.querySelector('.po-remove-retention')?.addEventListener('click', function () {
            const rows = retentionBody?.querySelectorAll('.po-retention-row') || [];
            if (rows.length <= 1) {
                row.querySelector('[data-name="retention_percent"]').value = '';
                row.querySelector('[data-name="release_period"]').value = '';
                return;
            }
            row.remove();
            reindexRetentionRows();
        });
    }

    function replaceRetentionRows(rows) {
        if (!retentionBody) {
            return;
        }

        retentionBody.innerHTML = '';
        const sourceRows = Array.isArray(rows) && rows.length > 0
            ? rows
            : [{ retention_percent: '', release_period: '' }];

        sourceRows.forEach(function (rowData, index) {
            const clone = retentionTemplate?.content?.cloneNode(true);
            const row = clone?.querySelector('tr');
            if (!row) {
                return;
            }

            row.querySelector('[data-name="retention_percent"]').value = rowData.retention_percent ?? '';
            row.querySelector('[data-name="release_period"]').value = rowData.release_period ?? '';
            retentionBody.appendChild(row);
            bindRetentionRow(row);
        });

        reindexRetentionRows();
    }

    let currentPoTotal = 0;

    function roundMoney(value) {
        return Math.round((parseFloat(value) || 0) * 100) / 100;
    }

    function reindexPaymentTermRows() {
        if (!paymentTermsBody) {
            return;
        }
        paymentTermsBody.querySelectorAll('.po-payment-term-row').forEach(function (row, index) {
            row.querySelectorAll('[data-name]').forEach(function (input) {
                const field = input.getAttribute('data-name');
                input.setAttribute('name', 'payment_term_rows[' + index + '][' + field + ']');
            });
        });
        updatePaymentTermSelectState();
    }

    function paymentTermNamedInput(row, field) {
        return row.querySelector('input[data-name="' + field + '"]:not([disabled])')
            || row.querySelector('input[data-name="' + field + '"]');
    }

    function updatePaymentTermTotals() {
        let pctTotal = 0;
        let amtTotal = 0;
        let hasPct = false;
        paymentTermsBody?.querySelectorAll('.po-payment-term-row').forEach(function (row) {
            const pctEl = row.querySelector('.po-payment-term-percentage');
            const amtEl = row.querySelector('.po-payment-term-amount');
            const pct = pctEl && pctEl.value !== '' ? parseFloat(pctEl.value) : null;
            const amt = amtEl && amtEl.value !== '' ? parseFloat(amtEl.value) : null;
            if (pct !== null && !isNaN(pct)) {
                pctTotal += pct;
                hasPct = true;
            }
            if (amt !== null && !isNaN(amt)) {
                amtTotal += amt;
            }
        });

        const pctEl = document.getElementById('po-payment-terms-pct-total');
        const amtEl = document.getElementById('po-payment-terms-amt-total');
        const badge = document.getElementById('po-payment-terms-pct-badge');
        if (pctEl) {
            pctEl.textContent = (Math.round(pctTotal * 100) / 100).toString();
        }
        if (amtEl) {
            amtEl.textContent = formatMoney(amtTotal);
        }
        if (badge) {
            if (! hasPct) {
                badge.classList.add('hidden');
            } else {
                badge.classList.remove('hidden');
                const ok = Math.abs(pctTotal - 100) < 0.05;
                badge.textContent = ok ? '100%' : '≠ 100%';
                badge.className = 'ml-2 rounded-full px-2 py-0.5 text-xs font-medium ' + (ok
                    ? 'bg-emerald-100 text-emerald-800'
                    : 'bg-amber-100 text-amber-800');
            }
        }
    }

    function syncPaymentTermRow(row, source) {
        if (row.getAttribute('data-invoiced') === '1') {
            return;
        }
        const pctInput = row.querySelector('.po-payment-term-percentage');
        const amtInput = row.querySelector('.po-payment-term-amount');
        if (!pctInput || !amtInput) {
            return;
        }
        const pctRaw = pctInput.value;
        const amtRaw = amtInput.value;
        const pct = pctRaw === '' ? null : parseFloat(pctRaw);
        const amt = amtRaw === '' ? null : parseFloat(amtRaw);

        if (source === 'percentage' && pct !== null && !isNaN(pct)) {
            amtInput.value = formatMoney(roundMoney((pct / 100) * currentPoTotal));
        } else if (source === 'amount' && amt !== null && !isNaN(amt)) {
            pctInput.value = currentPoTotal > 0
                ? (Math.round((amt / currentPoTotal) * 10000) / 100).toFixed(2)
                : '0';
        } else if (source === 'total') {
            if (pctRaw !== '' && !isNaN(pct)) {
                amtInput.value = formatMoney(roundMoney((pct / 100) * currentPoTotal));
            } else if (amtRaw !== '' && !isNaN(amt) && currentPoTotal > 0) {
                pctInput.value = (Math.round((amt / currentPoTotal) * 10000) / 100).toFixed(2);
            }
        }
    }

    function paymentTermRowHasContent(row) {
        const milestone = (row.querySelector('.po-payment-term-milestone')?.value || '').trim();
        const notes = (row.querySelector('.po-payment-term-notes')?.value || '').trim();
        const percentage = (row.querySelector('.po-payment-term-percentage')?.value || '').trim();
        const amount = (row.querySelector('.po-payment-term-amount')?.value || '').trim();

        return milestone !== '' || notes !== '' || percentage !== '' || amount !== '';
    }

    function validatePaymentTermNames() {
        if (!paymentTermsBody) {
            return true;
        }

        let valid = true;
        const seen = {};
        const hint = document.getElementById('po-payment-term-name-error');

        paymentTermsBody.querySelectorAll('.po-payment-term-row').forEach(function (row) {
            const input = row.querySelector('.po-payment-term-milestone');
            if (!input) {
                return;
            }
            input.classList.remove('border-red-500');
            if (!paymentTermRowHasContent(row)) {
                return;
            }
            const name = (input.value || '').trim();
            if (name === '') {
                input.classList.add('border-red-500');
                valid = false;
                return;
            }
            const key = name.toLowerCase();
            if (seen[key]) {
                input.classList.add('border-red-500');
                seen[key].classList.add('border-red-500');
                valid = false;
            } else {
                seen[key] = input;
            }
        });

        if (hint) {
            hint.classList.toggle('hidden', valid);
        }

        return valid;
    }

    function bindPaymentTermRow(row) {
        const milestone = row.querySelector('.po-payment-term-milestone');
        const notesInput = row.querySelector('.po-payment-term-notes');
        [milestone, notesInput].forEach(function (field) {
            if (!field) {
                return;
            }
            applyBilingualDirection(field);
            field.addEventListener('input', function () {
                applyBilingualDirection(field);
                validatePaymentTermNames();
            });
        });

        row.querySelector('.po-payment-term-percentage')?.addEventListener('input', function () {
            syncPaymentTermRow(row, 'percentage');
            updatePaymentTermTotals();
            validatePaymentTermNames();
        });
        row.querySelector('.po-payment-term-amount')?.addEventListener('input', function () {
            syncPaymentTermRow(row, 'amount');
            updatePaymentTermTotals();
            validatePaymentTermNames();
        });
        row.querySelector('.po-payment-term-select')?.addEventListener('change', updatePaymentTermSelectState);

        row.querySelector('.po-remove-payment-term')?.addEventListener('click', function () {
            if (row.getAttribute('data-invoiced') === '1') {
                return;
            }
            const rows = paymentTermsBody?.querySelectorAll('.po-payment-term-row') || [];
            if (rows.length <= 1) {
                row.querySelector('.po-payment-term-milestone').value = '';
                row.querySelector('.po-payment-term-percentage').value = '';
                row.querySelector('.po-payment-term-amount').value = '';
                const idInput = paymentTermNamedInput(row, 'id');
                if (idInput) {
                    idInput.value = '';
                }
                const notesInput = row.querySelector('.po-payment-term-notes');
                if (notesInput) {
                    notesInput.value = '';
                }
                updatePaymentTermTotals();
                validatePaymentTermNames();
                return;
            }
            row.remove();
            reindexPaymentTermRows();
            updatePaymentTermTotals();
            validatePaymentTermNames();
        });
    }

    function replacePaymentTermRows(rows) {
        if (!paymentTermsBody || !paymentTermTemplate) {
            return;
        }

        const invoicedRows = [];
        paymentTermsBody.querySelectorAll('.po-payment-term-row[data-invoiced="1"]').forEach(function (row) {
            invoicedRows.push(row);
        });

        paymentTermsBody.innerHTML = '';
        invoicedRows.forEach(function (row) {
            paymentTermsBody.appendChild(row);
        });
        const sourceRows = Array.isArray(rows) && rows.length > 0
            ? rows
            : [{ milestone: '', percentage: '', amount: '' }];

        sourceRows.forEach(function (rowData) {
            const clone = paymentTermTemplate.content.cloneNode(true);
            const row = clone.querySelector('tr');
            if (!row) {
                return;
            }
            const idInput = paymentTermNamedInput(row, 'id');
            if (idInput) {
                idInput.value = '';
            }
            row.querySelector('.po-payment-term-milestone').value = rowData.milestone ?? '';
            row.querySelector('.po-payment-term-percentage').value = rowData.percentage ?? '';
            row.querySelector('.po-payment-term-amount').value = rowData.amount ?? '';
            const notesInput = row.querySelector('.po-payment-term-notes');
            if (notesInput) {
                notesInput.value = rowData.notes ?? '';
            }
            paymentTermsBody.appendChild(row);
            bindPaymentTermRow(row);
            syncPaymentTermRow(row, 'percentage');
        });

        reindexPaymentTermRows();
        updatePaymentTermTotals();
    }

    function selectedPaymentTermIds() {
        const ids = [];
        paymentTermsBody?.querySelectorAll('.po-payment-term-row').forEach(function (row) {
            if (row.getAttribute('data-invoiced') === '1') {
                return;
            }
            const checkbox = row.querySelector('.po-payment-term-select');
            if (!checkbox || !checkbox.checked) {
                return;
            }
            const id = paymentTermNamedInput(row, 'id')?.value;
            if (id) {
                ids.push(id);
            }
        });
        return ids;
    }

    function updatePaymentTermSelectState() {
        const btn = document.getElementById('po-create-selected-invoices');
        if (!btn) {
            return;
        }
        btn.disabled = selectedPaymentTermIds().length < 2;
    }

    window.poRecalcPaymentTermsFromTotal = function (total) {
        currentPoTotal = parseFloat(total) || 0;
        paymentTermsBody?.querySelectorAll('.po-payment-term-row').forEach(function (row) {
            syncPaymentTermRow(row, 'total');
        });
        updatePaymentTermTotals();
    };

    function setYesNoApplicable(yesInput, noInput, value) {
        if (value === true || value === 1 || value === '1') {
            if (yesInput) {
                yesInput.checked = true;
            }
            return;
        }
        if (value === false || value === 0 || value === '0') {
            if (noInput) {
                noInput.checked = true;
            }
        }
    }

    function applyCurrencyFromPr(currencyCode) {
        if (!currencyInput) {
            return;
        }

        const code = (currencyCode || '').trim().toUpperCase();
        if (!code) {
            return;
        }

        currencyInput.value = code;
        updateCurrencyLabels();
    }

    function applyCommercialTermsFromPr(commercialTerms) {
        if (!commercialTerms || typeof commercialTerms !== 'object') {
            return;
        }

        if (Array.isArray(commercialTerms.payment_term_rows)) {
            replacePaymentTermRows(commercialTerms.payment_term_rows);
        }

        if (showPaymentTermsInput) {
            showPaymentTermsInput.checked = Boolean(commercialTerms.has_payment_terms);
        }

        replaceRetentionRows(commercialTerms.retentions || []);

        if (showRetentionInput) {
            showRetentionInput.checked = Boolean(commercialTerms.has_retention);
        }

        setYesNoApplicable(
            afterSaleServiceYes,
            afterSaleServiceNo,
            commercialTerms.after_sale_service_applicable
        );
        if (warrantyYearsInput) {
            warrantyYearsInput.value = commercialTerms.warranty_years ?? '';
        }
        if (warrantyCoverageInput) {
            warrantyCoverageInput.value = commercialTerms.warranty_coverage || '';
        }

        if (showMaintenanceInput) {
            showMaintenanceInput.checked = Boolean(commercialTerms.has_maintenance);
        }
    }

    document.getElementById('po-add-retention')?.addEventListener('click', function () {
        if (!retentionBody || !retentionTemplate) {
            return;
        }
        const clone = retentionTemplate.content.cloneNode(true);
        const row = clone.querySelector('tr');
        retentionBody.appendChild(row);
        bindRetentionRow(row);
        reindexRetentionRows();
    });

    retentionBody?.querySelectorAll('.po-retention-row').forEach(bindRetentionRow);

    document.getElementById('po-add-payment-term')?.addEventListener('click', function () {
        if (!paymentTermsBody || !paymentTermTemplate) {
            return;
        }
        const clone = paymentTermTemplate.content.cloneNode(true);
        const row = clone.querySelector('tr');
        paymentTermsBody.appendChild(row);
        bindPaymentTermRow(row);
        reindexPaymentTermRows();
        updatePaymentTermTotals();
    });

    paymentTermsBody?.querySelectorAll('.po-payment-term-row').forEach(bindPaymentTermRow);
    updatePaymentTermTotals();

    paymentTermsBody?.closest('form')?.addEventListener('submit', function (event) {
        if (!validatePaymentTermNames()) {
            event.preventDefault();
            document.getElementById('po-payment-terms-section')?.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
    });

    document.getElementById('po-create-selected-invoices')?.addEventListener('click', function () {
        const ids = selectedPaymentTermIds();
        const section = document.getElementById('po-payment-terms-section');
        const poId = section?.getAttribute('data-po-id');
        const baseUrl = section?.getAttribute('data-invoice-create-url');
        if (!baseUrl || !poId || ids.length < 2) {
            return;
        }
        const params = new URLSearchParams();
        params.set('po_id', poId);
        params.set('source', 'po_payment_term');
        ids.forEach(function (id) {
            params.append('milestone_ids[]', id);
        });
        window.location.href = baseUrl + '?' + params.toString();
    });

    async function fetchProcurementRequestLines(requestId) {
        const urlTemplate = procurementRequestSelect?.getAttribute('data-lines-url-template');
        if (!urlTemplate || !requestId) {
            return null;
        }

        const response = await fetch(urlTemplate.replace('__ID__', requestId), {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        });
        if (!response.ok) {
            return null;
        }

        return response.json();
    }

    async function openPrImportForRequest(requestId, options) {
        const opts = options || {};
        if (!requestId) {
            return;
        }

        try {
            const data = await fetchProcurementRequestLines(requestId);
            if (!data) {
                return;
            }

            openPrImportModal(data.request_number || '', data.items || [], function (selectedRows, mode) {
                updatePoPrContextPanel(
                    data.request_number || '',
                    contextFromProcurementRequestResponse(data, selectedRows)
                );
                applyPoCompanyPreview(data.company || null);
                applyCommercialTermsFromPr(data.commercial_terms || null);
                applyCurrencyFromPr(data.currency_code || '');
                if (opts.onImported) {
                    opts.onImported();
                }
                if (mode === 'append') {
                    appendRowsFromProcurementRequest(selectedRows);
                } else {
                    replaceRowsFromProcurementRequest(selectedRows);
                }
            });
        } catch (e) {
            console.error(e);
        }
    }

    prImportModal?.querySelectorAll('[data-po-pr-import-dismiss]').forEach(function (el) {
        el.addEventListener('click', closePrImportModal);
    });

    prImportSelectAll?.addEventListener('change', function () {
        prImportBody?.querySelectorAll('input[type="checkbox"][data-pr-line]').forEach(function (cb) {
            cb.checked = prImportSelectAll.checked;
        });
        updatePrImportConfirmState();
    });

    prImportConfirm?.addEventListener('click', function () {
        const selected = [];
        prImportBody?.querySelectorAll('input[type="checkbox"][data-pr-line]:checked').forEach(function (cb) {
            const index = parseInt(cb.getAttribute('data-pr-line'), 10);
            if (!Number.isNaN(index) && prImportPendingLines[index]) {
                selected.push(prImportPendingLines[index]);
            }
        });

        if (selected.length === 0 || !prImportOnConfirm) {
            return;
        }

        let mode = 'replace';
        if (linesBodyHasContent()) {
            const replaceExisting = window.confirm(
                'Replace all current line items with the selected P.R. lines?\n\nOK = replace table\nCancel = add selected lines (duplicate line codes are skipped)'
            );
            mode = replaceExisting ? 'replace' : 'append';
        }

        prImportOnConfirm(selected, mode);
        prImportRevertSelect = null;
        prImportPendingLines = [];
        prImportOnConfirm = null;
        if (prImportModal) {
            prImportModal.classList.add('hidden');
        }
        updatePrImportButtonState();
    });

    linesBody.querySelectorAll('.po-line-row').forEach(bindRow);
    document.querySelectorAll('.po-adjustment').forEach(function (input) {
        input.addEventListener('input', recalcAll);
    });
    recalcAll();
    applyUserDefaultIfEmpty();
    updateCurrencyLabels();

    addBtn?.addEventListener('click', addRow);

    currencyInput?.addEventListener('input', function () {
        currencyInput.value = currencyInput.value.toUpperCase().replace(/[^A-Z]/g, '').slice(0, 3);
        updateCurrencyLabels();
    });

    currencyInput?.addEventListener('blur', applyUserDefaultIfEmpty);
    currencyInput?.closest('form')?.addEventListener('submit', applyUserDefaultIfEmpty);

    if (procurementRequestSelect) {
        let previousProcurementRequestId = procurementRequestSelect.value;

        procurementRequestSelect.addEventListener('change', function () {
            const requestId = procurementRequestSelect.value;
            updatePrImportButtonState();

            if (!requestId) {
                previousProcurementRequestId = '';
                clearPoPrContextPanel();
                return;
            }

            if (requestId === previousProcurementRequestId) {
                return;
            }

            prImportRevertSelect = previousProcurementRequestId;
            openPrImportForRequest(requestId, {
                onImported: function () {
                    previousProcurementRequestId = requestId;
                    prImportRevertSelect = null;
                },
            });
        });

        prImportBtn()?.addEventListener('click', function () {
            const requestId = procurementRequestSelect.value;
            if (!requestId) {
                return;
            }
            openPrImportForRequest(requestId, {});
        });

        updatePrImportButtonState();
    }

    const generalTermsList = document.getElementById('po-general-terms-list');
    const customTermsList = document.getElementById('po-custom-terms-list');
    const customTermTemplate = document.getElementById('po-custom-term-template');
    const addCustomTermBtn = document.getElementById('po-add-custom-term');
    const scopeTermsMap = JSON.parse(
        document.getElementById('po-scope-terms-map')?.textContent || '{}'
    );
    const termsLocaleInputs = document.querySelectorAll('.po-terms-locale');
    const handoverInput = document.getElementById('handover_at');
    const dismantlingInput = document.getElementById('dismantling_at');

    function currentTermsLocale() {
        const checked = document.querySelector('.po-terms-locale:checked');
        return checked ? checked.value : 'en';
    }

    function scopeTextsForLocale(scopeKey, locale) {
        const entry = scopeTermsMap[scopeKey];
        if (!entry) {
            return [];
        }
        if (Array.isArray(entry)) {
            return entry;
        }
        return entry[locale] || entry.en || entry.ar || [];
    }

    function applyCustomTermDirection(locale) {
        document.querySelectorAll('.custom-term-key, .custom-term-value').forEach(function (input) {
            if (locale === 'ar') {
                input.setAttribute('dir', 'rtl');
            } else {
                input.removeAttribute('dir');
            }
        });
    }

    if (generalTermsList) {
        function collectScopeTypesFromOrderTerms() {
            const found = {};
            poPrScopeTypeKeys.forEach(function (scopeType) {
                found[scopeType] = true;
            });
            if (handoverInput?.value) {
                found.Maintenance = true;
            }
            if (dismantlingInput?.value) {
                found.Dismantling = true;
            }
            return Object.keys(found);
        }

        function mergeGeneralTerms(scopeTypes) {
            const merged = [];
            const seen = {};

            function addTexts(texts) {
                (texts || []).forEach(function (text) {
                    if (!text || seen[text]) {
                        return;
                    }
                    seen[text] = true;
                    merged.push(text);
                });
            }

            const locale = currentTermsLocale();
            addTexts(scopeTextsForLocale('global', locale));
            scopeTypes.forEach(function (scopeType) {
                addTexts(scopeTextsForLocale(scopeType, locale));
            });

            return merged;
        }

        function renderGeneralTerms(terms) {
            generalTermsList.innerHTML = '';

            if (terms.length === 0) {
                const empty = document.createElement('li');
                empty.id = 'po-general-terms-empty';
                empty.className = 'text-slate-500';
                empty.textContent = 'Company-wide terms load automatically. Set handover or dismantling dates to include related scope terms.';
                generalTermsList.appendChild(empty);
                return;
            }

            const locale = currentTermsLocale();
            terms.forEach(function (text) {
                const row = document.createElement('li');
                row.className = 'po-general-term-row flex gap-2';
                row.innerHTML = '<span class="shrink-0">-</span><span class="min-w-0 flex-1"></span>';
                const textEl = row.querySelector('span:last-child');
                textEl.textContent = text;
                if (locale === 'ar') {
                    textEl.setAttribute('dir', 'rtl');
                }
                generalTermsList.appendChild(row);
            });
        }

        window.poSyncGeneralTerms = function () {
            renderGeneralTerms(mergeGeneralTerms(collectScopeTypesFromOrderTerms()));
            applyCustomTermDirection(currentTermsLocale());
        };

        termsLocaleInputs.forEach(function (input) {
            input.addEventListener('change', function () {
                window.poSyncGeneralTerms();
            });
        });

        document.querySelectorAll('.po-order-term-date').forEach(function (input) {
            input.addEventListener('change', window.poSyncGeneralTerms);
        });

        window.poSyncGeneralTerms();
    }

    if (customTermsList && customTermTemplate) {
        function reindexCustomTerms() {
            customTermsList.querySelectorAll('.po-custom-term-row').forEach(function (row, index) {
                row.querySelectorAll('[data-name]').forEach(function (input) {
                    const field = input.getAttribute('data-name');
                    input.setAttribute('name', 'terms_custom[' + index + '][' + field + ']');
                });
                row.querySelectorAll('[name^="terms_custom["]').forEach(function (input) {
                    const match = input.getAttribute('name').match(/terms_custom\[\d+]\[(\w+)]/);
                    if (match) {
                        input.setAttribute('name', 'terms_custom[' + index + '][' + match[1] + ']');
                    }
                });
            });
        }

        function bindCustomTermRow(row) {
            row.querySelector('.po-remove-custom-term')?.addEventListener('click', function () {
                row.remove();
                reindexCustomTerms();
            });
        }

        customTermsList.querySelectorAll('.po-custom-term-row').forEach(bindCustomTermRow);

        addCustomTermBtn?.addEventListener('click', function () {
            const clone = customTermTemplate.content.cloneNode(true);
            const row = clone.querySelector('li');
            customTermsList.appendChild(row);
            reindexCustomTerms();
            bindCustomTermRow(row);
            applyCustomTermDirection(currentTermsLocale());
        });
    }
});
})();
</script>
