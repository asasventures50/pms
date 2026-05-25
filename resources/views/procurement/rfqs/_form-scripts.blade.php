<script>
document.addEventListener('DOMContentLoaded', function () {
    const linesBody = document.getElementById('rfq-lines-body');
    const template = document.getElementById('rfq-line-template');
    const addBtn = document.getElementById('rfq-add-line');
    const vendorSelect = document.getElementById('vendor_id');
    const prOptions = JSON.parse(
        document.getElementById('rfq-pr-item-options')?.textContent || '[]'
    );

    function optionById(id) {
        return prOptions.find(function (opt) {
            return String(opt.id) === String(id);
        });
    }

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

        function renderSupportingDocuments(row, documents) {
            const list = row.querySelector('[data-rfq-pr-documents-list]');
            const empty = row.querySelector('[data-rfq-pr-documents-empty]');
            if (! list || ! empty) {
                return;
            }

            const items = Array.isArray(documents) ? documents : [];
            list.innerHTML = '';

            if (items.length === 0) {
                list.classList.add('hidden');
                empty.classList.remove('hidden');
                return;
            }

            list.classList.remove('hidden');
            empty.classList.add('hidden');

            items.forEach(function (doc) {
                if (! doc || ! doc.url) {
                    return;
                }

                const li = document.createElement('li');
                li.className = 'flex flex-wrap items-center gap-2 rounded-lg border border-slate-200 bg-slate-50/80 px-3 py-2 text-sm';

                const link = document.createElement('a');
                link.href = doc.url;
                link.target = '_blank';
                link.rel = 'noopener';
                link.className = 'min-w-0 truncate font-medium text-slate-900 hover:underline';
                link.textContent = doc.file_name || doc.url;
                li.appendChild(link);

                if (doc.is_link) {
                    const badge = document.createElement('span');
                    badge.className = 'shrink-0 rounded bg-slate-200 px-1.5 py-0.5 text-xs font-medium text-slate-600';
                    badge.textContent = 'Link';
                    li.appendChild(badge);
                }

                list.appendChild(li);
            });
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
                ['pr_number', 'line_item', 'project', 'zone', 'category', 'subcategory', 'scope_type', 'description', 'unit', 'quantity', 'justification', 'scope_of_work', 'required_delivery_date', 'flexible_delivery_date', 'delivery_location'].forEach(function (key) {
                    setDisplay(row, key, '');
                });
                renderSupportingDocuments(row, []);
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
            setDisplay(row, 'scope_of_work', opt.scope_of_work);
            setDisplay(row, 'required_delivery_date', opt.required_delivery_date);
            setDisplay(row, 'flexible_delivery_date', opt.flexible_delivery_date);
            setDisplay(row, 'delivery_location', opt.delivery_location);
            renderSupportingDocuments(row, opt.supporting_documents);

            setHidden(row, 'item', opt.item);
            setHidden(row, 'description', opt.description);
            setHidden(row, 'quantity', opt.quantity);
            setHidden(row, 'unit', opt.unit);
            setHidden(row, 'request_lead_time', opt.request_lead_time);
        }

        function syncGeneralTermsFromLines() {
            if (typeof window.rfqSyncGeneralTerms === 'function') {
                window.rfqSyncGeneralTerms();
            }
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

            syncGeneralTermsFromLines();
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

    const generalTermsList = document.getElementById('rfq-general-terms-list');
    const customTermsList = document.getElementById('rfq-custom-terms-list');
    const customTermTemplate = document.getElementById('rfq-custom-term-template');
    const addCustomTermBtn = document.getElementById('rfq-add-custom-term');
    const scopeTermsMap = JSON.parse(
        document.getElementById('rfq-scope-terms-map')?.textContent || '{}'
    );
    const scopeTypeOrder = ['Supply', 'Service', 'Installation', 'Maintenance', 'Dismantling', 'Studies'];
    const termsLocaleInputs = document.querySelectorAll('.rfq-terms-locale');

    function currentTermsLocale() {
        const checked = document.querySelector('.rfq-terms-locale:checked');
        return checked ? checked.value : 'en';
    }

    function scopeTextsForLocale(scopeKey, locale) {
        const entry = scopeTermsMap[scopeKey];
        if (! entry) {
            return [];
        }
        if (Array.isArray(entry)) {
            return entry;
        }
        return entry[locale] || entry.en || entry.ar || [];
    }

    function applyCustomTermDirection(locale) {
        document.querySelectorAll('.rfq-custom-term-input, .rfq-payment-term-input').forEach(function (input) {
            if (locale === 'ar') {
                input.setAttribute('dir', 'rtl');
            } else {
                input.removeAttribute('dir');
            }
        });
    }

    if (generalTermsList) {
        function collectScopeTypesFromLines() {
            const found = {};
            if (! linesBody) {
                return [];
            }
            linesBody.querySelectorAll('.rfq-pr-item-select').forEach(function (select) {
                const opt = optionById(select.value);
                if (! opt || ! Array.isArray(opt.scope_types)) {
                    return;
                }
                opt.scope_types.forEach(function (scopeType) {
                    if (scopeType) {
                        found[scopeType] = true;
                    }
                });
            });

            return scopeTypeOrder.filter(function (scopeType) {
                return found[scopeType];
            });
        }

        function mergeGeneralTerms(scopeTypes) {
            const merged = [];
            const seen = {};

            function addTexts(texts) {
                (texts || []).forEach(function (text) {
                    if (! text || seen[text]) {
                        return;
                    }
                    seen[text] = true;
                    merged.push(text);
                });
            }

            const locale = currentTermsLocale();
            addTexts(scopeTextsForLocale('global', locale));
            scopeTypes.forEach(function (scopeType) {
                addTexts(scopeTextsForLocale(scopeType, locale));
            });

            return merged;
        }

        function renderGeneralTerms(terms) {
            generalTermsList.innerHTML = '';

            if (terms.length === 0) {
                const empty = document.createElement('li');
                empty.id = 'rfq-general-terms-empty';
                empty.className = 'text-slate-500';
                empty.textContent = 'Company-wide terms load automatically. Select line items to include scope-specific terms.';
                generalTermsList.appendChild(empty);
                return;
            }

            const locale = currentTermsLocale();
            terms.forEach(function (text) {
                const row = document.createElement('li');
                row.className = 'rfq-general-term-row flex gap-2';
                row.innerHTML = '<span class="shrink-0">-</span><span class="min-w-0 flex-1"></span>';
                const textEl = row.querySelector('span:last-child');
                textEl.textContent = text;
                if (locale === 'ar') {
                    textEl.setAttribute('dir', 'rtl');
                }
                generalTermsList.appendChild(row);
            });
        }

        window.rfqSyncGeneralTerms = function () {
            renderGeneralTerms(mergeGeneralTerms(collectScopeTypesFromLines()));
            applyCustomTermDirection(currentTermsLocale());
        };

        termsLocaleInputs.forEach(function (input) {
            input.addEventListener('change', function () {
                window.rfqSyncGeneralTerms();
            });
        });

        window.rfqSyncGeneralTerms();
    }

    if (customTermsList && customTermTemplate) {
        function reindexCustomTerms() {
            customTermsList.querySelectorAll('.rfq-custom-term-row').forEach(function (row, index) {
                const input = row.querySelector('input[type="text"]');
                if (input) {
                    input.setAttribute('name', 'terms_custom[' + index + ']');
                }
            });
        }

        function bindCustomTermRow(row) {
            row.querySelector('.rfq-remove-custom-term')?.addEventListener('click', function () {
                row.remove();
                reindexCustomTerms();
            });
        }

        customTermsList.querySelectorAll('.rfq-custom-term-row').forEach(bindCustomTermRow);
        reindexCustomTerms();

        addCustomTermBtn?.addEventListener('click', function () {
            const row = customTermTemplate.content.firstElementChild.cloneNode(true);
            customTermsList.appendChild(row);
            bindCustomTermRow(row);
            reindexCustomTerms();
            applyCustomTermDirection(currentTermsLocale());
            row.querySelector('input[type="text"]')?.focus();
        });
    }

    const paymentTermsList = document.getElementById('rfq-payment-terms-list');
    const paymentTermTemplate = document.getElementById('rfq-payment-term-template');
    const addPaymentTermBtn = document.getElementById('rfq-add-payment-term');

    if (paymentTermsList && paymentTermTemplate) {
        function reindexPaymentTerms() {
            paymentTermsList.querySelectorAll('.rfq-payment-term-row').forEach(function (row, index) {
                const input = row.querySelector('input[type="text"]');
                if (input) {
                    input.setAttribute('name', 'payment_terms[' + index + ']');
                }
            });
        }

        function bindPaymentTermRow(row) {
            row.querySelector('.rfq-remove-payment-term')?.addEventListener('click', function () {
                row.remove();
                reindexPaymentTerms();
            });
        }

        paymentTermsList.querySelectorAll('.rfq-payment-term-row').forEach(bindPaymentTermRow);
        reindexPaymentTerms();

        addPaymentTermBtn?.addEventListener('click', function () {
            const row = paymentTermTemplate.content.firstElementChild.cloneNode(true);
            paymentTermsList.appendChild(row);
            bindPaymentTermRow(row);
            reindexPaymentTerms();
            applyCustomTermDirection(currentTermsLocale());
            row.querySelector('input[type="text"]')?.focus();
        });
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
