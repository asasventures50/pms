<script>
(function () {
    document.addEventListener('DOMContentLoaded', function () {
        const linesBody = document.getElementById('sow-lines-body');
        const addLineBtn = document.getElementById('sow-add-line-btn');
        const notesList = document.getElementById('sow-notes-list');
        const addNoteBtn = document.getElementById('sow-add-note-btn');
        const currencyInput = document.getElementById('currency_code');
        const recipientInput = document.getElementById('recipient_name');
        const projectManagerInput = document.getElementById('project_manager_name');
        const scopeOfWorkInput = document.getElementById('scope_of_work');
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
        let previousProcurementRequestId = procurementRequestSelect?.value || '';

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

        function applyScopeTypesFromPr(scopeTypes) {
            const values = Array.isArray(scopeTypes) ? scopeTypes : [];
            if (values.length === 0) {
                return;
            }

            const allowed = new Set(values.map(function (value) {
                return String(value).trim().toLowerCase();
            }));
            document.querySelectorAll('[data-sow-scope-checkbox]').forEach(function (checkbox) {
                checkbox.checked = allowed.has((checkbox.value || '').trim().toLowerCase());
            });
        }

        function applyNotesFromPr(notes) {
            if (!notesList) {
                return;
            }

            const esc = function (value) {
                return String(value ?? '')
                    .replace(/&/g, '&amp;')
                    .replace(/"/g, '&quot;')
                    .replace(/</g, '&lt;');
            };

            const rows = Array.isArray(notes) ? notes.filter(function (note) {
                return String(note || '').trim() !== '';
            }) : [];

            if (rows.length === 0) {
                return;
            }

            notesList.innerHTML = '';
            rows.forEach(function (note, index) {
                const div = document.createElement('div');
                div.className = 'sow-note-row flex items-start gap-2';
                div.setAttribute('data-sow-note-row', '');
                div.innerHTML = ''
                    + '<textarea name="notes[]" rows="3" placeholder="Note ' + (index + 1) + '"'
                    + ' class="admin-filter-control sow-note-textarea min-h-[4.5rem] resize-y flex-1">'
                    + esc(note)
                    + '</textarea>'
                    + '<button type="button" data-sow-remove-note'
                    + ' class="shrink-0 rounded-lg border border-slate-300 px-3 py-2 text-sm text-slate-600 hover:bg-slate-50'
                    + (rows.length === 1 ? ' hidden' : '')
                    + '" title="Remove note">×</button>';
                notesList.appendChild(div);
            });
        }

        function prFormPayload(data) {
            if (!data || typeof data !== 'object') {
                return {};
            }
            if (data.form && typeof data.form === 'object') {
                return data.form;
            }
            return data;
        }

        function applyPrFormFields(data) {
            const form = prFormPayload(data);
            applyScopeTypesFromPr(form.scope_types || data.scope_types || []);
            applyCurrencyFromPr(form.currency_code || data.currency_code || '');
            applyNotesFromPr(form.notes || data.notes || []);
            applyPrSectionsFromData(form.pr_sections || data.pr_sections || null);

            if (recipientInput && (form.recipient_name || data.recipient_name)) {
                recipientInput.value = form.recipient_name || data.recipient_name || '';
            }
            if (projectManagerInput && (form.project_manager_name || data.project_manager_name)) {
                projectManagerInput.value = form.project_manager_name || data.project_manager_name || '';
            }
            if (scopeOfWorkInput && (form.scope_of_work || data.scope_of_work)) {
                scopeOfWorkInput.value = form.scope_of_work || data.scope_of_work || '';
            }
        }

        function boolSelectValue(value) {
            if (value === true || value === 1 || value === '1') {
                return '1';
            }
            if (value === false || value === 0 || value === '0') {
                return '0';
            }
            return '';
        }

        function escAttr(value) {
            return String(value ?? '')
                .replace(/&/g, '&amp;')
                .replace(/"/g, '&quot;')
                .replace(/</g, '&lt;');
        }

        function rebuildSowDocuments(rows) {
            const body = document.getElementById('sow-documents-body');
            if (!body) {
                return;
            }
            const list = Array.isArray(rows) && rows.length > 0 ? rows : [{}];
            body.innerHTML = '';
            list.forEach(function (row, index) {
                const div = document.createElement('div');
                div.className = 'sow-document-row grid gap-2 rounded border border-slate-100 p-3 sm:grid-cols-2';
                div.setAttribute('data-sow-document-row', '');
                div.innerHTML = ''
                    + '<input type="text" name="pr_sections[supporting_documents][' + index + '][document_type]" value="' + escAttr(row.document_type) + '" placeholder="Document type" class="admin-filter-control">'
                    + '<input type="text" name="pr_sections[supporting_documents][' + index + '][file_name]" value="' + escAttr(row.file_name) + '" placeholder="File name" class="admin-filter-control">'
                    + '<input type="text" name="pr_sections[supporting_documents][' + index + '][file_url]" value="' + escAttr(row.file_url) + '" placeholder="File URL" class="admin-filter-control sm:col-span-2">'
                    + '<input type="text" name="pr_sections[supporting_documents][' + index + '][file_description]" value="' + escAttr(row.file_description) + '" placeholder="Description" class="admin-filter-control sm:col-span-2">'
                    + '<button type="button" data-sow-remove-document-row class="text-sm text-slate-600 hover:text-slate-900 sm:col-span-2 text-left">Remove</button>';
                body.appendChild(div);
            });
        }

        function rebuildSowPaymentTerms(rows) {
            const body = document.getElementById('sow-payment-terms-body');
            if (!body) {
                return;
            }
            const list = Array.isArray(rows) && rows.length > 0 ? rows : [{}];
            body.innerHTML = '';
            list.forEach(function (row, index) {
                const div = document.createElement('div');
                div.className = 'sow-payment-term-row grid gap-2 rounded border border-slate-100 p-3 sm:grid-cols-4';
                div.setAttribute('data-sow-payment-term-row', '');
                div.innerHTML = ''
                    + '<input type="text" name="pr_sections[payment_terms][' + index + '][milestone]" value="' + escAttr(row.milestone) + '" placeholder="Milestone" class="admin-filter-control">'
                    + '<input type="text" name="pr_sections[payment_terms][' + index + '][amount]" value="' + escAttr(row.amount) + '" placeholder="Note" class="admin-filter-control">'
                    + '<input type="text" name="pr_sections[payment_terms][' + index + '][percentage]" value="' + escAttr(row.percentage) + '" placeholder="%" class="admin-filter-control">'
                    + '<input type="text" name="pr_sections[payment_terms][' + index + '][due_upon]" value="' + escAttr(row.due_upon) + '" placeholder="Due upon" class="admin-filter-control">'
                    + '<button type="button" data-sow-remove-payment-term-row class="text-sm text-slate-600 hover:text-slate-900 sm:col-span-4 text-left">Remove</button>';
                body.appendChild(div);
            });
        }

        function rebuildSowRetentions(rows) {
            const body = document.getElementById('sow-retentions-body');
            if (!body) {
                return;
            }
            const list = Array.isArray(rows) && rows.length > 0 ? rows : [{}];
            body.innerHTML = '';
            list.forEach(function (row, index) {
                const div = document.createElement('div');
                div.className = 'sow-retention-row grid gap-2 rounded border border-slate-100 p-3 sm:grid-cols-2';
                div.setAttribute('data-sow-retention-row', '');
                div.innerHTML = ''
                    + '<input type="text" name="pr_sections[retentions][' + index + '][retention_percent]" value="' + escAttr(row.retention_percent) + '" placeholder="Retention %" class="admin-filter-control">'
                    + '<input type="text" name="pr_sections[retentions][' + index + '][release_period]" value="' + escAttr(row.release_period) + '" placeholder="Release period" class="admin-filter-control">'
                    + '<button type="button" data-sow-remove-retention-row class="text-sm text-slate-600 hover:text-slate-900 sm:col-span-2 text-left">Remove</button>';
                body.appendChild(div);
            });
        }

        function applyPrSectionsFromData(sections) {
            if (!sections || typeof sections !== 'object') {
                return;
            }

            const info = sections.pr_info || {};
            ['project', 'zone', 'category', 'subcategory', 'procurement_type', 'geographic_scope', 'vendor_type'].forEach(function (key) {
                const el = document.querySelector('[name="pr_sections[pr_info][' + key + ']"]');
                if (el) {
                    el.value = info[key] ?? '';
                }
            });
            const samplesEl = document.querySelector('[name="pr_sections[pr_info][samples_required]"]');
            if (samplesEl && info.samples_required !== undefined && info.samples_required !== null && info.samples_required !== '') {
                samplesEl.value = boolSelectValue(info.samples_required);
            }

            const delivery = sections.delivery || {};
            const leadEl = document.querySelector('[name="pr_sections[delivery][lead_time_days]"]');
            if (leadEl) {
                leadEl.value = delivery.lead_time_days ?? '';
            }
            const locEl = document.querySelector('[name="pr_sections[delivery][location]"]');
            if (locEl) {
                locEl.value = delivery.location ?? '';
            }
            const flexEl = document.querySelector('[name="pr_sections[delivery][flexible_delivery_date]"]');
            if (flexEl && delivery.flexible_delivery_date !== undefined && delivery.flexible_delivery_date !== null && delivery.flexible_delivery_date !== '') {
                flexEl.value = boolSelectValue(delivery.flexible_delivery_date);
            }

            if (Array.isArray(sections.supporting_documents)) {
                rebuildSowDocuments(sections.supporting_documents);
            }
            if (Array.isArray(sections.payment_terms)) {
                rebuildSowPaymentTerms(sections.payment_terms);
            }
            if (Array.isArray(sections.retentions)) {
                rebuildSowRetentions(sections.retentions);
            }

            const maintenance = sections.maintenance || {};
            const afterSaleEl = document.querySelector('[name="pr_sections[maintenance][after_sale_service_applicable]"]');
            if (afterSaleEl && maintenance.after_sale_service_applicable !== undefined && maintenance.after_sale_service_applicable !== null && maintenance.after_sale_service_applicable !== '') {
                afterSaleEl.value = boolSelectValue(maintenance.after_sale_service_applicable);
            }
            const warrantyYearsEl = document.querySelector('[name="pr_sections[maintenance][warranty_years]"]');
            if (warrantyYearsEl) {
                warrantyYearsEl.value = maintenance.warranty_years ?? '';
            }
            const warrantyCovEl = document.querySelector('[name="pr_sections[maintenance][warranty_coverage]"]');
            if (warrantyCovEl) {
                warrantyCovEl.value = maintenance.warranty_coverage ?? '';
            }

            if (Array.isArray(sections.timeline)) {
                const byActivity = {};
                sections.timeline.forEach(function (row) {
                    if (row && row.activity) {
                        byActivity[row.activity] = row.duration_days ?? '';
                    }
                });
                document.querySelectorAll('[name^="pr_sections[timeline]"][name$="[activity]"]').forEach(function (input) {
                    const match = input.getAttribute('name').match(/pr_sections\[timeline]\[(\d+)]\[activity]/);
                    if (!match) {
                        return;
                    }
                    const durationInput = document.querySelector('[name="pr_sections[timeline][' + match[1] + '][duration_days]"]');
                    if (durationInput) {
                        durationInput.value = byActivity[input.value] ?? '';
                    }
                });
            }

            const compliance = sections.compliance || {};
            ['verification_required', 'prequalification_required', 'nda_required', 'conflict_of_interest_required', 'commitment_compliance_required'].forEach(function (key) {
                const el = document.querySelector('[name="pr_sections[compliance][' + key + ']"]');
                if (!el) {
                    return;
                }
                if (compliance[key] !== undefined && compliance[key] !== null && compliance[key] !== '') {
                    el.value = boolSelectValue(compliance[key]);
                }
            });
            const levelEl = document.querySelector('[name="pr_sections[compliance][prequalification_level]"]');
            if (levelEl && compliance.prequalification_level) {
                levelEl.value = compliance.prequalification_level;
            }
        }

        function importLinesFromPr(items, mode) {
            const rows = Array.isArray(items) ? items : [];
            if (mode === 'append') {
                appendRowsFromPr(rows);
            } else {
                replaceRowsFromPr(rows);
            }
        }

        function updatePrImportConfirmState() {
            if (!prImportConfirm) {
                return;
            }
            const anyChecked = prImportBody?.querySelector('input[type="checkbox"][data-pr-line]:checked');
            prImportConfirm.disabled = !anyChecked;
        }

        function openPrImportModal(requestNumber, items, onConfirm, options) {
            const opts = options || {};
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
                prImportSelectAll.checked = Boolean(opts.preselectAll && hasItems);
            }
            prImportPendingLines.forEach(function (line, index) {
                const tr = document.createElement('tr');
                const desc = (line.description || '').trim();
                const shortDesc = desc.length > 80 ? desc.slice(0, 80) + '…' : desc;
                const checked = opts.preselectAll && hasItems ? ' checked' : '';
                tr.innerHTML = ''
                    + '<td class="px-3 py-2"><input type="checkbox" data-pr-line="' + index + '" class="rounded border-slate-300"' + checked + '></td>'
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

        async function openPrImportForRequest(requestId, options) {
            const opts = options || {};
            if (!requestId) {
                return;
            }
            try {
                const data = await fetchProcurementRequestLines(requestId);
                if (!data) {
                    window.alert('Could not load P.R. lines. Check your access to this procurement request.');
                    return;
                }

                applyPrFormFields(data);

                const items = data.items || [];
                const shouldPromptForLines = linesBodyHasContent() && !opts.forceReplace;

                if (!shouldPromptForLines) {
                    importLinesFromPr(items, 'replace');
                    if (typeof opts.onImported === 'function') {
                        opts.onImported();
                    }
                    return;
                }

                openPrImportModal(data.request_number || '', items, function (selectedRows, mode) {
                    importLinesFromPr(selectedRows, mode);
                    if (typeof opts.onImported === 'function') {
                        opts.onImported();
                    }
                }, { preselectAll: Boolean(opts.preselectAll) });
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

        procurementRequestSelect?.addEventListener('change', function () {
            const requestId = procurementRequestSelect.value;
            updatePrImportButtonState();

            if (!requestId) {
                previousProcurementRequestId = '';
                return;
            }

            if (requestId === previousProcurementRequestId) {
                return;
            }

            openPrImportForRequest(requestId, {
                preselectAll: true,
                onImported: function () {
                    previousProcurementRequestId = requestId;
                },
            });
        });

        prImportBtn?.addEventListener('click', function () {
            const requestId = procurementRequestSelect?.value;
            if (requestId) {
                openPrImportForRequest(requestId, { preselectAll: true });
            }
        });

        document.getElementById('sow-add-document-row')?.addEventListener('click', function () {
            const body = document.getElementById('sow-documents-body');
            if (!body) {
                return;
            }
            const index = body.querySelectorAll('[data-sow-document-row]').length;
            const div = document.createElement('div');
            div.className = 'sow-document-row grid gap-2 rounded border border-slate-100 p-3 sm:grid-cols-2';
            div.setAttribute('data-sow-document-row', '');
            div.innerHTML = ''
                + '<input type="text" name="pr_sections[supporting_documents][' + index + '][document_type]" placeholder="Document type" class="admin-filter-control">'
                + '<input type="text" name="pr_sections[supporting_documents][' + index + '][file_name]" placeholder="File name" class="admin-filter-control">'
                + '<input type="text" name="pr_sections[supporting_documents][' + index + '][file_url]" placeholder="File URL" class="admin-filter-control sm:col-span-2">'
                + '<input type="text" name="pr_sections[supporting_documents][' + index + '][file_description]" placeholder="Description" class="admin-filter-control sm:col-span-2">'
                + '<button type="button" data-sow-remove-document-row class="text-sm text-slate-600 hover:text-slate-900 sm:col-span-2 text-left">Remove</button>';
            body.appendChild(div);
        });
        document.getElementById('sow-documents-body')?.addEventListener('click', function (e) {
            if (e.target.closest('[data-sow-remove-document-row]')) {
                e.target.closest('[data-sow-document-row]')?.remove();
                if (document.querySelectorAll('[data-sow-document-row]').length === 0) {
                    rebuildSowDocuments([{}]);
                }
            }
        });

        document.getElementById('sow-add-payment-term-row')?.addEventListener('click', function () {
            const body = document.getElementById('sow-payment-terms-body');
            if (!body) {
                return;
            }
            const index = body.querySelectorAll('[data-sow-payment-term-row]').length;
            const div = document.createElement('div');
            div.className = 'sow-payment-term-row grid gap-2 rounded border border-slate-100 p-3 sm:grid-cols-4';
            div.setAttribute('data-sow-payment-term-row', '');
            div.innerHTML = ''
                + '<input type="text" name="pr_sections[payment_terms][' + index + '][milestone]" placeholder="Milestone" class="admin-filter-control">'
                + '<input type="text" name="pr_sections[payment_terms][' + index + '][amount]" placeholder="Note" class="admin-filter-control">'
                + '<input type="text" name="pr_sections[payment_terms][' + index + '][percentage]" placeholder="%" class="admin-filter-control">'
                + '<input type="text" name="pr_sections[payment_terms][' + index + '][due_upon]" placeholder="Due upon" class="admin-filter-control">'
                + '<button type="button" data-sow-remove-payment-term-row class="text-sm text-slate-600 hover:text-slate-900 sm:col-span-4 text-left">Remove</button>';
            body.appendChild(div);
        });
        document.getElementById('sow-payment-terms-body')?.addEventListener('click', function (e) {
            if (e.target.closest('[data-sow-remove-payment-term-row]')) {
                e.target.closest('[data-sow-payment-term-row]')?.remove();
                if (document.querySelectorAll('[data-sow-payment-term-row]').length === 0) {
                    rebuildSowPaymentTerms([{}]);
                }
            }
        });

        document.getElementById('sow-add-retention-row')?.addEventListener('click', function () {
            const body = document.getElementById('sow-retentions-body');
            if (!body) {
                return;
            }
            const index = body.querySelectorAll('[data-sow-retention-row]').length;
            const div = document.createElement('div');
            div.className = 'sow-retention-row grid gap-2 rounded border border-slate-100 p-3 sm:grid-cols-2';
            div.setAttribute('data-sow-retention-row', '');
            div.innerHTML = ''
                + '<input type="text" name="pr_sections[retentions][' + index + '][retention_percent]" placeholder="Retention %" class="admin-filter-control">'
                + '<input type="text" name="pr_sections[retentions][' + index + '][release_period]" placeholder="Release period" class="admin-filter-control">'
                + '<button type="button" data-sow-remove-retention-row class="text-sm text-slate-600 hover:text-slate-900 sm:col-span-2 text-left">Remove</button>';
            body.appendChild(div);
        });
        document.getElementById('sow-retentions-body')?.addEventListener('click', function (e) {
            if (e.target.closest('[data-sow-remove-retention-row]')) {
                e.target.closest('[data-sow-retention-row]')?.remove();
                if (document.querySelectorAll('[data-sow-retention-row]').length === 0) {
                    rebuildSowRetentions([{}]);
                }
            }
        });
    });
})();
</script>
