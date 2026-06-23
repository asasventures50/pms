<script>
(function () {
    document.addEventListener('DOMContentLoaded', function () {
        const poList = document.getElementById('invoice-po-list');
        const poCheckboxes = poList?.querySelectorAll('[data-invoice-po-checkbox]') || [];
        const linesSection = document.getElementById('invoice-lines-section');
        const feesSection = document.getElementById('invoice-fees-section');
        const notesSection = document.getElementById('invoice-notes-section');
        const notesList = document.getElementById('invoice-notes-list');
        const addNoteBtn = document.getElementById('invoice-add-note-btn');
        const customFeesList = document.getElementById('invoice-custom-fees-list');
        const addCustomFeeBtn = document.getElementById('invoice-add-custom-fee-btn');
        const linesBody = document.getElementById('invoice-lines-body');
        const selectAll = document.getElementById('invoice-select-all-lines');
        const mergeToolbar = document.getElementById('invoice-merge-toolbar');
        const mergeSelectedBtn = document.getElementById('invoice-merge-selected-btn');
        const mergeGroupsEl = document.getElementById('invoice-merge-groups');
        const mergeGroupsInputs = document.getElementById('invoice-merge-groups-inputs');
        const selectedItemIdsEl = document.getElementById('invoice-selected-item-ids');
        const invoiceForm = document.getElementById('invoice-form');
        const poSummary = document.getElementById('invoice-po-summary');
        const currencyInput = document.getElementById('currency_code');
        const currencyHint = document.getElementById('invoice-currency-hint');
        const grandTotalPreview = document.getElementById('invoice-grand-total-preview');
        const feeInputs = document.querySelectorAll('[data-invoice-fee-input]');
        const urlTemplate = poList?.getAttribute('data-items-url-template');

        let poItems = [];
        let oldItemIds = [];
        let mergeGroups = [];
        let nextGroupId = 1;
        let currencyTouched = @json(filled(old('currency_code')) || filled(($invoiceDefaults ?? [])['currency_code'] ?? null));

        try {
            oldItemIds = JSON.parse(document.getElementById('invoice-old-item-ids')?.textContent || '[]');
        } catch (e) {
            oldItemIds = [];
        }

        try {
            const oldGroups = JSON.parse(document.getElementById('invoice-old-merge-groups')?.textContent || '[]');
            if (Array.isArray(oldGroups)) {
                mergeGroups = oldGroups.map(function (group, index) {
                    return {
                        id: 'g' + (index + 1),
                        itemIds: (group.item_ids || []).map(function (id) { return parseInt(id, 10); }),
                        description: group.description || '',
                    };
                });
                nextGroupId = mergeGroups.length + 1;
            }
        } catch (e) {
            mergeGroups = [];
        }

        function formatMoney(value) {
            return (Math.round(parseFloat(value || 0) * 100) / 100).toFixed(2);
        }

        function groupForItem(itemId) {
            return mergeGroups.find(function (group) {
                return group.itemIds.includes(itemId);
            }) || null;
        }

        function selectedPoIds() {
            return Array.from(poCheckboxes)
                .filter(function (cb) { return cb.checked; })
                .map(function (cb) { return cb.value; });
        }

        function updateCurrencyLabels() {
            const code = (currencyInput?.value || '').trim().toUpperCase();
            const suffix = code ? ' (' + code + ')' : '';

            document.querySelectorAll('[data-invoice-price-label]').forEach(function (el) {
                const base = el.getAttribute('data-invoice-price-label-base') || '';
                el.textContent = base + suffix;
            });
        }

        function applyCurrencyFromPo(data, force) {
            if (!currencyInput) {
                return;
            }

            if (currencyTouched && !force) {
                updateCurrencyLabels();
                return;
            }

            const code = (data?.currency_code || 'USD').trim().toUpperCase();
            currencyInput.value = code;
            currencyTouched = false;
            updateCurrencyLabels();

            if (currencyHint) {
                const sourceLabels = {
                    po: 'From purchase order',
                    pr: 'From linked P.R.',
                    default: 'Default (USD)',
                };
                const source = data?.currency_source || 'default';
                currencyHint.textContent = sourceLabels[source] || '';
                currencyHint.classList.toggle('hidden', !currencyHint.textContent);
            }
        }

        currencyInput?.addEventListener('input', function () {
            currencyTouched = true;
            currencyInput.value = currencyInput.value.toUpperCase().replace(/[^A-Z]/g, '').slice(0, 3);
            updateCurrencyLabels();
            if (currencyHint) {
                currencyHint.classList.add('hidden');
            }
            updateGrandTotalPreview();
        });

        updateCurrencyLabels();

        function updateNoteRemoveButtons() {
            const rows = notesList?.querySelectorAll('[data-invoice-note-row]') || [];
            rows.forEach(function (row) {
                const removeBtn = row.querySelector('[data-invoice-remove-note]');
                if (removeBtn) {
                    removeBtn.classList.toggle('hidden', rows.length <= 1);
                }
            });
        }

        function setupNoteTextarea(textarea) {
            textarea.setAttribute('data-invoice-note-input', '');
            textarea.rows = 3;
            textarea.className = 'admin-filter-control invoice-note-textarea min-h-[4.5rem] resize-y flex-1';

            function autoResize() {
                textarea.style.height = 'auto';
                textarea.style.height = Math.max(72, textarea.scrollHeight) + 'px';
            }

            textarea.addEventListener('input', autoResize);
            autoResize();
        }

        function addNoteRow(value) {
            if (!notesList) {
                return;
            }

            const row = document.createElement('div');
            row.className = 'invoice-note-row flex items-start gap-2';
            row.setAttribute('data-invoice-note-row', '');

            const input = document.createElement('textarea');
            input.name = 'notes[]';
            input.value = value || '';
            input.placeholder = 'ملاحظة';
            setupNoteTextarea(input);

            const removeBtn = document.createElement('button');
            removeBtn.type = 'button';
            removeBtn.setAttribute('data-invoice-remove-note', '');
            removeBtn.className = 'rounded-lg border border-slate-300 px-3 py-2 text-sm text-slate-600 hover:bg-slate-50';
            removeBtn.title = 'حذف الملاحظة';
            removeBtn.textContent = '×';
            removeBtn.addEventListener('click', function () {
                row.remove();
                if (notesList.querySelectorAll('[data-invoice-note-row]').length === 0) {
                    addNoteRow('');
                }
                updateNoteRemoveButtons();
            });

            row.appendChild(input);
            row.appendChild(removeBtn);
            notesList.appendChild(row);
            updateNoteRemoveButtons();
        }

        notesList?.querySelectorAll('[data-invoice-remove-note]').forEach(function (btn) {
            btn.addEventListener('click', function () {
                const row = btn.closest('[data-invoice-note-row]');
                row?.remove();
                if (notesList.querySelectorAll('[data-invoice-note-row]').length === 0) {
                    addNoteRow('');
                }
                updateNoteRemoveButtons();
            });
        });

        addNoteBtn?.addEventListener('click', function () {
            addNoteRow('');
        });

        notesList?.querySelectorAll('[data-invoice-note-input]').forEach(setupNoteTextarea);

        updateNoteRemoveButtons();

        function reindexCustomFeeInputs() {
            const rows = customFeesList?.querySelectorAll('[data-invoice-custom-fee-row]') || [];
            rows.forEach(function (row, index) {
                const labelInput = row.querySelector('[data-invoice-custom-fee-label]');
                const amountInput = row.querySelector('[data-invoice-custom-fee-amount]');
                if (labelInput) {
                    labelInput.name = 'custom_fees[' + index + '][label]';
                }
                if (amountInput) {
                    amountInput.name = 'custom_fees[' + index + '][amount]';
                }
            });
        }

        function setupCustomFeeAmountInput(input) {
            input.addEventListener('input', updateGrandTotalPreview);
        }

        function addCustomFeeRow(label, amount) {
            if (!customFeesList) {
                return;
            }

            const row = document.createElement('div');
            row.className = 'invoice-custom-fee-row flex flex-wrap items-end gap-2 sm:flex-nowrap';
            row.setAttribute('data-invoice-custom-fee-row', '');

            const labelWrap = document.createElement('div');
            labelWrap.className = 'min-w-0 flex-1';

            const labelFieldLabel = document.createElement('label');
            labelFieldLabel.className = 'block text-xs font-medium text-slate-600';
            labelFieldLabel.textContent = 'البيان';

            const labelInput = document.createElement('input');
            labelInput.type = 'text';
            labelInput.value = label || '';
            labelInput.placeholder = 'مثال: أجور واتعاب';
            labelInput.setAttribute('data-invoice-custom-fee-label', '');
            labelInput.className = 'admin-filter-control mt-1 w-full';

            labelWrap.appendChild(labelFieldLabel);
            labelWrap.appendChild(labelInput);

            const amountWrap = document.createElement('div');
            amountWrap.className = 'w-36 shrink-0';

            const amountFieldLabel = document.createElement('label');
            amountFieldLabel.className = 'block text-xs font-medium text-slate-600';
            amountFieldLabel.textContent = 'المبلغ';

            const amountInput = document.createElement('input');
            amountInput.type = 'number';
            amountInput.value = amount ?? '';
            amountInput.min = '0';
            amountInput.step = '0.01';
            amountInput.setAttribute('data-invoice-custom-fee-amount', '');
            amountInput.className = 'admin-filter-control mt-1 w-full text-right';
            setupCustomFeeAmountInput(amountInput);

            amountWrap.appendChild(amountFieldLabel);
            amountWrap.appendChild(amountInput);

            const removeBtn = document.createElement('button');
            removeBtn.type = 'button';
            removeBtn.setAttribute('data-invoice-remove-custom-fee', '');
            removeBtn.className = 'shrink-0 rounded-lg border border-slate-300 px-3 py-2 text-sm text-slate-600 hover:bg-slate-50';
            removeBtn.title = 'حذف البند';
            removeBtn.textContent = '×';
            removeBtn.addEventListener('click', function () {
                row.remove();
                reindexCustomFeeInputs();
                updateGrandTotalPreview();
            });

            row.appendChild(labelWrap);
            row.appendChild(amountWrap);
            row.appendChild(removeBtn);
            customFeesList.appendChild(row);
            reindexCustomFeeInputs();
        }

        customFeesList?.querySelectorAll('[data-invoice-remove-custom-fee]').forEach(function (btn) {
            btn.addEventListener('click', function () {
                const row = btn.closest('[data-invoice-custom-fee-row]');
                row?.remove();
                reindexCustomFeeInputs();
                updateGrandTotalPreview();
            });
        });

        customFeesList?.querySelectorAll('[data-invoice-custom-fee-amount]').forEach(setupCustomFeeAmountInput);

        addCustomFeeBtn?.addEventListener('click', function () {
            addCustomFeeRow('', '');
        });

        reindexCustomFeeInputs();

        function selectedCheckboxes() {
            return linesBody?.querySelectorAll('input[type="checkbox"][data-po-item-id]:checked') || [];
        }

        function allLineCheckboxes() {
            return linesBody?.querySelectorAll('input[type="checkbox"][data-po-item-id]') || [];
        }

        function updateSelectAllState() {
            const boxes = allLineCheckboxes();
            const checked = selectedCheckboxes();
            if (!selectAll) {
                return;
            }
            selectAll.checked = boxes.length > 0 && checked.length === boxes.length;
            selectAll.indeterminate = checked.length > 0 && checked.length < boxes.length;
        }

        function selectedUngroupedItemIds() {
            const ids = [];
            selectedCheckboxes().forEach(function (cb) {
                const itemId = parseInt(cb.getAttribute('data-po-item-id'), 10);
                if (!groupForItem(itemId)) {
                    ids.push(itemId);
                }
            });
            return ids;
        }

        function syncSelectedItemInputs() {
            if (!selectedItemIdsEl) {
                return;
            }

            selectedItemIdsEl.innerHTML = '';

            allLineCheckboxes().forEach(function (cb) {
                if (!cb.checked) {
                    return;
                }

                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'purchase_order_item_ids[]';
                input.value = cb.value;
                selectedItemIdsEl.appendChild(input);
            });
        }

        function onLineCheckboxChange(checkbox) {
            const itemId = parseInt(checkbox.getAttribute('data-po-item-id'), 10);

            if (groupForItem(itemId) && !checkbox.checked) {
                checkbox.checked = true;
                return;
            }

            updateSelectAllState();
            updateMergeToolbar();
            updateGrandTotalPreview();
            syncSelectedItemInputs();
        }

        function updateMergeToolbar() {
            const ungroupedSelected = selectedUngroupedItemIds();
            if (mergeToolbar) {
                mergeToolbar.classList.toggle('hidden', allLineCheckboxes().length === 0);
            }
            if (mergeSelectedBtn) {
                mergeSelectedBtn.disabled = ungroupedSelected.length < 2;
            }
        }

        function effectiveLineTotalForItem(itemId) {
            const group = groupForItem(itemId);
            if (!group) {
                const item = poItems.find(function (row) { return row.id === itemId; });
                return item ? parseFloat(item.line_total || 0) : 0;
            }

            let total = 0;
            group.itemIds.forEach(function (id) {
                const item = poItems.find(function (row) { return row.id === id; });
                if (item) {
                    total += parseFloat(item.line_total || 0);
                }
            });
            return total;
        }

        function countedItemIdsForTotal() {
            const seenGroups = {};
            const ids = [];

            selectedCheckboxes().forEach(function (cb) {
                const itemId = parseInt(cb.getAttribute('data-po-item-id'), 10);
                const group = groupForItem(itemId);
                if (!group) {
                    ids.push(itemId);
                    return;
                }
                if (!seenGroups[group.id]) {
                    seenGroups[group.id] = true;
                    ids.push(itemId);
                }
            });

            return ids;
        }

        function linesSubtotalFromSelection() {
            let total = 0;
            countedItemIdsForTotal().forEach(function (itemId) {
                total += effectiveLineTotalForItem(itemId);
            });
            return total;
        }

        function feesSubtotal() {
            let total = 0;
            feeInputs.forEach(function (input) {
                total += parseFloat(input.value || 0);
            });
            (customFeesList?.querySelectorAll('[data-invoice-custom-fee-amount]') || []).forEach(function (input) {
                total += parseFloat(input.value || 0);
            });
            return total;
        }

        function updateGrandTotalPreview() {
            if (!grandTotalPreview) {
                return;
            }
            const total = linesSubtotalFromSelection() + feesSubtotal();
            const code = (currencyInput?.value || '').trim().toUpperCase();
            grandTotalPreview.textContent = formatMoney(total) + (code ? ' ' + code : '');
        }

        feeInputs.forEach(function (input) {
            input.addEventListener('input', updateGrandTotalPreview);
        });

        function applyRowGroupStyles() {
            allLineCheckboxes().forEach(function (cb) {
                const row = cb.closest('tr');
                if (!row) {
                    return;
                }
                const itemId = parseInt(cb.getAttribute('data-po-item-id'), 10);
                const group = groupForItem(itemId);
                row.classList.toggle('bg-indigo-50', !!group);
                row.dataset.mergeGroup = group ? group.id : '';
            });
        }

        function syncMergeGroupInputs() {
            if (!mergeGroupsInputs) {
                return;
            }

            mergeGroupsInputs.innerHTML = '';

            mergeGroups.forEach(function (group, index) {
                const desc = document.createElement('input');
                desc.type = 'hidden';
                desc.name = 'merge_groups[' + index + '][description]';
                desc.value = group.description;
                desc.setAttribute('data-merge-group-desc', group.id);
                mergeGroupsInputs.appendChild(desc);

                group.itemIds.forEach(function (itemId) {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'merge_groups[' + index + '][item_ids][]';
                    input.value = String(itemId);
                    mergeGroupsInputs.appendChild(input);
                });
            });
        }

        function renderMergeGroupsPanel() {
            if (!mergeGroupsEl) {
                return;
            }

            mergeGroupsEl.innerHTML = '';

            mergeGroups.forEach(function (group, index) {
                const card = document.createElement('div');
                card.className = 'rounded-lg border border-indigo-200 bg-indigo-50 p-4';

                const header = document.createElement('div');
                header.className = 'flex flex-wrap items-center justify-between gap-2';

                const title = document.createElement('p');
                title.className = 'text-sm font-semibold text-indigo-900';
                title.textContent = 'مجموعة دمج #' + (index + 1) + ' (' + group.itemIds.length + ' بنود)';

                const ungroupBtn = document.createElement('button');
                ungroupBtn.type = 'button';
                ungroupBtn.className = 'text-sm font-medium text-red-700 hover:text-red-900';
                ungroupBtn.textContent = 'إلغاء الدمج';
                ungroupBtn.addEventListener('click', function () {
                    mergeGroups = mergeGroups.filter(function (g) { return g.id !== group.id; });
                    renderMergeGroupsPanel();
                    syncMergeGroupInputs();
                    applyRowGroupStyles();
                    updateMergeToolbar();
                    updateGrandTotalPreview();
                    syncSelectedItemInputs();
                });

                header.appendChild(title);
                header.appendChild(ungroupBtn);
                card.appendChild(header);

                const label = document.createElement('label');
                label.className = 'mt-3 block text-xs font-medium uppercase tracking-wide text-slate-600';
                label.textContent = 'وصف السطر المدمج (يظهر على الفاتورة)';

                const input = document.createElement('input');
                input.type = 'text';
                input.value = group.description;
                input.placeholder = 'مثال: تجهيزات مكتبية';
                input.className = 'admin-filter-control mt-1 w-full max-w-xl';
                input.addEventListener('input', function () {
                    group.description = input.value;
                    syncMergeGroupInputs();
                });

                card.appendChild(label);
                card.appendChild(input);

                const itemsList = document.createElement('p');
                itemsList.className = 'mt-2 text-xs text-slate-600';
                const labels = group.itemIds.map(function (itemId) {
                    const item = poItems.find(function (row) { return row.id === itemId; });
                    if (!item) {
                        return '#' + itemId;
                    }
                    return (item.po_number ? item.po_number + ': ' : '') + (item.description || '—');
                });
                itemsList.textContent = labels.join(' · ');

                card.appendChild(itemsList);
                mergeGroupsEl.appendChild(card);
            });

            syncMergeGroupInputs();
        }

        function createMergeGroup(itemIds) {
            mergeGroups.push({
                id: 'g' + (nextGroupId++),
                itemIds: itemIds.slice(),
                description: '',
            });
            renderMergeGroupsPanel();
            applyRowGroupStyles();

            itemIds.forEach(function (itemId) {
                const cb = linesBody?.querySelector('input[data-po-item-id="' + itemId + '"]');
                if (cb) {
                    cb.checked = true;
                }
            });

            updateSelectAllState();
            updateMergeToolbar();
            updateGrandTotalPreview();
            syncSelectedItemInputs();
        }

        mergeSelectedBtn?.addEventListener('click', function () {
            const itemIds = selectedUngroupedItemIds();
            if (itemIds.length < 2) {
                return;
            }
            createMergeGroup(itemIds);
        });

        function renderLines(items) {
            if (!linesBody) {
                return;
            }

            linesBody.innerHTML = '';
            poItems = Array.isArray(items) ? items : [];

            poItems.forEach(function (item) {
                const tr = document.createElement('tr');
                tr.className = 'align-top';

                const checkTd = document.createElement('td');
                checkTd.className = 'px-3 py-3';
                const checkbox = document.createElement('input');
                checkbox.type = 'checkbox';
                checkbox.value = String(item.id);
                checkbox.setAttribute('data-po-item-id', String(item.id));
                checkbox.className = 'rounded border-slate-300';
                const shouldCheck = oldItemIds.length === 0 || oldItemIds.includes(item.id);
                checkbox.checked = shouldCheck;
                checkbox.addEventListener('change', function () {
                    onLineCheckboxChange(checkbox);
                });
                checkTd.appendChild(checkbox);
                tr.appendChild(checkTd);

                const poTd = document.createElement('td');
                poTd.className = 'px-3 py-3 font-mono text-xs text-slate-600';
                poTd.textContent = item.po_number || '—';
                tr.appendChild(poTd);

                const projectTd = document.createElement('td');
                projectTd.className = 'px-3 py-3 text-sm text-slate-700';
                projectTd.textContent = item.project_zone || '—';
                tr.appendChild(projectTd);

                const descTd = document.createElement('td');
                descTd.className = 'px-3 py-3 text-slate-800';
                descTd.textContent = item.description || '—';
                if (item.line_code) {
                    const hint = document.createElement('span');
                    hint.className = 'mt-0.5 block font-mono text-xs text-slate-400';
                    hint.textContent = item.line_code + ' (not printed)';
                    descTd.appendChild(hint);
                }
                tr.appendChild(descTd);

                const qtyTd = document.createElement('td');
                qtyTd.className = 'px-3 py-3 text-slate-700';
                qtyTd.textContent = item.quantity;
                tr.appendChild(qtyTd);

                const unitTd = document.createElement('td');
                unitTd.className = 'px-3 py-3 text-slate-700';
                unitTd.textContent = item.unit || '—';
                tr.appendChild(unitTd);

                const priceTd = document.createElement('td');
                priceTd.className = 'px-3 py-3 text-right text-slate-700';
                priceTd.textContent = formatMoney(item.unit_price);
                tr.appendChild(priceTd);

                const totalTd = document.createElement('td');
                totalTd.className = 'px-3 py-3 text-right font-medium text-slate-900';
                totalTd.textContent = formatMoney(item.line_total);
                tr.appendChild(totalTd);

                linesBody.appendChild(tr);
            });

            oldItemIds = [];

            mergeGroups = mergeGroups
                .map(function (group) {
                    return {
                        id: group.id,
                        description: group.description,
                        itemIds: group.itemIds.filter(function (id) {
                            return poItems.some(function (item) { return item.id === id; });
                        }),
                    };
                })
                .filter(function (group) { return group.itemIds.length >= 2; });

            renderMergeGroupsPanel();
            applyRowGroupStyles();

            allLineCheckboxes().forEach(function (cb) {
                const itemId = parseInt(cb.getAttribute('data-po-item-id'), 10);
                if (groupForItem(itemId)) {
                    cb.checked = true;
                }
            });

            updateSelectAllState();
            updateMergeToolbar();
            updateGrandTotalPreview();
            syncSelectedItemInputs();
        }

        async function loadSelectedPurchaseOrders() {
            const poIds = selectedPoIds();
            if (!urlTemplate || poIds.length === 0) {
                linesSection?.classList.add('hidden');
                feesSection?.classList.add('hidden');
                notesSection?.classList.add('hidden');
                poSummary?.classList.add('hidden');
                mergeToolbar?.classList.add('hidden');
                poItems = [];
                mergeGroups = [];
                renderMergeGroupsPanel();
                if (linesBody) {
                    linesBody.innerHTML = '';
                }
                return;
            }

            const responses = await Promise.all(poIds.map(function (poId) {
                return fetch(urlTemplate.replace('__ID__', poId), {
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                }).then(function (response) {
                    if (!response.ok) {
                        throw new Error('Failed to load PO ' + poId);
                    }
                    return response.json();
                });
            }));

            const allItems = [];
            responses.forEach(function (data) {
                (data.items || []).forEach(function (item) {
                    allItems.push(item);
                });
            });

            renderLines(allItems);

            if (responses[0]) {
                applyCurrencyFromPo(responses[0], poIds.length === 1);
            }

            if (poSummary) {
                const summaries = responses.map(function (data) {
                    const parts = [data.po_number];
                    if (data.vendor_company_name) {
                        parts.push(data.vendor_company_name);
                    }
                    return parts.join(' · ');
                });
                poSummary.textContent = summaries.join(' | ');
                poSummary.classList.remove('hidden');
            }

            linesSection?.classList.remove('hidden');
            feesSection?.classList.remove('hidden');
            notesSection?.classList.remove('hidden');
        }

        poCheckboxes.forEach(function (checkbox) {
            checkbox.addEventListener('change', function () {
                if (selectedPoIds().length === 0) {
                    currencyTouched = false;
                    mergeGroups = [];
                    renderMergeGroupsPanel();
                }
                loadSelectedPurchaseOrders();
            });
        });

        selectAll?.addEventListener('change', function () {
            allLineCheckboxes().forEach(function (cb) {
                const itemId = parseInt(cb.getAttribute('data-po-item-id'), 10);
                if (groupForItem(itemId)) {
                    cb.checked = true;
                    return;
                }
                cb.checked = selectAll.checked;
            });
            updateSelectAllState();
            updateMergeToolbar();
            updateGrandTotalPreview();
            syncSelectedItemInputs();
        });

        invoiceForm?.addEventListener('submit', function () {
            syncSelectedItemInputs();
            syncMergeGroupInputs();
        });

        if (selectedPoIds().length > 0) {
            loadSelectedPurchaseOrders();
        } else {
            renderMergeGroupsPanel();
        }

        updateGrandTotalPreview();
        syncSelectedItemInputs();
    });
})();
</script>
