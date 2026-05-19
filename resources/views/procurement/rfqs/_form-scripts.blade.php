<script>
document.addEventListener('DOMContentLoaded', function () {
    const linesBody = document.getElementById('rfq-lines-body');
    const template = document.getElementById('rfq-line-template');
    const quotationBody = document.getElementById('rfq-quotation-body');
    const quotationTemplate = document.getElementById('rfq-quotation-template');
    const addBtn = document.getElementById('rfq-add-line');
    const vendorSelect = document.getElementById('vendor_id');

    if (!linesBody || !template) {
        return;
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

        if (quotationBody) {
            quotationBody.querySelectorAll('.rfq-quotation-row').forEach(function (row, index) {
                const indexCell = row.querySelector('.rfq-quotation-index');
                if (indexCell) {
                    indexCell.textContent = String(index + 1);
                }
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
    }

    function bindRow(row) {
        row.querySelector('.rfq-remove-line')?.addEventListener('click', function () {
            if (linesBody.querySelectorAll('.rfq-line-row').length <= 1) {
                return;
            }
            const rows = Array.from(linesBody.querySelectorAll('.rfq-line-row'));
            const index = rows.indexOf(row);
            row.remove();
            if (quotationBody && index >= 0) {
                const quoteRows = quotationBody.querySelectorAll('.rfq-quotation-row');
                if (quoteRows[index]) {
                    quoteRows[index].remove();
                }
            }
            reindexRows();
        });
    }

    function addRow() {
        const row = template.content.cloneNode(true).querySelector('tr');
        linesBody.appendChild(row);
        if (quotationBody && quotationTemplate) {
            quotationBody.appendChild(quotationTemplate.content.cloneNode(true).querySelector('tr'));
        }
        reindexRows();
        bindRow(row);
    }

    linesBody.querySelectorAll('.rfq-line-row').forEach(bindRow);
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
