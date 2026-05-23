<script>
document.addEventListener('DOMContentLoaded', function () {
    const linesBody = document.getElementById('rfq-lines-body');
    const template = document.getElementById('rfq-line-template');
    const addBtn = document.getElementById('rfq-add-line');
    const vendorSelect = document.getElementById('vendor_id');
    const prOptions = JSON.parse(
        document.getElementById('rfq-pr-item-options')?.textContent || '[]'
    );

    if (linesBody && template) {
        function selectedPrItemIds(exceptRow) {
            const ids = [];
            linesBody.querySelectorAll('.rfq-line-row').forEach(function (row) {
                if (row === exceptRow) {
                    return;
                }
                const value = row.querySelector('.rfq-pr-item-select')?.value;
                if (value) {
                    ids.push(String(value));
                }
            });
            return ids;
        }

        function fillSelectOptions(select, exceptRow, selectedValue) {
            const taken = selectedPrItemIds(exceptRow);
            const current = String(selectedValue ?? select.value ?? '');

            select.innerHTML = '';
            const blank = document.createElement('option');
            blank.value = '';
            blank.textContent = '— Select PR item —';
            select.appendChild(blank);

            prOptions.forEach(function (opt) {
                const id = String(opt.id);
                if (id !== current && taken.includes(id)) {
                    return;
                }
                const option = document.createElement('option');
                option.value = id;
                option.textContent = opt.label;
                option.dataset.item = opt.item ?? '';
                option.dataset.description = opt.description ?? '';
                option.dataset.quantity = opt.quantity ?? '';
                option.dataset.unit = opt.unit ?? '';
                if (id === current) {
                    option.selected = true;
                }
                select.appendChild(option);
            });
        }

        function applyPrItem(row) {
            const select = row.querySelector('.rfq-pr-item-select');
            const option = select?.selectedOptions?.[0];
            const itemInput = row.querySelector('[data-name="item"]');
            const descriptionInput = row.querySelector('[data-name="description"]');
            const quantityInput = row.querySelector('[data-name="quantity"]');
            const unitInput = row.querySelector('[data-name="unit"]');

            if (! option || ! select?.value) {
                if (itemInput) itemInput.value = '';
                if (descriptionInput) descriptionInput.value = '';
                if (quantityInput) quantityInput.value = '1';
                if (unitInput) unitInput.value = '';
                return;
            }

            if (itemInput) itemInput.value = option.dataset.item || '';
            if (descriptionInput) descriptionInput.value = option.dataset.description || '';
            if (quantityInput) quantityInput.value = option.dataset.quantity || '1';
            if (unitInput) unitInput.value = option.dataset.unit || '';
        }

        function reindexRows() {
            linesBody.querySelectorAll('.rfq-line-row').forEach(function (row, index) {
                const label = row.querySelector('.rfq-line-label');
                if (label) {
                    label.textContent = 'Line ' + (index + 1);
                }

                row.querySelectorAll('[data-name]').forEach(function (input) {
                    const field = input.getAttribute('data-name');
                    input.setAttribute('name', 'items[' + index + '][' + field + ']');
                });

                const select = row.querySelector('.rfq-pr-item-select');
                if (select) {
                    fillSelectOptions(select, row, select.value);
                }
            });

            linesBody.querySelectorAll('.rfq-remove-line').forEach(function (btn) {
                const rows = linesBody.querySelectorAll('.rfq-line-row');
                btn.style.display = rows.length > 1 ? '' : 'none';
            });
        }

        function bindRow(row) {
            const select = row.querySelector('.rfq-pr-item-select');
            select?.addEventListener('change', function () {
                applyPrItem(row);
                reindexRows();
            });

            row.querySelector('.rfq-remove-line')?.addEventListener('click', function () {
                if (linesBody.querySelectorAll('.rfq-line-row').length <= 1) {
                    return;
                }
                row.remove();
                reindexRows();
            });
        }

        function addRow() {
            const row = template.content.cloneNode(true).querySelector('.rfq-line-row');
            linesBody.appendChild(row);
            bindRow(row);
            reindexRows();
        }

        linesBody.querySelectorAll('.rfq-line-row').forEach(function (row) {
            bindRow(row);
            applyPrItem(row);
        });
        reindexRows();

        addBtn?.addEventListener('click', addRow);
    }

    if (! vendorSelect) {
        return;
    }

    vendorSelect.addEventListener('change', async function () {
        const vendorId = vendorSelect.value;
        if (! vendorId) {
            return;
        }
        const base = vendorSelect.getAttribute('data-snapshot-url');
        try {
            const response = await fetch(base + '/' + vendorId + '/rfq-snapshot', {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            });
            if (! response.ok) {
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
});
</script>
