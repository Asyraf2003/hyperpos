(() => {
    const config = window.expenseTableConfig;
    if (!config) return;

    const state = { q: '', page: 1, per_page: 10, sort_by: 'expense_date', sort_dir: 'desc', category_id: '', date_from: '', date_to: '' };
    const $ = (id) => document.getElementById(id);
    const body = $('expense-table-body'), summary = $('expense-table-summary'), pagination = $('expense-table-pagination');
    const searchForm = $('expense-search-form'), searchInput = $('expense-search-input');
    const filterForm = $('expense-filter-form'), categoryInput = $('filter-category-id'), dateFromInput = $('filter-date-from'), dateToInput = $('filter-date-to');
    const drawer = $('expense-filter-drawer'), backdrop = $('expense-filter-backdrop');
    const openBtn = $('open-expense-filter'), closeBtn = $('close-expense-filter'), resetBtn = $('reset-expense-filter');
    let debounceId = null;

    const esc = (v) => String(v ?? '').replace(/[&<>"']/g, (m) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' }[m]));
    const rupiah = (v) => new Intl.NumberFormat('id-ID').format(Number(v || 0));
    const params = () => new URLSearchParams(Object.entries(state).filter(([, v]) => v !== '' && v !== null && v !== undefined)).toString();

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

    function renderRows(rows, meta) {
        if (!rows.length) {
            body.innerHTML = '<tr><td colspan="7" class="text-center text-muted py-4">Belum ada data pengeluaran.</td></tr>';
        } else {
            body.innerHTML = rows.map((row, i) => `
                <tr>
                    <td>${(meta.page - 1) * meta.per_page + i + 1}</td>
                    <td>${esc(row.expense_date)}</td>
                    <td>${esc(row.category_name)} (${esc(row.category_code)})</td>
                    <td>${esc(row.description)}</td>
                    <td class="text-end">Rp ${rupiah(row.amount_rupiah)}</td>
                    <td>${esc(row.payment_method)}</td>
                    <td><span class="badge ${esc(row.status_badge_class)}">${esc(row.status_label)}</span></td>
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
        body.innerHTML = '<tr><td colspan="7" class="text-center text-muted py-4">Sedang memuat data...</td></tr>';
        try {
            const response = await fetch(`${config.endpoint}?${params()}`, { headers: { Accept: 'application/json' } });
            const payload = await response.json();
            if (!response.ok || payload.success !== true) throw new Error('Gagal memuat data');
            renderRows(payload.data.rows, payload.data.meta);
        } catch (_error) {
            body.innerHTML = '<tr><td colspan="7" class="text-center text-danger py-4">Gagal memuat data pengeluaran.</td></tr>';
            summary.textContent = 'Total: -';
            pagination.innerHTML = '';
        }
    }

    document.querySelectorAll('[data-sort-by]').forEach((btn) => {
        btn.addEventListener('click', () => {
            const sortBy = btn.getAttribute('data-sort-by') || 'expense_date';
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

    openBtn?.addEventListener('click', () => toggleDrawer(true));
    closeBtn?.addEventListener('click', () => toggleDrawer(false));
    backdrop?.addEventListener('click', () => toggleDrawer(false));

    filterForm?.addEventListener('submit', (e) => {
        e.preventDefault();
        state.category_id = categoryInput.value;
        state.date_from = dateFromInput.value;
        state.date_to = dateToInput.value;
        state.page = 1;
        toggleDrawer(false);
        fetchTable();
    });

    resetBtn?.addEventListener('click', () => {
        filterForm.reset();
        state.category_id = ''; state.date_from = ''; state.date_to = ''; state.page = 1;
        toggleDrawer(false);
        fetchTable();
    });

    fetchTable();
})();
