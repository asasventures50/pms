<script>
document.addEventListener('DOMContentLoaded', function () {
    const linesBody = document.getElementById('pr-lines-body');
    const template = document.getElementById('pr-line-template');
    const addBtn = document.getElementById('pr-add-line');

    if (!linesBody || !template) {
        return;
    }

    function reindexRows() {
        linesBody.querySelectorAll('.pr-line-row').forEach(function (row, index) {
            const noCell = row.querySelector('.pr-line-no');
            if (noCell) {
                noCell.textContent = String(index + 1);
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
});
</script>
