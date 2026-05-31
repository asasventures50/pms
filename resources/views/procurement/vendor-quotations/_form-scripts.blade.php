<script>
document.addEventListener('DOMContentLoaded', function () {
    const vendorSelect = document.getElementById('vendor_id');
    const linesBody = document.getElementById('vq-lines-body');
    const grandTotalEl = document.getElementById('vq-grand-total');

    function recalcRow(row) {
        const qty = parseFloat(row.dataset.quantity || '0') || 0;
        const unitInput = row.querySelector('.vq-unit-price');
        const totalInput = row.querySelector('.vq-total-price');
        const taxInput = row.querySelector('.vq-tax');

        const unit = parseFloat(unitInput?.value || '0') || 0;
        let lineTotal = parseFloat(totalInput?.value || '');

        if ((totalInput?.value === '' || Number.isNaN(lineTotal)) && unit > 0 && qty > 0) {
            lineTotal = Math.round(qty * unit * 100) / 100;
            if (totalInput) {
                totalInput.value = lineTotal.toFixed(2);
            }
        } else if (Number.isNaN(lineTotal)) {
            lineTotal = 0;
        }

        const tax = parseFloat(taxInput?.value || '0') || 0;

        return lineTotal + tax;
    }

    function recalcGrandTotal() {
        if (!linesBody || !grandTotalEl) {
            return;
        }

        let sum = 0;
        linesBody.querySelectorAll('.vq-line-row').forEach(function (row) {
            sum += recalcRow(row);
        });

        grandTotalEl.textContent = sum.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    if (linesBody) {
        linesBody.addEventListener('input', function (event) {
            if (event.target.matches('.vq-unit-price, .vq-total-price, .vq-tax')) {
                recalcGrandTotal();
            }
        });
        recalcGrandTotal();
    }

    if (vendorSelect) {
        vendorSelect.addEventListener('change', function () {
            const id = vendorSelect.value;
            if (!id) {
                return;
            }

            const base = vendorSelect.dataset.snapshotUrl || '/vendors';
            fetch(base + '/' + encodeURIComponent(id) + '/rfq-snapshot', {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            })
                .then(function (r) { return r.ok ? r.json() : null; })
                .then(function (data) {
                    if (!data) {
                        return;
                    }
                    const map = {
                        vendor_company_name: data.vendor_company_name,
                        vendor_contact: data.vendor_contact,
                        vendor_email: data.vendor_email,
                        vendor_phone: data.vendor_phone,
                        vendor_address: data.vendor_address,
                    };
                    Object.keys(map).forEach(function (key) {
                        const el = document.getElementById(key);
                        if (el && map[key]) {
                            el.value = map[key];
                        }
                    });
                })
                .catch(function () {});
        });
    }
});
</script>
