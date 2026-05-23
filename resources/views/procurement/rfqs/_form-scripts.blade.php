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
        function optionById(id) {
            return prOptions.find(function (opt) {
                return String(opt.id) === String(id);
            });
        }

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
                if (id === current) {
                    option.selected = true;
                }
                select.appendChild(option);
            });
        }

        function setDisplay(row, key, value) {
            const el = row.querySelector('[data-display="' + key + '"]');
            if (! el) {
                return;
            }
            if (key === 'flexible_delivery_date') {
                el.textContent = value === true || value === '1' || value === 1 ? 'Yes' : (value === false || value === '0' || value === 0 ? 'No' : '—');
                return;
            }
            if (key === 'quantity' && value !== '' && value != null) {
                el.textContent = Number(value).toLocaleString(undefined, { minimumFractionDigits: 0, maximumFractionDigits: 3 });
                return;
            }
            el.textContent = (value !== null && value !== undefined && String(value).trim() !== '') ? String(value) : '—';
        }

        function setHidden(row, key, value) {
            const input = row.querySelector('[data-name="' + key + '"]');
            if (input) {
                input.value = value ?? '';
            }
        }

        function showPrDetails(details) {
            if (! details) {
                return;
            }
            details.classList.remove('hidden');
            details.removeAttribute('hidden');
        }

        function hidePrDetails(details) {
            if (! details) {
                return;
            }
            details.classList.add('hidden');
        }

        function applyPrItem(row) {
            const select = row.querySelector('.rfq-pr-item-select');
            const details = row.querySelector('.rfq-pr-details');
            const opt = optionById(select?.value);

            if (! opt) {
                hidePrDetails(details);
                ['item', 'description', 'quantity', 'unit', 'request_lead_time'].forEach(function (key) {
                    setHidden(row, key, key === 'quantity' ? '1' : '');
                });
                ['pr_number', 'line_item', 'project', 'zone', 'category', 'subcategory', 'scope_type', 'description', 'unit', 'quantity', 'justification', 'required_delivery_date', 'flexible_delivery_date', 'delivery_location'].forEach(function (key) {
                    setDisplay(row, key, '');
                });
                return;
            }

            showPrDetails(details);
            setDisplay(row, 'pr_number', opt.pr_number);
            setDisplay(row, 'line_item', opt.item);
            setDisplay(row, 'project', opt.project);
            setDisplay(row, 'zone', opt.zone);
            setDisplay(row, 'category', opt.category);
            setDisplay(row, 'subcategory', opt.subcategory);
            setDisplay(row, 'scope_type', opt.scope_type);
            setDisplay(row, 'description', opt.description);
            setDisplay(row, 'unit', opt.unit);
            setDisplay(row, 'quantity', opt.quantity);
            setDisplay(row, 'justification', opt.justification);
            setDisplay(row, 'required_delivery_date', opt.required_delivery_date);
            setDisplay(row, 'flexible_delivery_date', opt.flexible_delivery_date);
            setDisplay(row, 'delivery_location', opt.delivery_location);

            setHidden(row, 'item', opt.item);
            setHidden(row, 'description', opt.description);
            setHidden(row, 'quantity', opt.quantity);
            setHidden(row, 'unit', opt.unit);
            setHidden(row, 'request_lead_time', opt.request_lead_time);
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
            const row = template.content.firstElementChild.cloneNode(true);
            linesBody.appendChild(row);
            bindRow(row);
            reindexRows();
            row.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
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
