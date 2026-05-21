@php
    $prQuickStoreUrls = [
        'project' => auth()->user()->hasPermission('projects.create') ? route('projects.quick-store') : null,
        'zone' => auth()->user()->hasPermission('projects.update') ? route('zones.quick-store') : null,
    ];
@endphp
<script type="application/json" id="pr-quick-store-urls">@json($prQuickStoreUrls)</script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
    const quickStoreUrls = JSON.parse(
        document.getElementById('pr-quick-store-urls')?.textContent || '{}'
    );
    const projectQuickStoreUrl = quickStoreUrls.project ?? null;
    const zoneQuickStoreUrl = quickStoreUrls.zone ?? null;

    const supportingBody = document.getElementById('pr-supporting-files-body');
    const supportingTpl = document.getElementById('pr-supporting-file-template');
    const addSupportingBtn = document.getElementById('pr-add-supporting-file');

    function bindSupportingFileRow(row) {
        const input = row.querySelector('input[type="file"]');
        const nameEl = row.querySelector('.pr-supporting-file-name');

        input?.addEventListener('change', function () {
            const file = input.files?.[0];
            if (nameEl) {
                nameEl.textContent = file ? file.name : '';
            }
        });

        row.querySelector('.pr-remove-supporting-file')?.addEventListener('click', function () {
            row.remove();
        });
    }

    if (supportingBody && supportingTpl) {
        supportingBody.querySelectorAll('.pr-supporting-file-row').forEach(bindSupportingFileRow);

        addSupportingBtn?.addEventListener('click', function () {
            const row = supportingTpl.content.firstElementChild.cloneNode(true);
            supportingBody.appendChild(row);
            bindSupportingFileRow(row);
        });
    }

    const linesBody = document.getElementById('pr-lines-body');
    const template = document.getElementById('pr-line-template');
    const addBtn = document.getElementById('pr-add-line');
    const requestNumberInput = document.getElementById('request_number');
    const previewDocNumber = document.getElementById('pr-doc-number-preview');

    if (!linesBody || !template) {
        return;
    }

    function docNumber() {
        return requestNumberInput?.value?.trim() || previewDocNumber?.dataset.preview || '';
    }

    function lineNumberFor(index) {
        const code = docNumber();
        if (!code) {
            return '—';
        }
        const match = code.match(/-(\d+)$/);
        const seq = match ? Math.max(1, parseInt(match[1], 10)) : 1;
        const suffix = String(seq).padStart(2, '0') + '.' + (index + 1);

        return code + '-' + suffix;
    }

    function reindexRows() {
        linesBody.querySelectorAll('.pr-line-row').forEach(function (row, index) {
            const noCell = row.querySelector('.pr-line-no');
            if (noCell) {
                noCell.textContent = lineNumberFor(index);
            }
            row.querySelectorAll('[data-name]').forEach(function (input) {
                const field = input.getAttribute('data-name');
                input.setAttribute('name', 'items[' + index + '][' + field + ']');
            });
            row.querySelectorAll('[name^="items["]').forEach(function (input) {
                const match = input.getAttribute('name').match(/items\[\d+]\[(\w+)]/);
                if (match) {
                    input.setAttribute('name', 'items[' + index + '][' + match[1] + ']');
                }
            });
        });
    }

    function projectLabel(code, name) {
        return code + ' — ' + name;
    }

    function zoneLabel(code, name) {
        return code + ' — ' + name;
    }

    function appendProjectOption(project) {
        const id = String(project.id);
        document.querySelectorAll('[data-pr-project-select]').forEach(function (select) {
            if (select.querySelector('option[value="' + id + '"]')) {
                return;
            }
            const option = document.createElement('option');
            option.value = id;
            option.textContent = projectLabel(project.code, project.name);
            select.appendChild(option);
        });
    }

    function appendZoneOption(zone) {
        const id = String(zone.id);
        const projectId = String(zone.project_id);
        document.querySelectorAll('[data-pr-zone-select]').forEach(function (select) {
            if (select.querySelector('option[value="' + id + '"]')) {
                return;
            }
            const option = document.createElement('option');
            option.value = id;
            option.dataset.projectId = projectId;
            option.textContent = zoneLabel(zone.code, zone.name);
            select.appendChild(option);
        });
    }

    function syncZonesForRow(row) {
        const projectSelect = row.querySelector('[data-pr-project-select]');
        const zoneSelect = row.querySelector('[data-pr-zone-select]');
        const addZoneBtn = row.querySelector('[data-pr-add-zone]');
        if (!projectSelect || !zoneSelect) {
            return;
        }

        const projectId = projectSelect.value;
        const hasProject = Boolean(projectId);

        zoneSelect.disabled = !hasProject;
        if (addZoneBtn) {
            addZoneBtn.disabled = !hasProject;
        }

        if (!hasProject) {
            zoneSelect.value = '';
        }

        zoneSelect.querySelectorAll('option').forEach(function (option) {
            if (!option.value) {
                option.hidden = false;
                option.disabled = false;
                return;
            }

            const matches = hasProject && option.dataset.projectId === projectId;
            option.hidden = !matches;
            option.disabled = !matches;
        });

        const selected = zoneSelect.selectedOptions[0];
        if (selected && (selected.disabled || selected.hidden)) {
            zoneSelect.value = '';
        }
    }

    function bindProjectZone(row) {
        const projectSelect = row.querySelector('[data-pr-project-select]');
        projectSelect?.addEventListener('change', function () {
            syncZonesForRow(row);
        });
        syncZonesForRow(row);
    }

    let quickAddTargetRow = null;

    const projectModal = document.getElementById('pr-add-project-modal');
    const projectNameInput = document.getElementById('pr-add-project-name');
    const projectErrName = document.getElementById('pr-add-project-error-name');
    const projectErrGeneral = document.getElementById('pr-add-project-error-general');
    const projectCancelBtn = document.getElementById('pr-add-project-cancel');
    const projectSaveBtn = document.getElementById('pr-add-project-save');

    const zoneModal = document.getElementById('pr-add-zone-modal');
    const zoneProjectIdInput = document.getElementById('pr-add-zone-project-id');
    const zoneNameInput = document.getElementById('pr-add-zone-name');
    const zoneErrName = document.getElementById('pr-add-zone-error-name');
    const zoneErrGeneral = document.getElementById('pr-add-zone-error-general');
    const zoneCancelBtn = document.getElementById('pr-add-zone-cancel');
    const zoneSaveBtn = document.getElementById('pr-add-zone-save');

    function clearFieldErrors(errEl, inputEl) {
        if (errEl) {
            errEl.classList.add('hidden');
            errEl.textContent = '';
        }
        if (inputEl) {
            inputEl.classList.remove('border-red-500');
        }
    }

    function setFieldError(inputEl, errEl, message) {
        if (!inputEl || !errEl) {
            return;
        }
        errEl.textContent = message;
        errEl.classList.remove('hidden');
        inputEl.classList.add('border-red-500');
    }

    function closeModal(modal) {
        if (!modal) {
            return;
        }
        modal.classList.add('hidden');
        modal.setAttribute('aria-hidden', 'true');
        document.body.classList.remove('overflow-hidden');
    }

    function openModal(modal) {
        if (!modal) {
            return;
        }
        modal.classList.remove('hidden');
        modal.setAttribute('aria-hidden', 'false');
        document.body.classList.add('overflow-hidden');
    }

    function bindModalBackdrop(modal) {
        if (!modal) {
            return;
        }
        const panel = modal.querySelector('.relative');
        modal.addEventListener('click', function (e) {
            if (panel && !panel.contains(e.target)) {
                quickAddTargetRow = null;
                closeModal(modal);
            }
        });
    }

    bindModalBackdrop(projectModal);
    bindModalBackdrop(zoneModal);

    function openProjectModal(row) {
        if (!projectModal || !projectNameInput) {
            return;
        }
        quickAddTargetRow = row;
        projectNameInput.value = '';
        clearFieldErrors(projectErrName, projectNameInput);
        clearFieldErrors(projectErrGeneral, null);
        openModal(projectModal);
        setTimeout(function () {
            projectNameInput.focus();
        }, 0);
    }

    function openZoneModal(row) {
        if (!zoneModal || !zoneNameInput || !zoneProjectIdInput) {
            return;
        }
        const projectSelect = row.querySelector('[data-pr-project-select]');
        const projectId = projectSelect?.value || '';
        if (!projectId) {
            return;
        }
        quickAddTargetRow = row;
        zoneProjectIdInput.value = projectId;
        zoneNameInput.value = '';
        clearFieldErrors(zoneErrName, zoneNameInput);
        clearFieldErrors(zoneErrGeneral, null);
        openModal(zoneModal);
        setTimeout(function () {
            zoneNameInput.focus();
        }, 0);
    }

    projectCancelBtn?.addEventListener('click', function () {
        quickAddTargetRow = null;
        closeModal(projectModal);
    });

    zoneCancelBtn?.addEventListener('click', function () {
        quickAddTargetRow = null;
        closeModal(zoneModal);
    });

    projectSaveBtn?.addEventListener('click', async function () {
        if (!quickAddTargetRow || !projectQuickStoreUrl) {
            return;
        }

        const name = (projectNameInput?.value || '').trim();
        clearFieldErrors(projectErrName, projectNameInput);
        clearFieldErrors(projectErrGeneral, null);

        if (!name) {
            setFieldError(projectNameInput, projectErrName, 'Name is required.');
            return;
        }

        projectSaveBtn.disabled = true;

        try {
            const formData = new FormData();
            formData.append('name', name);

            const res = await fetch(projectQuickStoreUrl, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                },
                body: formData,
            });

            const payload = await res.json().catch(function () {
                return null;
            });

            if (!res.ok) {
                const message = payload?.errors?.name?.[0] || 'Failed to create project.';
                setFieldError(projectNameInput, projectErrName, message);
                return;
            }

            appendProjectOption(payload);

            const projectSelect = quickAddTargetRow.querySelector('[data-pr-project-select]');
            if (projectSelect) {
                projectSelect.value = String(payload.id);
            }

            document.querySelectorAll('.pr-line-row').forEach(syncZonesForRow);

            quickAddTargetRow = null;
            closeModal(projectModal);
        } finally {
            projectSaveBtn.disabled = false;
        }
    });

    zoneSaveBtn?.addEventListener('click', async function () {
        if (!quickAddTargetRow || !zoneQuickStoreUrl) {
            return;
        }

        const projectId = (zoneProjectIdInput?.value || '').trim();
        const name = (zoneNameInput?.value || '').trim();
        clearFieldErrors(zoneErrName, zoneNameInput);
        clearFieldErrors(zoneErrGeneral, null);

        if (!projectId) {
            if (zoneErrGeneral) {
                zoneErrGeneral.textContent = 'Select a project first.';
                zoneErrGeneral.classList.remove('hidden');
            }
            return;
        }
        if (!name) {
            setFieldError(zoneNameInput, zoneErrName, 'Name is required.');
            return;
        }

        zoneSaveBtn.disabled = true;

        try {
            const formData = new FormData();
            formData.append('project_id', projectId);
            formData.append('name', name);

            const res = await fetch(zoneQuickStoreUrl, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                },
                body: formData,
            });

            const payload = await res.json().catch(function () {
                return null;
            });

            if (!res.ok) {
                const message = payload?.errors?.name?.[0] || 'Failed to create zone.';
                setFieldError(zoneNameInput, zoneErrName, message);
                return;
            }

            appendZoneOption(payload);

            const zoneSelect = quickAddTargetRow.querySelector('[data-pr-zone-select]');
            if (zoneSelect) {
                zoneSelect.value = String(payload.id);
            }

            document.querySelectorAll('.pr-line-row').forEach(syncZonesForRow);

            quickAddTargetRow = null;
            closeModal(zoneModal);
        } finally {
            zoneSaveBtn.disabled = false;
        }
    });

    function bindRow(row) {
        bindProjectZone(row);

        row.querySelector('[data-pr-add-project]')?.addEventListener('click', function () {
            openProjectModal(row);
        });

        row.querySelector('[data-pr-add-zone]')?.addEventListener('click', function () {
            openZoneModal(row);
        });

        row.querySelector('.pr-remove-line')?.addEventListener('click', function () {
            if (linesBody.querySelectorAll('.pr-line-row').length <= 1) {
                return;
            }
            row.remove();
            reindexRows();
        });
    }

    function addRow() {
        const row = template.content.firstElementChild.cloneNode(true);
        linesBody.appendChild(row);
        reindexRows();
        bindRow(row);
        syncZonesForRow(row);
    }

    linesBody.querySelectorAll('.pr-line-row').forEach(bindRow);
    addBtn?.addEventListener('click', addRow);
    reindexRows();
});
</script>
