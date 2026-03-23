(() => {
    const config = window.expenseCategoryTableConfig;
    if (!config) return;

    const state = { q: '', is_active: '', page: 1, per_page: 10, sort_by: 'name', sort_dir: 'asc' };
    const $ = (id) => document.getElementById(id);
    const body = $('expense-category-table-body'), summary = $('expense-category-table-summary'), pagination = $('expense-category-table-pagination');
    const searchForm = $('expense-category-search-form'), searchInput = $('expense-category-search-input');
    const filterForm = $('expense-category-filter-form'), statusInput = $('filter-category-status');
    const drawer = $('expense-category-filter-drawer'), backdrop = $('expense-category-filter-backdrop');
    let debounceId = null;

    const esc = (v) => String(v ?? '').replace(/[&<>"']/g, (m) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' }[m]));
    const url = (template, id) => template.replace('__CATEGORY_ID__', encodeURIComponent(id));
    const params = () => new URLSearchParams(Object.entries(state).filter(([, v]) => v !== '' && v !== null)).toString();

    function toggleDrawer(show) {
        drawer.classList.toggle('d-none', !show);
        backdrop.classList.toggle('d-none', !show);
    }

    function syncSortIndicators() {
        document.querySelectorAll('[data-sort-indicator]').forEach((el) => {
            const key = el.getAttribute('data-sort-indicator');
            el.textContent = key === state.sort_by ? (state.sort_dir === 'asc' ? '↑' : '↓') : '↕';
        });
    }

    function renderAction(row) {
        const editLink = `<a href="${esc(url(config.editUrlTemplate, row.id))}" class="btn btn-sm btn-light-primary">Edit</a>`;
        const actionUrl = row.is_active ? url(config.deactivateUrlTemplate, row.id) : url(config.activateUrlTemplate, row.id);
        const actionText = row.is_active ? 'Nonaktifkan' : 'Aktifkan';
        const actionClass = row.is_active ? 'btn-light-danger' : 'btn-light-success';

        return `
            <div class="d-flex flex-wrap gap-2">
                ${editLink}
                <form action="${esc(actionUrl)}" method="post">
                    <input type="hidden" name="_token" value="${esc(config.csrfToken)}">
                    <input type="hidden" name="_method" value="PATCH">
                    <button type="submit" class="btn btn-sm ${actionClass}">${actionText}</button>
                </form>
            </div>
        `;
    }

    function renderRows(rows, meta) {
        if (!rows.length) {
            body.innerHTML = '<tr><td colspan="6" class="text-center text-muted py-4">Belum ada kategori pengeluaran.</td></tr>';
        } else {
            body.innerHTML = rows.map((row, i) => `
                <tr>
                    <td>${(meta.page - 1) * meta.per_page + i + 1}</td>
                    <td>${esc(row.code)}</td>
                    <td>${esc(row.name)}</td>
                    <td>${esc(row.description ?? '-')}</td>
                    <td><span class="badge ${esc(row.status_badge_class)}">${esc(row.status_label)}</span></td>
                    <td>${renderAction(row)}</td>
                </tr>
            `).join('');
        }

        summary.textContent = `Total: ${meta.total} data`;
        pagination.innerHTML = buildPagination(meta);
        syncSortIndicators();
        bindPagination();
    }

    function buildPagination(meta) {
        if (meta.last_page <= 1) return '';
        let html = '<div class="btn-group">';
        html += `<button type="button" class="btn btn-sm btn-light-secondary" data-page="${Math.max(1, meta.page - 1)}" ${meta.page === 1 ? 'disabled' : ''}>Prev</button>`;
        for (let p = 1; p <= meta.last_page; p += 1) {
            html += `<button type="button" class="btn btn-sm ${p === meta.page ? 'btn-primary' : 'btn-light-secondary'}" data-page="${p}">${p}</button>`;
        }
        html += `<button type="button" class="btn btn-sm btn-light-secondary" data-page="${Math.min(meta.last_page, meta.page + 1)}" ${meta.page === meta.last_page ? 'disabled' : ''}>Next</button>`;
        html += '</div>';
        return html;
    }

    function bindPagination() {
        pagination.querySelectorAll('[data-page]').forEach((btn) => {
            btn.addEventListener('click', () => {
                state.page = Number(btn.getAttribute('data-page') || '1');
                fetchTable();
            });
        });
    }

    async function fetchTable() {
        body.innerHTML = '<tr><td colspan="6" class="text-center text-muted py-4">Sedang memuat data...</td></tr>';
        try {
            const response = await fetch(`${config.endpoint}?${params()}`, { headers: { Accept: 'application/json' } });
            const payload = await response.json();
            if (!response.ok || payload.success !== true) throw new Error('Gagal memuat data');
            renderRows(payload.data.rows, payload.data.meta);
        } catch (_error) {
            body.innerHTML = '<tr><td colspan="6" class="text-center text-danger py-4">Gagal memuat data kategori.</td></tr>';
            summary.textContent = 'Total: -';
            pagination.innerHTML = '';
        }
    }

    document.querySelectorAll('[data-sort-by]').forEach((btn) => {
        btn.addEventListener('click', () => {
            const sortBy = btn.getAttribute('data-sort-by') || 'name';
            state.sort_dir = state.sort_by === sortBy && state.sort_dir === 'asc' ? 'desc' : 'asc';
            state.sort_by = sortBy;
            state.page = 1;
            fetchTable();
        });
    });

    searchForm?.addEventListener('submit', (e) => { e.preventDefault(); state.q = searchInput.value.trim(); state.page = 1; fetchTable(); });
    searchInput?.addEventListener('input', () => {
        clearTimeout(debounceId);
        debounceId = setTimeout(() => { state.q = searchInput.value.trim(); state.page = 1; fetchTable(); }, 300);
    });

    $('open-expense-category-filter')?.addEventListener('click', () => toggleDrawer(true));
    $('close-expense-category-filter')?.addEventListener('click', () => toggleDrawer(false));
    backdrop?.addEventListener('click', () => toggleDrawer(false));

    filterForm?.addEventListener('submit', (e) => {
        e.preventDefault();
        state.is_active = statusInput.value;
        state.page = 1;
        toggleDrawer(false);
        fetchTable();
    });

    $('reset-expense-category-filter')?.addEventListener('click', () => {
        filterForm.reset();
        state.is_active = '';
        state.page = 1;
        toggleDrawer(false);
        fetchTable();
    });

    fetchTable();
})();
