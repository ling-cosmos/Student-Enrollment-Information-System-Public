// assets/js/main.js
// Client-side helpers and form validation

document.addEventListener('DOMContentLoaded', function () {

    // ── Responsive primary navigation ──
    const navToggle = document.querySelector('.mobile-nav-toggle');
    const sidebar = document.getElementById('seisSidebar');

    if (navToggle && sidebar) {
        const setNavigationOpen = function (isOpen) {
            sidebar.classList.toggle('is-open', isOpen);
            navToggle.setAttribute('aria-expanded', String(isOpen));
            navToggle.setAttribute('aria-label', isOpen ? 'Close navigation' : 'Open navigation');
        };

        navToggle.addEventListener('click', function () {
            setNavigationOpen(!sidebar.classList.contains('is-open'));
        });

        sidebar.querySelectorAll('a').forEach(function (link) {
            link.addEventListener('click', function () {
                setNavigationOpen(false);
            });
        });

        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape' && sidebar.classList.contains('is-open')) {
                setNavigationOpen(false);
                navToggle.focus();
            }
        });

        document.addEventListener('click', function (event) {
            if (!sidebar.contains(event.target) && !navToggle.contains(event.target)) {
                setNavigationOpen(false);
            }
        });
    }

    // ── Desktop sidebar collapse and grouped navigation ──
    const desktopSidebarToggle = document.querySelector('.desktop-sidebar-toggle');
    if (desktopSidebarToggle && sidebar) {
        const setSidebarCollapsed = function (collapsed) {
            document.body.classList.toggle('sidebar-collapsed', collapsed);
            desktopSidebarToggle.setAttribute('aria-expanded', String(!collapsed));
            desktopSidebarToggle.setAttribute('aria-label', collapsed ? 'Expand sidebar' : 'Collapse sidebar');
        };
        setSidebarCollapsed(localStorage.getItem('seis-sidebar-collapsed') === 'true');
        desktopSidebarToggle.addEventListener('click', function () {
            const collapsed = !document.body.classList.contains('sidebar-collapsed');
            setSidebarCollapsed(collapsed);
            localStorage.setItem('seis-sidebar-collapsed', String(collapsed));
        });
        sidebar.querySelectorAll('.nav-link').forEach(function (link) {
            link.title = link.textContent.trim();
        });
    }

    document.querySelectorAll('[data-sidebar-group]').forEach(function (toggle) {
        const group = toggle.getAttribute('data-sidebar-group');
        const panel = document.querySelector('[data-sidebar-panel="' + group + '"]');
        if (!panel) return;
        toggle.addEventListener('click', function () {
            const open = toggle.getAttribute('aria-expanded') !== 'false';
            toggle.setAttribute('aria-expanded', String(!open));
            panel.hidden = open;
        });
    });

    // ── Styled confirmation dialog for sensitive actions ──
    const confirmDialog = document.createElement('dialog');
    confirmDialog.className = 'seis-confirm-dialog';
    confirmDialog.innerHTML = '<div class="confirm-dialog-body"><h2>Confirm action</h2><p data-confirm-message></p><div class="confirm-dialog-actions"><button type="button" class="btn-seis-outline" data-confirm-cancel>Cancel</button><button type="button" class="btn-seis-danger" data-confirm-accept>Confirm</button></div></div>';
    document.body.appendChild(confirmDialog);
    let pendingConfirm = null;
    document.querySelectorAll('[data-confirm]').forEach(function (el) {
        el.addEventListener('click', function (e) {
            if (el.dataset.confirmed === 'true') return;
            e.preventDefault();
            pendingConfirm = el;
            confirmDialog.querySelector('[data-confirm-message]').textContent = el.getAttribute('data-confirm') || 'Are you sure?';
            confirmDialog.showModal();
        });
    });
    confirmDialog.querySelector('[data-confirm-cancel]').addEventListener('click', function () { confirmDialog.close(); pendingConfirm = null; });
    confirmDialog.querySelector('[data-confirm-accept]').addEventListener('click', function () {
        if (!pendingConfirm) return;
        const target = pendingConfirm;
        pendingConfirm = null;
        confirmDialog.close();
        target.dataset.confirmed = 'true';
        target.click();
        queueMicrotask(function () { delete target.dataset.confirmed; });
    });

    // ── Auto-dismiss alerts after 4 seconds ──
    document.querySelectorAll('.alert-dismissible').forEach(function (alert) {
        setTimeout(function () {
            const bsAlert = bootstrap.Alert.getOrCreateInstance(alert);
            bsAlert.close();
        }, 4000);
    });

    // ── Live table search filter ──
    const searchInput = document.getElementById('tableSearch');
    if (searchInput) {
        searchInput.addEventListener('keyup', function () {
            const term  = this.value.toLowerCase();
            const rows  = document.querySelectorAll('tbody tr');
            rows.forEach(function (row) {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(term) ? '' : 'none';
            });
        });
    }

    // ── User table search, filters, sorting, pagination, and bulk selection ──
    const userTable = document.querySelector('[data-table-tool="users"]');
    if (userTable) {
        const body = userTable.tBodies[0];
        const rows = Array.from(body.querySelectorAll('tr[data-row]'));
        const emptyRow = body.querySelector('.table-empty-filter');
        const search = document.querySelector('[data-user-search]');
        const role = document.querySelector('[data-user-role]');
        const status = document.querySelector('[data-user-status]');
        const size = document.querySelector('[data-page-size]');
        const summary = document.querySelector('[data-table-summary]');
        const pagination = document.querySelector('[data-table-pagination]');
        const selectAll = userTable.querySelector('[data-select-all]');
        const mobileSelectAll = document.querySelector('[data-mobile-select-all]');
        const mobileSort = document.querySelector('[data-mobile-sort]');
        let page = 1;
        let sortKey = 'name';
        let sortDirection = 1;

        const updateSelectionState = function () {
            const count = rows.filter(function (row) { const box = row.querySelector('[data-row-select]'); return box && box.checked; }).length;
            const label = document.querySelector('[data-selected-count]');
            if (label) label.textContent = String(count);
            const visibleBoxes = rows.filter(function (row) { return !row.hidden; }).map(function (row) { return row.querySelector('[data-row-select]:not(:disabled)'); }).filter(Boolean);
            const visibleChecked = visibleBoxes.filter(function (box) { return box.checked; }).length;
            [selectAll, mobileSelectAll].filter(Boolean).forEach(function (control) {
                control.checked = visibleBoxes.length > 0 && visibleChecked === visibleBoxes.length;
                control.indeterminate = visibleChecked > 0 && visibleChecked < visibleBoxes.length;
            });
        };

        const render = function () {
            const term = (search.value || '').trim().toLowerCase();
            const filtered = rows.filter(function (row) {
                return (!term || row.dataset.search.includes(term)) && (!role.value || row.dataset.role === role.value) && (!status.value || row.dataset.status === status.value);
            }).sort(function (a, b) {
                return (a.dataset['sort' + sortKey.charAt(0).toUpperCase() + sortKey.slice(1)] || '').localeCompare(b.dataset['sort' + sortKey.charAt(0).toUpperCase() + sortKey.slice(1)] || '') * sortDirection;
            });
            filtered.forEach(function (row) { body.insertBefore(row, emptyRow); });
            const perPage = Number(size.value) || 10;
            const totalPages = Math.max(1, Math.ceil(filtered.length / perPage));
            page = Math.min(page, totalPages);
            rows.forEach(function (row) { row.hidden = true; });
            filtered.slice((page - 1) * perPage, page * perPage).forEach(function (row) { row.hidden = false; });
            emptyRow.hidden = filtered.length !== 0;
            summary.textContent = filtered.length ? 'Showing ' + (((page - 1) * perPage) + 1) + '–' + Math.min(page * perPage, filtered.length) + ' of ' + filtered.length + ' users' : 'No matching users';
            pagination.replaceChildren();
            for (let index = 1; index <= totalPages; index += 1) {
                const button = document.createElement('button');
                button.type = 'button';
                button.textContent = String(index);
                button.setAttribute('aria-label', 'Page ' + index);
                if (index === page) button.setAttribute('aria-current', 'page');
                button.addEventListener('click', function () { page = index; render(); });
                pagination.appendChild(button);
            }
            updateSelectionState();
        };
        [search, role, status, size].forEach(function (control) { control.addEventListener(control === search ? 'input' : 'change', function () { page = 1; render(); }); });
        userTable.querySelectorAll('[data-sort-key]').forEach(function (button) {
            button.addEventListener('click', function () {
                const key = button.getAttribute('data-sort-key');
                sortDirection = key === sortKey ? sortDirection * -1 : 1;
                sortKey = key;
                render();
            });
        });
        const setVisibleSelection = function (checked) {
            rows.filter(function (row) { return !row.hidden; }).forEach(function (row) { const box = row.querySelector('[data-row-select]:not(:disabled)'); if (box) box.checked = checked; });
            updateSelectionState();
        };
        selectAll.addEventListener('change', function (event) { setVisibleSelection(event.target.checked); });
        if (mobileSelectAll) mobileSelectAll.addEventListener('change', function (event) { setVisibleSelection(event.target.checked); });
        if (mobileSort) mobileSort.addEventListener('change', function () { const parts = mobileSort.value.split(':'); sortKey = parts[0]; sortDirection = Number(parts[1]) || 1; render(); });
        rows.forEach(function (row) { const box = row.querySelector('[data-row-select]'); if (box) box.addEventListener('change', updateSelectionState); });
        render();
    }

    document.addEventListener('click', function (event) {
        document.querySelectorAll('.row-actions[open]').forEach(function (details) {
            if (!details.contains(event.target)) details.removeAttribute('open');
        });
    });

    // ── Responsive labels for every standard SEIS table ──
    document.querySelectorAll('.seis-table').forEach(function (table) {
        table.classList.add('responsive-table');
        const labels = Array.from(table.querySelectorAll('thead th')).map(function (header) { return header.textContent.trim(); });
        table.querySelectorAll('tbody tr').forEach(function (row) {
            Array.from(row.children).forEach(function (cell, index) {
                if (!cell.hasAttribute('colspan') && !cell.hasAttribute('data-label')) cell.setAttribute('data-label', labels[index] || 'Details');
            });
        });
        table.querySelectorAll('tfoot tr').forEach(function (row) {
            let headerIndex = 0;
            Array.from(row.children).forEach(function (cell) {
                const span = Number(cell.getAttribute('colspan')) || 1;
                if (!cell.hasAttribute('aria-hidden')) {
                    cell.setAttribute('data-label', headerIndex === 0 && span > 1 ? 'Summary' : (labels[headerIndex] || 'Details'));
                }
                headerIndex += span;
            });
        });
    });

    // ── Bootstrap form validation ──
    document.querySelectorAll('form.needs-validation').forEach(function (form) {
        form.addEventListener('submit', function (event) {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            form.classList.add('was-validated');
        });
    });

    document.querySelectorAll('form[data-loading-form]').forEach(function (form) {
        form.addEventListener('submit', function () {
            if (!form.checkValidity()) return;
            const button = form.querySelector('button[type="submit"]');
            if (button) {
                button.disabled = true;
                button.classList.add('is-loading');
                button.insertAdjacentHTML('afterbegin', '<span class="spinner-border spinner-border-sm" aria-hidden="true"></span>');
            }
        });
    });

    window.addEventListener('pageshow', function () {
        document.querySelectorAll('[data-confirmed]').forEach(function (element) { delete element.dataset.confirmed; });
        document.querySelectorAll('button.is-loading').forEach(function (button) {
            button.disabled = false;
            button.classList.remove('is-loading');
            const spinner = button.querySelector('.spinner-border');
            if (spinner) spinner.remove();
        });
    });
});
