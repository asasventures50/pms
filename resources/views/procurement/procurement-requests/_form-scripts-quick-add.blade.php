<script>
(function () {
    const quickStoreUrls = JSON.parse(document.getElementById('pr-quick-store-urls')?.textContent || '{}');
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';

    function projectSelects() {
        return Array.from(document.querySelectorAll('[data-pr-project-select]'));
    }

    function zoneSelects() {
        return Array.from(document.querySelectorAll('[data-pr-zone-select]'));
    }

    function contextProjectSelect(context) {
        return context?.querySelector?.('[data-pr-project-select]')
            || document.getElementById('pr_project_id');
    }

    function contextZoneSelect(context) {
        return context?.querySelector?.('[data-pr-zone-select]')
            || document.getElementById('pr_zone_id');
    }

    function appendProjectOption(select, payload) {
        if (!select || select.querySelector('option[value="' + payload.id + '"]')) return;
        const opt = document.createElement('option');
        opt.value = payload.id;
        opt.textContent = payload.code + ' — ' + payload.name;
        select.appendChild(opt);
    }

    function appendZoneOption(select, payload) {
        if (!select || select.querySelector('option[value="' + payload.id + '"]')) return;
        const opt = document.createElement('option');
        opt.value = payload.id;
        opt.dataset.projectId = String(payload.project_id);
        opt.textContent = payload.code + ' — ' + payload.name;
        select.appendChild(opt);
    }

    window.prQuickAddProject = function (context) {
        const modal = document.getElementById('pr-add-project-modal');
        const input = document.getElementById('pr-add-project-name');
        if (!modal || !input || !quickStoreUrls.project) return;
        modal.dataset.prContext = context ? '1' : '';
        modal._prContextEl = context || null;
        input.value = '';
        modal.classList.remove('hidden');
        input.focus();
    };

    window.prQuickAddZone = function (context) {
        const modal = document.getElementById('pr-add-zone-modal');
        const projectSelect = contextProjectSelect(context);
        const projectId = projectSelect?.value || '';
        const projectInput = document.getElementById('pr-add-zone-project-id');
        if (!modal || !projectId || !projectInput || !quickStoreUrls.zone) return;
        modal._prContextEl = context || null;
        projectInput.value = projectId;
        document.getElementById('pr-add-zone-name').value = '';
        modal.classList.remove('hidden');
        document.getElementById('pr-add-zone-name')?.focus();
    };

    document.getElementById('pr-add-project-cancel')?.addEventListener('click', function () {
        document.getElementById('pr-add-project-modal')?.classList.add('hidden');
    });
    document.getElementById('pr-add-zone-cancel')?.addEventListener('click', function () {
        document.getElementById('pr-add-zone-modal')?.classList.add('hidden');
    });

    document.getElementById('pr-add-project-save')?.addEventListener('click', async function () {
        const modal = document.getElementById('pr-add-project-modal');
        const context = modal?._prContextEl || null;
        const nameInput = document.getElementById('pr-add-project-name');
        const name = (nameInput?.value || '').trim();
        if (!name || !quickStoreUrls.project) return;
        const btn = document.getElementById('pr-add-project-save');
        btn.disabled = true;
        try {
            const formData = new FormData();
            formData.append('name', name);
            const res = await fetch(quickStoreUrls.project, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
                body: formData,
            });
            const payload = await res.json();
            if (!res.ok) return;
            projectSelects().forEach(function (select) {
                appendProjectOption(select, payload);
            });
            const targetSelect = contextProjectSelect(context);
            if (targetSelect) {
                targetSelect.value = String(payload.id);
                targetSelect.dispatchEvent(new Event('change'));
            }
            modal?.classList.add('hidden');
        } finally {
            btn.disabled = false;
        }
    });

    document.getElementById('pr-add-zone-save')?.addEventListener('click', async function () {
        const modal = document.getElementById('pr-add-zone-modal');
        const context = modal?._prContextEl || null;
        const projectId = document.getElementById('pr-add-zone-project-id')?.value || '';
        const name = (document.getElementById('pr-add-zone-name')?.value || '').trim();
        if (!projectId || !name || !quickStoreUrls.zone) return;
        const btn = document.getElementById('pr-add-zone-save');
        btn.disabled = true;
        try {
            const formData = new FormData();
            formData.append('project_id', projectId);
            formData.append('name', name);
            const res = await fetch(quickStoreUrls.zone, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
                body: formData,
            });
            const payload = await res.json();
            if (!res.ok) return;
            zoneSelects().forEach(function (select) {
                appendZoneOption(select, payload);
            });
            const targetSelect = contextZoneSelect(context);
            if (targetSelect) {
                targetSelect.value = String(payload.id);
            }
            modal?.classList.add('hidden');
        } finally {
            btn.disabled = false;
        }
    });
})();
</script>
