<script>
(function () {
    window.initVendorSearchSelect = function (options) {
        options = options || {};
        const onChange = typeof options.onChange === 'function' ? options.onChange : function () {};

        const vendorIdInput = document.getElementById('vendor_id');
        const vendorSearchRoot = document.querySelector('.vendor-search-select');

        if (!vendorIdInput || !vendorSearchRoot) {
            return;
        }

        const searchInput = document.getElementById('vendor_search_input');
        const resultsList = document.getElementById('vendor_search_results');
        const clearBtn = document.getElementById('vendor_search_clear');
        const maxVisible = 150;
        let vendorOptions = [];

        try {
            vendorOptions = JSON.parse(document.getElementById('vendor-select-options')?.textContent || '[]');
        } catch (e) {
            console.error(e);
        }

        let lastSelectedLabel = searchInput?.value || '';

        function closeVendorResults() {
            resultsList?.classList.add('hidden');
            searchInput?.setAttribute('aria-expanded', 'false');
        }

        function openVendorResults() {
            resultsList?.classList.remove('hidden');
            searchInput?.setAttribute('aria-expanded', 'true');
        }

        function normalizeFilterText(text) {
            return (text || '').toLowerCase().trim();
        }

        function filterQueryForList() {
            const typed = searchInput?.value.trim() || '';
            if (vendorIdInput.value && typed === lastSelectedLabel) {
                return '';
            }
            return typed;
        }

        function filterVendors(query) {
            const normalized = normalizeFilterText(query);
            if (!normalized) {
                return vendorOptions;
            }
            return vendorOptions.filter(function (item) {
                return normalizeFilterText(item.label).includes(normalized);
            });
        }

        function renderVendorResults(items) {
            if (!resultsList) {
                return;
            }

            resultsList.innerHTML = '';
            const total = items.length;

            if (total === 0) {
                const empty = document.createElement('li');
                empty.className = 'px-3 py-2 text-sm text-slate-500';
                empty.textContent = vendorOptions.length === 0
                    ? 'No vendors in the system.'
                    : 'No vendors match your search.';
                resultsList.appendChild(empty);
                return;
            }

            if (total > maxVisible) {
                const hint = document.createElement('li');
                hint.className = 'border-b border-slate-100 px-3 py-2 text-xs text-slate-500';
                hint.textContent = 'Showing ' + maxVisible + ' of ' + total + ' — type more to narrow the list.';
                resultsList.appendChild(hint);
            }

            items.slice(0, maxVisible).forEach(function (item) {
                const option = document.createElement('li');
                option.className = 'cursor-pointer px-3 py-2 text-sm text-slate-800 hover:bg-slate-50';
                option.textContent = item.label;
                option.setAttribute('role', 'option');
                option.addEventListener('mousedown', function (event) {
                    event.preventDefault();
                    selectVendor(item.id, item.label);
                });
                resultsList.appendChild(option);
            });
        }

        function refreshVendorDropdown() {
            renderVendorResults(filterVendors(filterQueryForList()));
            openVendorResults();
        }

        function selectVendor(id, label) {
            vendorIdInput.value = String(id);
            if (searchInput) {
                searchInput.value = label;
            }
            lastSelectedLabel = label;
            closeVendorResults();
            vendorIdInput.dispatchEvent(new Event('change', { bubbles: true }));
        }

        function clearVendorSelection() {
            vendorIdInput.value = '';
            if (searchInput) {
                searchInput.value = '';
            }
            lastSelectedLabel = '';
            closeVendorResults();
            vendorIdInput.dispatchEvent(new Event('change', { bubbles: true }));
        }

        vendorIdInput.addEventListener('change', function () {
            onChange(vendorIdInput.value);
        });

        clearBtn?.addEventListener('click', clearVendorSelection);

        searchInput?.addEventListener('focus', function () {
            refreshVendorDropdown();
        });

        searchInput?.addEventListener('input', function () {
            const query = searchInput.value.trim();
            if (vendorIdInput.value && query !== lastSelectedLabel) {
                vendorIdInput.value = '';
            }
            refreshVendorDropdown();
        });

        searchInput?.addEventListener('keydown', function (event) {
            if (event.key === 'Escape') {
                closeVendorResults();
            }
        });

        document.addEventListener('click', function (event) {
            if (!vendorSearchRoot.contains(event.target)) {
                closeVendorResults();
                if (searchInput && vendorIdInput.value && searchInput.value.trim() === '') {
                    searchInput.value = lastSelectedLabel;
                }
            }
        });
    };
})();
</script>
