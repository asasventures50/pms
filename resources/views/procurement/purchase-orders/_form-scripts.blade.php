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
        const initialProcurementRequestId = procurementRequestSelect.value;
        procurementRequestSelect.addEventListener('change', async function () {
            const requestId = procurementRequestSelect.value;
            if (!requestId) {
                return;
            }

            if (initialProcurementRequestId && requestId === initialProcurementRequestId) {
                return;
            }

            const urlTemplate = procurementRequestSelect.getAttribute('data-lines-url-template');
            if (!urlTemplate) {
                return;
            }

            const confirmed = window.confirm('Load line items from this P.R.? Current table rows will be replaced, and you can still edit them afterward.');
            if (!confirmed) {
                return;
            }

            try {
                const response = await fetch(urlTemplate.replace('__ID__', requestId), {
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                });
                if (!response.ok) {
                    return;
                }

                const data = await response.json();
                replaceRowsFromProcurementRequest(data.items || []);
            } catch (e) {
                console.error(e);
            }
        });
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
