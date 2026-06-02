<script>
document.addEventListener('DOMContentLoaded', function () {
    const linesBody = document.getElementById('po-lines-body');
    const template = document.getElementById('po-line-template');
    const addBtn = document.getElementById('po-add-line');
    const subtotalEl = document.getElementById('po-lines-subtotal');
    const totalPriceEl = document.getElementById('po-total-price');
    const deliveryFeeInput = document.getElementById('delivery_fee');
    const discountInput = document.getElementById('discount');
    const vendorIdInput = document.getElementById('vendor_id');
    const vendorSearchRoot = document.querySelector('.vendor-search-select');
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

    async function loadVendorSnapshot(vendorId) {
        if (!vendorId) {
            if (currencyInput && userDefaultCurrency) {
                currencyInput.value = userDefaultCurrency;
                updateCurrencyLabels();
            }
            return;
        }
        const base = vendorSearchRoot?.getAttribute('data-snapshot-url');
        if (!base) {
            return;
        }
        try {
            const response = await fetch(base + '/' + vendorId + '/purchase-order-snapshot', {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            });
            if (!response.ok) {
                return;
            }
            const data = await response.json();
            const fields = ['vendor_company_name', 'vendor_contact', 'vendor_email', 'vendor_phone', 'vendor_address', 'payment_terms', 'currency_code'];
            fields.forEach(function (key) {
                const el = document.getElementById(key);
                if (!el) {
                    return;
                }
                if (key === 'currency_code') {
                    el.value = currencyFromVendor(data.currency_code);
                } else if (data[key]) {
                    el.value = data[key];
                    if (el.classList.contains('po-bilingual-text')) {
                        applyBilingualDirection(el);
                    }
                }
            });
            updateCurrencyLabels();
        } catch (e) {
            console.error(e);
        }
    }

    if (vendorIdInput && vendorSearchRoot) {
        const searchInput = document.getElementById('vendor_search_input');
        const resultsList = document.getElementById('vendor_search_results');
        const clearBtn = document.getElementById('vendor_search_clear');
        const maxVisible = 150;
        let vendorOptions = [];

        try {
            vendorOptions = JSON.parse(document.getElementById('vendor-select-options')?.textContent || '[]');
        } catch (e) {
            console.error(e);
        }

        let lastSelectedLabel = searchInput?.value || '';

        function closeVendorResults() {
            resultsList?.classList.add('hidden');
            searchInput?.setAttribute('aria-expanded', 'false');
        }

        function openVendorResults() {
            resultsList?.classList.remove('hidden');
            searchInput?.setAttribute('aria-expanded', 'true');
        }

        function normalizeFilterText(text) {
            return (text || '').toLowerCase().trim();
        }

        function filterQueryForList() {
            const typed = searchInput?.value.trim() || '';
            if (vendorIdInput.value && typed === lastSelectedLabel) {
                return '';
            }
            return typed;
        }

        function filterVendors(query) {
            const normalized = normalizeFilterText(query);
            if (!normalized) {
                return vendorOptions;
            }
            return vendorOptions.filter(function (item) {
                return normalizeFilterText(item.label).includes(normalized);
            });
        }

        function renderVendorResults(items) {
            if (!resultsList) {
                return;
            }

            resultsList.innerHTML = '';
            const total = items.length;

            if (total === 0) {
                const empty = document.createElement('li');
                empty.className = 'px-3 py-2 text-sm text-slate-500';
                empty.textContent = vendorOptions.length === 0
                    ? 'No vendors in the system.'
                    : 'No vendors match your search.';
                resultsList.appendChild(empty);
                return;
            }

            if (total > maxVisible) {
                const hint = document.createElement('li');
                hint.className = 'border-b border-slate-100 px-3 py-2 text-xs text-slate-500';
                hint.textContent = 'Showing ' + maxVisible + ' of ' + total + ' — type more to narrow the list.';
                resultsList.appendChild(hint);
            }

            items.slice(0, maxVisible).forEach(function (item) {
                const option = document.createElement('li');
                option.className = 'cursor-pointer px-3 py-2 text-sm text-slate-800 hover:bg-slate-50';
                option.textContent = item.label;
                option.setAttribute('role', 'option');
                option.addEventListener('mousedown', function (event) {
                    event.preventDefault();
                    selectVendor(item.id, item.label);
                });
                resultsList.appendChild(option);
            });
        }

        function refreshVendorDropdown() {
            renderVendorResults(filterVendors(filterQueryForList()));
            openVendorResults();
        }

        function selectVendor(id, label) {
            vendorIdInput.value = String(id);
            if (searchInput) {
                searchInput.value = label;
            }
            lastSelectedLabel = label;
            closeVendorResults();
            vendorIdInput.dispatchEvent(new Event('change', { bubbles: true }));
        }

        function clearVendorSelection() {
            vendorIdInput.value = '';
            if (searchInput) {
                searchInput.value = '';
            }
            lastSelectedLabel = '';
            closeVendorResults();
            vendorIdInput.dispatchEvent(new Event('change', { bubbles: true }));
        }

        vendorIdInput.addEventListener('change', function () {
            loadVendorSnapshot(vendorIdInput.value);
        });

        clearBtn?.addEventListener('click', clearVendorSelection);

        searchInput?.addEventListener('focus', function () {
            refreshVendorDropdown();
        });

        searchInput?.addEventListener('input', function () {
            const query = searchInput.value.trim();
            if (vendorIdInput.value && query !== lastSelectedLabel) {
                vendorIdInput.value = '';
            }
            refreshVendorDropdown();
        });

        searchInput?.addEventListener('keydown', function (event) {
            if (event.key === 'Escape') {
                closeVendorResults();
            }
        });

        document.addEventListener('click', function (event) {
            if (!vendorSearchRoot.contains(event.target)) {
                closeVendorResults();
                if (searchInput && vendorIdInput.value && searchInput.value.trim() === '') {
                    searchInput.value = lastSelectedLabel;
                }
            }
        });
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

    function addRowFromData(rowData) {
        const clone = template.content.cloneNode(true);
        const row = clone.querySelector('tr');
        if (!row) {
            return;
        }

        row.querySelector('[data-name="item"]').value = rowData.item || '';
        row.querySelector('[data-name="description"]').value = rowData.description || '';
        row.querySelector('[data-name="quantity"]').value = rowData.quantity ?? 1;
        row.querySelector('[data-name="unit_price"]').value = rowData.unit_price ?? 0;

        linesBody.appendChild(row);
        bindRow(row);
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
    const prImportBtn = document.getElementById('po-import-pr-lines');

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

    function updatePrImportButtonState() {
        if (!prImportBtn || !procurementRequestSelect) {
            return;
        }
        prImportBtn.disabled = !procurementRequestSelect.value;
    }

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

        prImportBtn?.addEventListener('click', function () {
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
            if (handoverInput?.value) {
                found.Maintenance = true;
            }
            if (dismantlingInput?.value) {
                found.Dismantling = true;
            }
            return ['Maintenance', 'Dismantling'].filter(function (scopeType) {
                return found[scopeType];
            });
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
</script>
