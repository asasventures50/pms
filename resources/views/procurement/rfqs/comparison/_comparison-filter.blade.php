@if ($columns->count() >= 2)
    <script>
        (function () {
            const picker = document.getElementById('comparison-column-picker');
            if (!picker) {
                return;
            }

            const table = document.querySelector('.comparison-table');
            const feedback = document.getElementById('comparison-picker-feedback');
            const countEl = document.getElementById('comparison-visible-count');
            const applyBtn = document.getElementById('comparison-apply-btn');
            const printBtn = document.getElementById('comparison-print-btn');
            const headerPrintBtn = document.querySelector('[data-comparison-print-trigger]');

            function getSelectedIds() {
                return [...picker.querySelectorAll('.comparison-picker-checkbox:checked')]
                    .map((checkbox) => checkbox.value);
            }

            function getColumnIds() {
                return [...table.querySelectorAll('thead th[data-comparison-quotation]')]
                    .map((th) => th.dataset.comparisonQuotation);
            }

            function setFeedback(message, isError) {
                if (!feedback) {
                    return;
                }

                feedback.textContent = message;
                feedback.classList.remove('hidden', 'text-red-700', 'text-emerald-800');

                if (!message) {
                    feedback.classList.add('hidden');
                    return;
                }

                feedback.classList.add(isError ? 'text-red-700' : 'text-emerald-800');
            }

            function syncSupportingDocuments(selectedSet) {
                document.querySelectorAll('[data-comparison-quotation]').forEach((el) => {
                    if (el.tagName === 'TH') {
                        return;
                    }

                    const id = el.dataset.comparisonQuotation;
                    if (!id) {
                        return;
                    }

                    el.classList.toggle('comparison-col-hidden', !selectedSet.has(id));
                });
            }

            function syncTableBody(selectedSet, columnIds) {
                table.querySelectorAll('tbody tr').forEach((row) => {
                    const cells = [...row.children];

                    if (cells.length <= 1) {
                        return;
                    }

                    cells.slice(1).forEach((cell, index) => {
                        const id = columnIds[index];

                        if (!id) {
                            return;
                        }

                        cell.classList.toggle('comparison-col-hidden', !selectedSet.has(id));
                    });
                });
            }

            function syncHeaderColumns(selectedSet) {
                table.querySelectorAll('thead th[data-comparison-quotation]').forEach((th) => {
                    const id = th.dataset.comparisonQuotation;
                    th.classList.toggle('comparison-col-hidden', !selectedSet.has(id));
                });
            }

            function updateLowestHighlights(selectedSet, columnIds) {
                const grandTotalRow = table.querySelector('.comparison-grand-total-row');

                if (!grandTotalRow) {
                    return;
                }

                const totals = [];
                const grandCells = [...grandTotalRow.children].slice(1);

                grandCells.forEach((cell, index) => {
                    const id = columnIds[index];

                    if (!id || !selectedSet.has(id)) {
                        cell.classList.remove('bg-emerald-50', 'text-emerald-900');
                        return;
                    }

                    const value = parseFloat(cell.textContent.replace(/,/g, '').trim());

                    if (!Number.isNaN(value)) {
                        totals.push({ id, value, cell });
                    }
                });

                const minValue = totals.length
                    ? Math.min(...totals.map((entry) => entry.value))
                    : null;

                totals.forEach((entry) => {
                    const isLowest = minValue !== null && Math.abs(entry.value - minValue) < 0.001;
                    entry.cell.classList.toggle('bg-emerald-50', isLowest);
                    entry.cell.classList.toggle('text-emerald-900', isLowest);
                });

                table.querySelectorAll('thead th[data-comparison-quotation]').forEach((th) => {
                    const id = th.dataset.comparisonQuotation;
                    const badge = th.querySelector('.comparison-badge-lowest');

                    if (!badge) {
                        return;
                    }

                    const isVisible = selectedSet.has(id);
                    const isLowest = totals.some(
                        (entry) => entry.id === id
                            && minValue !== null
                            && Math.abs(entry.value - minValue) < 0.001
                    );

                    badge.classList.toggle('hidden', !isVisible || !isLowest);
                });
            }

            function applyComparisonFilter(options) {
                const silent = options && options.silent;
                const selectedIds = getSelectedIds();

                if (selectedIds.length < 2) {
                    if (!silent) {
                        setFeedback('Select at least two quotations to compare.', true);
                    }

                    return false;
                }

                const selectedSet = new Set(selectedIds);
                const columnIds = getColumnIds();

                syncHeaderColumns(selectedSet);
                syncTableBody(selectedSet, columnIds);
                syncSupportingDocuments(selectedSet);
                updateLowestHighlights(selectedSet, columnIds);

                if (table) {
                    table.style.setProperty('--comparison-quotation-count', String(selectedIds.length));
                }

                if (countEl) {
                    countEl.textContent = String(selectedIds.length);
                }

                if (!silent) {
                    setFeedback(
                        'Showing ' + selectedIds.length + ' quotation' + (selectedIds.length === 1 ? '' : 's') + ' for comparison.',
                        false
                    );
                }

                return true;
            }

            function handlePrint() {
                if (!applyComparisonFilter({ silent: true })) {
                    setFeedback('Select at least two quotations before printing.', true);
                    return;
                }

                window.print();
            }

            applyBtn?.addEventListener('click', () => applyComparisonFilter());
            printBtn?.addEventListener('click', handlePrint);
            headerPrintBtn?.addEventListener('click', handlePrint);

            applyComparisonFilter({ silent: true });
        })();
    </script>
@endif
