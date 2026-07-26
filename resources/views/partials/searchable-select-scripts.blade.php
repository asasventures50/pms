{{-- Shared searchable dropdown (search inside select). Safe to include more than once. --}}
<script>
    (function () {
        if (window.__pmsSearchableSelectWired) {
            document.querySelectorAll('[data-searchable-select]').forEach(function (root) {
                if (typeof window.__pmsInitSearchableSelect === 'function') {
                    window.__pmsInitSearchableSelect(root);
                }
            });
            return;
        }

        window.__pmsSearchableSelectWired = true;

        let openSearchableSelect = null;

        function getSearchableSelectRoot(panel) {
            const rootId = panel.dataset.searchableSelectRootId;
            if (rootId) {
                return document.querySelector('[data-searchable-select-root-id="' + rootId + '"]');
            }

            return panel.closest('[data-searchable-select]');
        }

        function positionSearchableSelectPanel(btn, panel) {
            const rect = btn.getBoundingClientRect();
            const width = Math.max(rect.width, 192);
            let left = rect.left;
            const margin = 8;

            if (left + width > window.innerWidth - margin) {
                left = window.innerWidth - width - margin;
            }
            if (left < margin) {
                left = margin;
            }

            panel.style.width = width + 'px';
            panel.style.left = left + 'px';

            panel.classList.remove('hidden');
            const panelHeight = panel.offsetHeight || 280;
            const spaceBelow = window.innerHeight - rect.bottom - margin;
            const spaceAbove = rect.top - margin;

            if (spaceBelow >= panelHeight || spaceBelow >= spaceAbove) {
                panel.style.top = (rect.bottom + 4) + 'px';
            } else {
                panel.style.top = Math.max(margin, rect.top - panelHeight - 4) + 'px';
            }
        }

        function attachSearchableSelectPanel(root, panel) {
            if (panel.parentElement !== document.body) {
                document.body.appendChild(panel);
            }
            panel.dataset.searchableSelectDetached = '1';
        }

        function restoreSearchableSelectPanel(root, panel) {
            panel.classList.add('hidden');
            panel.style.top = '';
            panel.style.left = '';
            panel.style.width = '';
            panel.dataset.searchableSelectDetached = '0';

            if (root && panel.parentElement === document.body) {
                root.appendChild(panel);
            }
        }

        function closeSearchableSelectPanel(panel) {
            if (!panel) {
                return;
            }

            const root = getSearchableSelectRoot(panel);
            restoreSearchableSelectPanel(root, panel);

            const btn = root ? root.querySelector('[data-searchable-select-btn]') : null;
            if (btn) {
                btn.setAttribute('aria-expanded', 'false');
            }

            if (openSearchableSelect && openSearchableSelect.panel === panel) {
                openSearchableSelect = null;
            }
        }

        function closeAllSearchableSelectPanels(exceptPanel) {
            document.querySelectorAll('[data-searchable-select-panel]').forEach(function (panel) {
                if (panel !== exceptPanel) {
                    closeSearchableSelectPanel(panel);
                }
            });
        }

        function filterSearchableSelectOptions(panel, query) {
            const normalized = query.trim().toLowerCase();
            let visibleCount = 0;

            panel.querySelectorAll('[data-searchable-select-option]').forEach(function (option) {
                const haystack = (option.getAttribute('data-search') || option.textContent || '').toLowerCase();
                const visible = normalized === '' || haystack.includes(normalized);
                option.classList.toggle('hidden', !visible);
                if (visible) {
                    visibleCount++;
                }
            });

            const emptyState = panel.querySelector('[data-searchable-select-empty]');
            if (emptyState) {
                emptyState.classList.toggle('hidden', visibleCount > 0);
            }
        }

        function setSearchableSelectValue(root, value, label) {
            const hidden = root.querySelector('[data-searchable-select-value]');
            const labelEl = root.querySelector('[data-searchable-select-label]');
            if (!hidden || !labelEl) {
                return;
            }

            hidden.value = value;
            labelEl.textContent = label;
            hidden.dispatchEvent(new Event('change', { bubbles: true }));

            const markSelected = function (option) {
                const isSelected = option.getAttribute('data-value') === String(value);
                option.classList.toggle('bg-slate-100', isSelected);
                option.classList.toggle('font-medium', isSelected);
                option.classList.toggle('text-slate-900', isSelected);
            };

            root.querySelectorAll('[data-searchable-select-option]').forEach(markSelected);

            const panel = root.querySelector('[data-searchable-select-panel]');
            if (panel) {
                panel.querySelectorAll('[data-searchable-select-option]').forEach(markSelected);
            }
        }

        function openSearchableSelectPanel(root, btn, panel, searchInput) {
            attachSearchableSelectPanel(root, panel);
            positionSearchableSelectPanel(btn, panel);
            btn.setAttribute('aria-expanded', 'true');
            openSearchableSelect = { root: root, btn: btn, panel: panel };

            if (searchInput) {
                searchInput.value = '';
                filterSearchableSelectOptions(panel, '');
                searchInput.focus();
            }
        }

        function initSearchableSelect(root) {
            if (!root || root.dataset.searchableSelectWired === '1') {
                return;
            }

            root.dataset.searchableSelectWired = '1';
            root.dataset.searchableSelectRootId = 'searchable-select-' + Math.random().toString(36).slice(2);

            const btn = root.querySelector('[data-searchable-select-btn]');
            const panel = root.querySelector('[data-searchable-select-panel]');
            const searchInput = panel ? panel.querySelector('[data-searchable-select-search]') : null;

            if (!btn || !panel) {
                return;
            }

            panel.dataset.searchableSelectRootId = root.dataset.searchableSelectRootId;

            btn.addEventListener('click', function (event) {
                event.stopPropagation();
                const isOpen = openSearchableSelect && openSearchableSelect.panel === panel;
                closeAllSearchableSelectPanels(isOpen ? null : panel);

                if (isOpen) {
                    closeSearchableSelectPanel(panel);
                } else {
                    openSearchableSelectPanel(root, btn, panel, searchInput);
                }
            });

            if (searchInput) {
                searchInput.addEventListener('input', function () {
                    filterSearchableSelectOptions(panel, searchInput.value);
                });

                searchInput.addEventListener('keydown', function (event) {
                    event.stopPropagation();
                });
            }

            panel.querySelectorAll('[data-searchable-select-option]').forEach(function (option) {
                option.addEventListener('click', function () {
                    setSearchableSelectValue(
                        root,
                        option.getAttribute('data-value') || '',
                        option.getAttribute('data-label') || option.textContent.trim()
                    );
                    closeSearchableSelectPanel(panel);
                });
            });
        }

        window.__pmsInitSearchableSelect = initSearchableSelect;

        document.querySelectorAll('[data-searchable-select]').forEach(initSearchableSelect);

        document.addEventListener('click', function (event) {
            if (event.target.closest('[data-searchable-select]') || event.target.closest('[data-searchable-select-panel]')) {
                return;
            }
            closeAllSearchableSelectPanels();
        });

        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape') {
                closeAllSearchableSelectPanels();
            }
        });

        window.addEventListener('resize', function () {
            if (!openSearchableSelect) {
                return;
            }
            positionSearchableSelectPanel(openSearchableSelect.btn, openSearchableSelect.panel);
        });

        window.addEventListener('scroll', function () {
            if (!openSearchableSelect) {
                return;
            }
            if (openSearchableSelect.panel.dataset.searchableSelectDetached === '1') {
                positionSearchableSelectPanel(openSearchableSelect.btn, openSearchableSelect.panel);
            }
        }, true);
    })();
</script>
