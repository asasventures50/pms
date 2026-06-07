@include('procurement.partials._vendor-search-scripts')

<script>
document.addEventListener('DOMContentLoaded', function () {
    const vendorSearchRoot = document.querySelector('.vendor-search-select');
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

    function loadVendorSnapshot(vendorId) {
        if (!vendorId) {
            return;
        }

        const base = vendorSearchRoot?.getAttribute('data-snapshot-url') || '/vendors';
        fetch(base + '/' + encodeURIComponent(vendorId) + '/rfq-snapshot', {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        })
            .then(function (r) { return r.ok ? r.json() : null; })
            .then(function (data) {
                if (!data) {
                    return;
                }
                ['vendor_company_name', 'vendor_contact', 'vendor_email', 'vendor_phone', 'vendor_address'].forEach(function (key) {
                    const el = document.getElementById(key);
                    if (el && data[key]) {
                        el.value = data[key];
                    }
                });
            })
            .catch(function () {});
    }

    if (typeof window.initVendorSearchSelect === 'function') {
        window.initVendorSearchSelect({ onChange: loadVendorSnapshot });
    }
});
</script>
