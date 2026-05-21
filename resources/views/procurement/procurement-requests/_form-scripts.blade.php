<script>
document.addEventListener('DOMContentLoaded', function () {
    const supportingInput = document.getElementById('supporting_document');
    const supportingNameWrap = document.getElementById('pr-supporting-document-name');
    const supportingNameEl = supportingNameWrap?.querySelector('[data-filename]');

    function showSelectedSupportingFile() {
        if (!supportingNameWrap || !supportingNameEl || !supportingInput) {
            return;
        }
        const file = supportingInput.files?.[0];
        if (file) {
            supportingNameEl.textContent = file.name;
            supportingNameWrap.classList.remove('hidden');
        } else {
            supportingNameEl.textContent = '';
            supportingNameWrap.classList.add('hidden');
        }
    }

    supportingInput?.addEventListener('change', showSelectedSupportingFile);

    const supportingDropZone = supportingInput?.closest('label');
    if (supportingDropZone) {
        ['dragenter', 'dragover'].forEach(function (eventName) {
            supportingDropZone.addEventListener(eventName, function (e) {
                e.preventDefault();
                supportingDropZone.classList.add('border-slate-500', 'bg-slate-50');
            });
        });
        supportingDropZone.addEventListener('dragleave', function (e) {
            e.preventDefault();
            supportingDropZone.classList.remove('border-slate-500', 'bg-slate-50');
        });
        supportingDropZone.addEventListener('drop', function (e) {
            e.preventDefault();
            supportingDropZone.classList.remove('border-slate-500', 'bg-slate-50');
            const file = e.dataTransfer?.files?.[0];
            if (!file || !supportingInput) {
                return;
            }
            const transfer = new DataTransfer();
            transfer.items.add(file);
            supportingInput.files = transfer.files;
            showSelectedSupportingFile();
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

    function prSeqPart() {
        const raw = requestNumberInput?.value?.trim() || previewDocNumber?.dataset.preview || '';
        const match = raw.match(/-(\d+)$/);
        const seq = match ? Math.max(1, parseInt(match[1], 10)) : 1;

        return String(seq).padStart(2, '0');
    }

    function lineNumberFor(index) {
        return prSeqPart() + '.' + (index + 1);
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

    function bindRow(row) {
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
    }

    linesBody.querySelectorAll('.pr-line-row').forEach(bindRow);
    addBtn?.addEventListener('click', addRow);
    reindexRows();
});
</script>
