<script>
(function () {
    document.addEventListener('DOMContentLoaded', function () {
        const linesBody = document.getElementById('sow-lines-body');
        const addLineBtn = document.getElementById('sow-add-line-btn');
        const notesList = document.getElementById('sow-notes-list');
        const addNoteBtn = document.getElementById('sow-add-note-btn');
        const currencyInput = document.getElementById('currency_code');
        const grandTotalPreview = document.getElementById('sow-grand-total-preview');
        const procurementRequestSelect = document.getElementById('procurement_request_id');
        const prImportBtn = document.getElementById('sow-import-pr-lines');
        const prImportModal = document.getElementById('sow-pr-import-modal');
        const prImportSubtitle = document.getElementById('sow-pr-import-subtitle');
        const prImportEmpty = document.getElementById('sow-pr-import-empty');
        const prImportTable = document.getElementById('sow-pr-import-table');
        const prImportBody = document.getElementById('sow-pr-import-body');
        const prImportSelectAll = document.getElementById('sow-pr-import-select-all');
        const prImportConfirm = document.getElementById('sow-pr-import-confirm');
        let prImportPendingLines = [];
        let prImportOnConfirm = null;

        function formatMoney(value) {
            return (Math.round(parseFloat(value || 0) * 100) / 100).toFixed(2);
        }

        function updateCurrencyLabels() {
            const code = (currencyInput?.value || '').trim().toUpperCase();
            const suffix = code ? ' (' + code + ')' : '';
            document.querySelectorAll('[data-sow-price-label], [data-sow-total-label]').forEach(function (el) {
                const base = el.getAttribute('data-sow-price-label-base') || '';
                el.textContent = base + suffix;
            });
        }

        function reindexLines() {
            const rows = linesBody?.querySelectorAll('[data-sow-line-row]') || [];
            rows.forEach(function (row, index) {
                const num = row.querySelector('[data-sow-line-num]');
                if (num) num.textContent = String(index + 1);
                row.querySelectorAll('input').forEach(function (input) {
                    const name = input.getAttribute('name');
                    if (!name) return;
                    input.setAttribute('name', name.replace(/items\[\d+\]/, 'items[' + index + ']'));
                });
                const removeBtn = row.querySelector('[data-sow-remove-line]');
                if (removeBtn) removeBtn.classList.toggle('hidden', rows.length === 1);
            });
        }

        function lineRowTotal(row) {
            const qty = parseFloat(row.querySelector('[data-sow-line-quantity]')?.value || 0);
            const price = parseFloat(row.querySelector('[data-sow-line-unit-price]')?.value || 0);
            return qty > 0 && price >= 0 ? qty * price : 0;
        }

        function updateGrandTotal() {
            let total = 0;
            linesBody?.querySelectorAll('[data-sow-line-row]').forEach(function (row) {
                const lineTotal = lineRowTotal(row);
                const cell = row.querySelector('[data-sow-line-total]');
                if (cell) cell.textContent = formatMoney(lineTotal);
                total += lineTotal;
            });
            if (grandTotalPreview) {
                const code = (currencyInput?.value || '').trim().toUpperCase();
                grandTotalPreview.textContent = formatMoney(total) + (code ? ' ' + code : '');
            }
        }

        function bindRowInputs(row) {
            row.querySelectorAll('input').forEach(function (input) {
                input.addEventListener('input', updateGrandTotal);
            });
        }

        function createLineRow(index, data) {
            data = data || {};
            const tr = document.createElement('tr');
            tr.setAttribute('data-sow-line-row', '');
            const esc = function (value) {
                return String(value ?? '')
                    .replace(/&/g, '&amp;')
                    .replace(/"/g, '&quot;')
                    .replace(/</g, '&lt;');
            };
            tr.innerHTML = ''
                + '<td class="px-3 py-2 text-center text-slate-500" data-sow-line-num">' + (index + 1) + '</td>'
                + '<td class="px-3 py-2"><input type="text" name="items[' + index + '][project_zone]" value="' + esc(data.project_zone) + '" class="admin-filter-control w-full min-w-[8rem]"></td>'
                + '<td class="px-3 py-2"><input type="text" name="items[' + index + '][description]" required data-sow-line-description value="' + esc(data.description) + '" class="admin-filter-control w-full min-w-[10rem]"></td>'
                + '<td class="px-3 py-2"><input type="number" name="items[' + index + '][quantity]" min="0" step="0.001" data-sow-line-quantity value="' + esc(data.quantity) + '" class="admin-filter-control w-24 text-right"></td>'
                + '<td class="px-3 py-2"><input type="text" name="items[' + index + '][unit]" value="' + esc(data.unit) + '" class="admin-filter-control w-24"></td>'
                + '<td class="px-3 py-2"><input type="number" name="items[' + index + '][unit_price]" min="0" step="0.01" data-sow-line-unit-price value="' + esc(data.unit_price) + '" class="admin-filter-control w-28 text-right"></td>'
                + '<td class="px-3 py-2 text-right font-medium text-slate-900 tabular-nums" data-sow-line-total>0.00</td>'
                + '<td class="px-3 py-2 text-center"><button type="button" data-sow-remove-line class="rounded-lg border border-slate-300 px-2 py-1 text-sm text-slate-600 hover:bg-slate-50" title="Remove line">×</button></td>';
            bindRowInputs(tr);
            return tr;
        }

        function linesBodyHasContent() {
            return Array.from(linesBody?.querySelectorAll('[data-sow-line-row]') || []).some(function (row) {
                return (row.querySelector('[data-sow-line-description]')?.value || '').trim() !== '';
            });
        }

        function clearLineRows() {
            if (!linesBody) {
                return;
            }
            linesBody.innerHTML = '';
        }

        function appendRowsFromPr(selectedRows) {
            let index = linesBody?.querySelectorAll('[data-sow-line-row]').length || 0;
            selectedRows.forEach(function (line) {
                const row = createLineRow(index, {
                    project_zone: line.project_zone || '',
                    description: line.description || '',
                    quantity: line.quantity ?? '',
                    unit: line.unit || '',
                    unit_price: line.unit_price ?? '',
                });
                linesBody?.appendChild(row);
                index++;
            });
            reindexLines();
            updateGrandTotal();
        }

        function replaceRowsFromPr(selectedRows) {
            clearLineRows();
            if (selectedRows.length === 0) {
                linesBody?.appendChild(createLineRow(0));
            } else {
                selectedRows.forEach(function (line, index) {
                    linesBody?.appendChild(createLineRow(index, {
                        project_zone: line.project_zone || '',
                        description: line.description || '',
                        quantity: line.quantity ?? '',
                        unit: line.unit || '',
                        unit_price: line.unit_price ?? '',
                    }));
                });
            }
            reindexLines();
            updateGrandTotal();
        }

        function applyCurrencyFromPr(code) {
            if (!currencyInput || !code) {
                return;
            }
            currencyInput.value = String(code).trim().toUpperCase().slice(0, 3);
            updateCurrencyLabels();
            updateGrandTotal();
        }

        function updatePrImportConfirmState() {
            if (!prImportConfirm) {
                return;
            }
            const anyChecked = prImportBody?.querySelector('input[type="checkbox"][data-pr-line]:checked');
            prImportConfirm.disabled = !anyChecked;
        }

        function openPrImportModal(requestNumber, items, onConfirm) {
            prImportPendingLines = items || [];
            prImportOnConfirm = onConfirm;
            if (prImportSubtitle) {
                prImportSubtitle.textContent = requestNumber ? ('P.R. ' + requestNumber) : '';
            }
            if (prImportBody) {
                prImportBody.innerHTML = '';
            }
            const hasItems = prImportPendingLines.length > 0;
            prImportEmpty?.classList.toggle('hidden', hasItems);
            prImportTable?.classList.toggle('hidden', !hasItems);
            if (prImportSelectAll) {
                prImportSelectAll.checked = false;
            }
            prImportPendingLines.forEach(function (line, index) {
                const tr = document.createElement('tr');
                const desc = (line.description || '').trim();
                const shortDesc = desc.length > 80 ? desc.slice(0, 80) + '…' : desc;
                tr.innerHTML = ''
                    + '<td class="px-3 py-2"><input type="checkbox" data-pr-line="' + index + '" class="rounded border-slate-300"></td>'
                    + '<td class="px-3 py-2 font-mono text-xs text-slate-600">' + (line.line_code || '—') + '</td>'
                    + '<td class="px-3 py-2 text-slate-700">' + (line.project_zone || '—') + '</td>'
                    + '<td class="px-3 py-2 text-slate-800">' + shortDesc + '</td>'
                    + '<td class="px-3 py-2 text-right tabular-nums">' + formatMoney(line.quantity) + '</td>'
                    + '<td class="px-3 py-2 text-slate-700">' + (line.unit || '—') + '</td>'
                    + '<td class="px-3 py-2 text-right tabular-nums">' + formatMoney(line.unit_price) + '</td>';
                prImportBody?.appendChild(tr);
            });
            prImportBody?.querySelectorAll('input[type="checkbox"][data-pr-line]').forEach(function (cb) {
                cb.addEventListener('change', updatePrImportConfirmState);
            });
            updatePrImportConfirmState();
            prImportModal?.classList.remove('hidden');
        }

        function closePrImportModal() {
            prImportModal?.classList.add('hidden');
            prImportPendingLines = [];
            prImportOnConfirm = null;
        }

        async function fetchProcurementRequestLines(requestId) {
            const urlTemplate = procurementRequestSelect?.getAttribute('data-lines-url-template');
            if (!urlTemplate || !requestId) {
                return null;
            }
            const response = await fetch(urlTemplate.replace('__ID__', requestId), {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            });
            if (!response.ok) {
                return null;
            }
            return response.json();
        }

        async function openPrImportForRequest(requestId) {
            if (!requestId) {
                return;
            }
            try {
                const data = await fetchProcurementRequestLines(requestId);
                if (!data) {
                    window.alert('Could not load P.R. lines. Check your access to this procurement request.');
                    return;
                }
                openPrImportModal(data.request_number || '', data.items || [], function (selectedRows, mode) {
                    applyCurrencyFromPr(data.currency_code || '');
                    if (mode === 'append') {
                        appendRowsFromPr(selectedRows);
                    } else {
                        replaceRowsFromPr(selectedRows);
                    }
                });
            } catch (e) {
                console.error(e);
            }
        }

        function updatePrImportButtonState() {
            if (prImportBtn) {
                prImportBtn.disabled = !(procurementRequestSelect?.value);
            }
        }

        addLineBtn?.addEventListener('click', function () {
            const count = linesBody?.querySelectorAll('[data-sow-line-row]').length || 0;
            linesBody?.appendChild(createLineRow(count));
            reindexLines();
            updateGrandTotal();
        });

        linesBody?.addEventListener('click', function (e) {
            const btn = e.target.closest('[data-sow-remove-line]');
            if (!btn) return;
            const row = btn.closest('[data-sow-line-row]');
            if (!row || linesBody.querySelectorAll('[data-sow-line-row]').length <= 1) return;
            row.remove();
            reindexLines();
            updateGrandTotal();
        });

        addNoteBtn?.addEventListener('click', function () {
            const div = document.createElement('div');
            div.className = 'sow-note-row flex items-start gap-2';
            div.setAttribute('data-sow-note-row', '');
            div.innerHTML = ''
                + '<textarea name="notes[]" rows="3" class="admin-filter-control sow-note-textarea min-h-[4.5rem] resize-y flex-1"></textarea>'
                + '<button type="button" data-sow-remove-note class="shrink-0 rounded-lg border border-slate-300 px-3 py-2 text-sm text-slate-600 hover:bg-slate-50" title="Remove note">×</button>';
            notesList?.appendChild(div);
            notesList?.querySelectorAll('[data-sow-remove-note]').forEach(function (b) { b.classList.remove('hidden'); });
        });

        notesList?.addEventListener('click', function (e) {
            const btn = e.target.closest('[data-sow-remove-note]');
            if (!btn) return;
            const rows = notesList.querySelectorAll('[data-sow-note-row]');
            if (rows.length <= 1) return;
            btn.closest('[data-sow-note-row]')?.remove();
            if (notesList.querySelectorAll('[data-sow-note-row]').length === 1) {
                notesList.querySelector('[data-sow-remove-note]')?.classList.add('hidden');
            }
        });

        currencyInput?.addEventListener('input', function () {
            updateCurrencyLabels();
            updateGrandTotal();
        });

        if (typeof window.initVendorSearchSelect === 'function') {
            window.initVendorSearchSelect({
                onChange: async function (vendorId) {
                    if (!vendorId) {
                        return;
                    }
                    const data = await window.fetchVendorSnapshot(vendorId, 'purchase-order-snapshot');
                    if (data) {
                        window.applyVendorSnapshotFields(data, { fields: ['vendor_company_name'] });
                    }
                },
            });

            const preselectedVendorId = document.getElementById('vendor_id')?.value;
            if (preselectedVendorId) {
                window.fetchVendorSnapshot(preselectedVendorId, 'purchase-order-snapshot').then(function (data) {
                    if (data) {
                        window.applyVendorSnapshotFields(data, { fields: ['vendor_company_name'], onlyIfEmpty: true });
                    }
                });
            }
        }

        linesBody?.querySelectorAll('[data-sow-line-row]').forEach(bindRowInputs);
        updateCurrencyLabels();
        updateGrandTotal();
        updatePrImportButtonState();

        prImportModal?.querySelectorAll('[data-sow-pr-import-dismiss]').forEach(function (el) {
            el.addEventListener('click', closePrImportModal);
        });

        prImportSelectAll?.addEventListener('change', function () {
            prImportBody?.querySelectorAll('input[type="checkbox"][data-pr-line]').forEach(function (cb) {
                cb.checked = prImportSelectAll.checked;
            });
            updatePrImportConfirmState();
        });

        prImportConfirm?.addEventListener('click', function () {
            const selected = [];
            prImportBody?.querySelectorAll('input[type="checkbox"][data-pr-line]:checked').forEach(function (cb) {
                const index = parseInt(cb.getAttribute('data-pr-line'), 10);
                if (!Number.isNaN(index) && prImportPendingLines[index]) {
                    selected.push(prImportPendingLines[index]);
                }
            });
            if (selected.length === 0 || !prImportOnConfirm) {
                return;
            }
            let mode = 'replace';
            if (linesBodyHasContent()) {
                const replaceExisting = window.confirm(
                    'Replace all current line items with the selected P.R. lines?\n\nOK = replace table\nCancel = add selected lines to the table'
                );
                mode = replaceExisting ? 'replace' : 'append';
            }
            prImportOnConfirm(selected, mode);
            closePrImportModal();
        });

        procurementRequestSelect?.addEventListener('change', updatePrImportButtonState);

        prImportBtn?.addEventListener('click', function () {
            const requestId = procurementRequestSelect?.value;
            if (requestId) {
                openPrImportForRequest(requestId);
            }
        });
    });
})();
</script>
