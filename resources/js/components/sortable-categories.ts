document.addEventListener('DOMContentLoaded', function () {
    const tbody = document.getElementById('sortableCategories') as HTMLTableSectionElement | null;
    if (!tbody) return;

    const reorderUrl = tbody.dataset.reorderUrl;
    if (!reorderUrl) return;

    let draggedRow: HTMLTableRowElement | null = null;

    tbody.querySelectorAll<HTMLTableRowElement>('tr').forEach(row => {
        const handle = row.querySelector('.drag-handle');
        if (!handle) return;

        handle.addEventListener('mousedown', function () {
            row.draggable = true;
        });

        row.addEventListener('dragstart', function (e) {
            draggedRow = row;
            row.style.opacity = '0.4';
            if (e.dataTransfer) {
                e.dataTransfer.effectAllowed = 'move';
            }
        });

        row.addEventListener('dragend', function () {
            row.style.opacity = '1';
            row.draggable = false;
            draggedRow = null;
            tbody.querySelectorAll('tr').forEach(r => r.classList.remove('drag-over'));
        });

        row.addEventListener('dragover', function (e) {
            e.preventDefault();
            if (e.dataTransfer) {
                e.dataTransfer.dropEffect = 'move';
            }
            if (row !== draggedRow) {
                row.classList.add('drag-over');
            }
        });

        row.addEventListener('dragleave', function () {
            row.classList.remove('drag-over');
        });

        row.addEventListener('drop', function (e) {
            e.preventDefault();
            row.classList.remove('drag-over');
            if (draggedRow && row !== draggedRow) {
                const rows = Array.from(tbody.querySelectorAll('tr'));
                const draggedIndex = rows.indexOf(draggedRow);
                const targetIndex = rows.indexOf(row);

                if (draggedIndex < targetIndex) {
                    tbody.insertBefore(draggedRow, row.nextSibling);
                } else {
                    tbody.insertBefore(draggedRow, row);
                }

                saveOrder();
            }
        });
    });

    function saveOrder() {
        const order = Array.from(tbody!.querySelectorAll<HTMLTableRowElement>('tr')).map(row => row.dataset.id);
        const token = document.querySelector<HTMLMetaElement>('meta[name="csrf-token"]')?.getAttribute('content');

        fetch(reorderUrl!, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': token || '',
                'Accept': 'application/json',
            },
            body: JSON.stringify({ order: order }),
        })
        .then(response => response.json())
        .then(data => {
            if (!data.success) {
                alert('並び替えの保存に失敗しました');
            }
        })
        .catch(() => {
            alert('並び替えの保存に失敗しました');
        });
    }
});
