<script>
document.addEventListener('DOMContentLoaded', function () {
    const supportingInput = document.getElementById('supporting_documents');
    const supportingList = document.getElementById('pr-supporting-document-list');
    const supportingDropZone = document.getElementById('pr-supporting-dropzone');

    function showSelectedSupportingFiles() {
        if (!supportingInput || !supportingList) {
            return;
        }
        const files = supportingInput.files;
        supportingList.innerHTML = '';
        if (!files || files.length === 0) {
            supportingList.classList.add('hidden');
            return;
        }
        Array.from(files).forEach(function (file) {
            const li = document.createElement('li');
            li.textContent = file.name;
            supportingList.appendChild(li);
        });
        supportingList.classList.remove('hidden');
    }

    function assignFilesToInput(fileList) {
        if (!supportingInput || !fileList || fileList.length === 0) {
            return;
        }
        const transfer = new DataTransfer();
        Array.from(fileList).forEach(function (file) {
            transfer.items.add(file);
        });
        supportingInput.files = transfer.files;
        showSelectedSupportingFiles();
    }

    supportingInput?.addEventListener('change', showSelectedSupportingFiles);

    if (supportingDropZone && supportingInput) {
        let dragDepth = 0;

        supportingDropZone.addEventListener('dragenter', function (e) {
            e.preventDefault();
            dragDepth += 1;
            supportingDropZone.classList.add('border-slate-500', 'bg-slate-50');
        });

        supportingDropZone.addEventListener('dragover', function (e) {
            e.preventDefault();
        });

        supportingDropZone.addEventListener('dragleave', function (e) {
            e.preventDefault();
            dragDepth = Math.max(0, dragDepth - 1);
            if (dragDepth === 0) {
                supportingDropZone.classList.remove('border-slate-500', 'bg-slate-50');
            }
        });

        supportingDropZone.addEventListener('drop', function (e) {
            e.preventDefault();
            dragDepth = 0;
            supportingDropZone.classList.remove('border-slate-500', 'bg-slate-50');
            if (e.dataTransfer?.files?.length) {
                assignFilesToInput(e.dataTransfer.files);
            }
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

    function syncZonesForRow(row) {
        const projectSelect = row.querySelector('[data-pr-project-select]');
        const zoneSelect = row.querySelector('[data-pr-zone-select]');
        if (!projectSelect || !zoneSelect) {
            return;
        }

        const projectId = projectSelect.value;

        zoneSelect.querySelectorAll('option').forEach(function (option) {
            if (!option.value) {
                option.hidden = false;
                option.disabled = false;
                return;
            }

            const matches = !projectId || option.dataset.projectId === projectId;
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

    function bindRow(row) {
        bindProjectZone(row);

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
