<script>
document.addEventListener('DOMContentLoaded', function () {
    const linesBody = document.getElementById('rfq-lines-body');
    const template = document.getElementById('rfq-line-template');
    const addBtn = document.getElementById('rfq-add-line');
    const grandTotalEl = document.getElementById('rfq-grand-total');
    const vendorSelect = document.getElementById('vendor_id');

    if (!linesBody || !template) {
        return;
    }

    function formatMoney(value) {
        return (Math.round(value * 100) / 100).toFixed(2);
    }

    function reindexRows() {
        linesBody.querySelectorAll('.rfq-line-row').forEach(function (row, index) {
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
        const qty = parseFloat(row.querySelector('.rfq-qty')?.value || '0');
        const unit = parseFloat(row.querySelector('.rfq-unit')?.value || '0');
        const total = qty * unit;
        const totalEl = row.querySelector('.rfq-line-total');
        if (totalEl) {
            totalEl.textContent = formatMoney(total);
        }
        return total;
    }

    function recalcAll() {
        let grand = 0;
        linesBody.querySelectorAll('.rfq-line-row').forEach(function (row) {
            grand += recalcRow(row);
        });
        if (grandTotalEl) {
            grandTotalEl.textContent = formatMoney(grand);
        }
    }

    function bindRow(row) {
        row.querySelectorAll('.rfq-qty, .rfq-unit').forEach(function (input) {
            input.addEventListener('input', recalcAll);
        });
        row.querySelector('.rfq-remove-line')?.addEventListener('click', function () {
            if (linesBody.querySelectorAll('.rfq-line-row').length <= 1) {
                return;
            }
            row.remove();
            reindexRows();
            recalcAll();
        });
    }

    function addRow() {
        const row = template.content.cloneNode(true).querySelector('tr');
        linesBody.appendChild(row);
        reindexRows();
        bindRow(row);
        recalcAll();
    }

    linesBody.querySelectorAll('.rfq-line-row').forEach(bindRow);
    recalcAll();
    addBtn?.addEventListener('click', addRow);

    if (vendorSelect) {
        vendorSelect.addEventListener('change', async function () {
            const vendorId = vendorSelect.value;
            if (!vendorId) {
                return;
            }
            const base = vendorSelect.getAttribute('data-snapshot-url');
            try {
                const response = await fetch(base + '/' + vendorId + '/rfq-snapshot', {
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                });
                if (!response.ok) {
                    return;
                }
                const data = await response.json();
                ['vendor_company_name', 'vendor_contact', 'vendor_email', 'vendor_phone', 'vendor_address'].forEach(function (key) {
                    const el = document.getElementById(key);
                    if (el && data[key]) {
                        el.value = data[key];
                    }
                });
            } catch (e) {
                console.error(e);
            }
        });
    }
});
</script>
