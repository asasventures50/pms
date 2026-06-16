@include('procurement.partials._vendor-search-scripts')

<script>
document.addEventListener('DOMContentLoaded', function () {
    const linesBody = document.getElementById('vq-lines-body');
    const subtotalEl = document.getElementById('vq-subtotal');
    const taxTotalEl = document.getElementById('vq-tax-total');
    const grandTotalEl = document.getElementById('vq-grand-total');

    function parseNumber(value) {
        const parsed = parseFloat(value);
        return Number.isNaN(parsed) ? 0 : parsed;
    }

    function formatMoney(value) {
        return value.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    function recalcRow(row) {
        const qty = parseNumber(row.querySelector('.vq-qty-quoted')?.value || row.dataset.quantity || '0');
        const unitInput = row.querySelector('.vq-unit-price');
        const totalInput = row.querySelector('.vq-total-price');
        const discountInput = row.querySelector('.vq-discount');
        const taxRateInput = row.querySelector('.vq-tax-rate');
        const taxInput = row.querySelector('.vq-tax');
        const lineDeliveryInput = row.querySelector('.vq-line-delivery');
        const lineInstallationInput = row.querySelector('.vq-line-installation');
        const lineTotalEl = row.querySelector('.vq-line-total');

        const unit = parseNumber(unitInput?.value);
        const discount = parseNumber(discountInput?.value);
        let lineSubtotal = parseNumber(totalInput?.value);

        if ((totalInput?.value === '' || Number.isNaN(lineSubtotal)) && unit > 0 && qty > 0) {
            lineSubtotal = Math.round((qty * unit - discount) * 100) / 100;
            if (totalInput) {
                totalInput.value = lineSubtotal.toFixed(2);
            }
        } else if (!Number.isNaN(lineSubtotal) && discount > 0 && unit > 0 && qty > 0 && totalInput?.value === '') {
            lineSubtotal = Math.max(0, lineSubtotal - discount);
        }

        const taxRate = parseNumber(taxRateInput?.value);
        let tax = parseNumber(taxInput?.value);

        if ((taxInput?.value === '' || tax === 0) && taxRate > 0 && lineSubtotal > 0) {
            tax = Math.round(lineSubtotal * (taxRate / 100) * 100) / 100;
            if (taxInput) {
                taxInput.value = tax.toFixed(2);
            }
        }

        const lineDelivery = parseNumber(lineDeliveryInput?.value);
        const lineInstallation = parseNumber(lineInstallationInput?.value);
        const lineGrand = lineSubtotal + tax + lineDelivery + lineInstallation;

        if (lineTotalEl) {
            lineTotalEl.textContent = formatMoney(lineGrand);
        }

        return { lineSubtotal, tax, lineGrand };
    }

    function recalcSummary() {
        if (!linesBody) {
            return;
        }

        let subtotal = 0;
        let taxTotal = 0;
        let linesGrand = 0;

        linesBody.querySelectorAll('.vq-line-row').forEach(function (row) {
            const result = recalcRow(row);
            subtotal += result.lineSubtotal;
            taxTotal += result.tax;
            linesGrand += result.lineGrand;
        });

        const headerDelivery = parseNumber(document.getElementById('delivery_charges')?.value);
        const headerInstallation = parseNumber(document.getElementById('installation_charges')?.value);
        const headerDiscount = parseNumber(document.getElementById('total_discount')?.value);
        const grandTotal = linesGrand + headerDelivery + headerInstallation - headerDiscount;

        if (subtotalEl) {
            subtotalEl.textContent = formatMoney(subtotal);
        }
        if (taxTotalEl) {
            taxTotalEl.textContent = formatMoney(taxTotal);
        }
        if (grandTotalEl) {
            grandTotalEl.textContent = formatMoney(grandTotal);
        }
    }

    if (linesBody) {
        linesBody.addEventListener('input', function (event) {
            if (event.target.matches('.vq-unit-price, .vq-qty-quoted, .vq-total-price, .vq-discount, .vq-tax-rate, .vq-tax, .vq-line-delivery, .vq-line-installation')) {
                recalcSummary();
            }
        });
    }

    document.querySelectorAll('.vq-summary-input').forEach(function (input) {
        input.addEventListener('input', recalcSummary);
    });

    recalcSummary();

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
