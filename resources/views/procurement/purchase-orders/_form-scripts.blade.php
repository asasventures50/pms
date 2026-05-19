<script>
document.addEventListener('DOMContentLoaded', function () {
    const linesBody = document.getElementById('po-lines-body');
    const template = document.getElementById('po-line-template');
    const addBtn = document.getElementById('po-add-line');
    const grandTotalEl = document.getElementById('po-grand-total');
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
        let grand = 0;
        linesBody.querySelectorAll('.po-line-row').forEach(function (row) {
            grand += recalcRow(row);
        });
        if (grandTotalEl) {
            grandTotalEl.textContent = formatMoney(grand);
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
});
</script>
