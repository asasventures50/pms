<script>
document.addEventListener('DOMContentLoaded', function () {
    const linesBody = document.getElementById('po-lines-body');
    const template = document.getElementById('po-line-template');
    const addBtn = document.getElementById('po-add-line');
    const subtotalEl = document.getElementById('po-lines-subtotal');
    const totalPriceEl = document.getElementById('po-total-price');
    const deliveryFeeInput = document.getElementById('delivery_fee');
    const discountInput = document.getElementById('discount');
    const vendorSelect = document.getElementById('vendor_id');
    const currencyInput = document.getElementById('currency_code');
    const userDefaultCurrency = (currencyInput?.dataset.userDefaultCurrency || '').trim().toUpperCase();

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

    if (vendorSelect) {
        vendorSelect.addEventListener('change', async function () {
            const vendorId = vendorSelect.value;
            if (!vendorId) {
                if (currencyInput && userDefaultCurrency) {
                    currencyInput.value = userDefaultCurrency;
                    updateCurrencyLabels();
                }
                return;
            }
            const base = vendorSelect.getAttribute('data-snapshot-url');
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
                    }
                });
                updateCurrencyLabels();
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
        document.querySelectorAll('.po-custom-term-input').forEach(function (input) {
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
                const input = row.querySelector('input[type="text"]');
                if (input) {
                    input.setAttribute('name', 'terms_custom[' + index + ']');
                }
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
