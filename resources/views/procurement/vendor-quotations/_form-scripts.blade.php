@include('procurement.partials._vendor-search-scripts')

<script>
document.addEventListener('DOMContentLoaded', function () {
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

    async function loadVendorSnapshot(vendorId) {
        if (!vendorId) {
            if (typeof window.applyVendorSnapshotFields === 'function') {
                window.applyVendorSnapshotFields({});
            }
            return;
        }

        const data = typeof window.fetchVendorSnapshot === 'function'
            ? await window.fetchVendorSnapshot(vendorId, 'rfq-snapshot')
            : null;

        if (!data || typeof window.applyVendorSnapshotFields !== 'function') {
            return;
        }

        window.applyVendorSnapshotFields(data);
    }

    if (typeof window.initVendorSearchSelect === 'function') {
        window.initVendorSearchSelect({ onChange: loadVendorSnapshot });
    }
});
</script>
