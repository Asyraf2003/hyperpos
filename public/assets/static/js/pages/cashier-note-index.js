document.addEventListener('DOMContentLoaded', () => {
    const configNode = document.getElementById('cashier-note-index-config');
    const searchForm = document.getElementById('cashier-note-search-form');
    const searchInput = document.getElementById('cashier-note-search-input');
    const dateInput = document.getElementById('cashier-note-date');
    const paymentStatusInput = document.getElementById('cashier-note-payment-status');
    const workStatusInput = document.getElementById('cashier-note-work-status');
    const tableBody = document.getElementById('cashier-note-table-body');
    const summaryNode = document.getElementById('cashier-note-table-summary');
    const paginationNode = document.getElementById('cashier-note-table-pagination');
    const filterForm = document.getElementById('cashier-note-filter-form');
    const filterDrawer = document.getElementById('cashier-note-filter-drawer');
    const filterBackdrop = document.getElementById('cashier-note-filter-backdrop');
    const openFilterButton = document.getElementById('open-cashier-note-filter');
    const closeFilterButton = document.getElementById('close-cashier-note-filter');
    const resetFilterButton = document.getElementById('reset-cashier-note-filter');

    if (
        !configNode
        || !searchForm
        || !searchInput
        || !dateInput
        || !paymentStatusInput
        || !workStatusInput
        || !tableBody
        || !summaryNode
        || !paginationNode
        || !filterForm
        || !filterDrawer
        || !filterBackdrop
        || !openFilterButton
        || !closeFilterButton
        || !resetFilterButton
    ) {
        return;
    }

    let config = {};

    try {
        config = JSON.parse(configNode.textContent || '{}');
    } catch (_error) {
        config = {};
    }

    const endpoint = typeof config.endpoint === 'string' ? config.endpoint : '';
    const filters = typeof config.filters === 'object' && config.filters !== null
        ? config.filters
        : {};

    const normalize = (value) => String(value ?? '').trim();
    const intOrDefault = (value, fallback) => {
        const parsed = Number.parseInt(String(value ?? ''), 10);
        return Number.isNaN(parsed) || parsed < 1 ? fallback : parsed;
    };

    const stateFromUrl = () => {
        const params = new URLSearchParams(window.location.search);

        return {
            date: normalize(params.get('date') || filters.date),
            search: normalize(params.get('search') || filters.search),
            payment_status: normalize(params.get('payment_status') || filters.payment_status),
            work_status: normalize(params.get('work_status') || filters.work_status),
            page: intOrDefault(params.get('page'), 1),
            per_page: intOrDefault(params.get('per_page'), 10),
        };
    };

    const state = stateFromUrl();

    let searchDebounceTimer = null;
    let requestCounter = 0;

    const fillControlsFromState = () => {
        searchInput.value = state.search;
        dateInput.value = state.date;
        paymentStatusInput.value = state.payment_status;
        workStatusInput.value = state.work_status;
    };

    const syncDrawerState = () => {
        state.date = normalize(dateInput.value);
        state.payment_status = normalize(paymentStatusInput.value);
        state.work_status = normalize(workStatusInput.value);
        state.page = 1;
    };

    const syncSearchState = () => {
        state.search = normalize(searchInput.value);
        state.page = 1;
    };

    const paramsObject = () => {
        const obj = {
            page: String(state.page),
            per_page: String(state.per_page),
        };

        ['date', 'search', 'payment_status', 'work_status'].forEach((key) => {
            const value = normalize(state[key]);
            if (value !== '') {
                obj[key] = value;
            }
        });

        return obj;
    };

    const paramsString = () => new URLSearchParams(paramsObject()).toString();

    const updateUrlState = (replace = false) => {
        const url = new URL(window.location.href);
        url.search = paramsString();

        if (replace) {
            window.history.replaceState(null, '', url);
            return;
        }

        window.history.pushState(null, '', url);
    };

    const drawOpen = (open) => {
        filterDrawer.classList.toggle('d-none', !open);
        filterBackdrop.classList.toggle('d-none', !open);
    };

    const renderLoading = () => {
        tableBody.innerHTML = `
            <tr>
                <td colspan="10" class="text-center text-muted py-4">Sedang memuat riwayat nota...</td>
            </tr>
        `;
        summaryNode.textContent = 'Memuat ringkasan riwayat kasir...';
        paginationNode.innerHTML = '<span class="text-muted small">Memuat pagination...</span>';
    };

    const renderError = () => {
        tableBody.innerHTML = `
            <tr>
                <td colspan="10" class="text-center text-danger py-4">Riwayat nota gagal dimuat.</td>
            </tr>
        `;
        summaryNode.textContent = 'Gagal memuat ringkasan riwayat kasir.';
        paginationNode.innerHTML = '<span class="text-muted small">Pagination belum tersedia.</span>';
    };

    const escapeHtml = (value) => String(value ?? '')
        .replaceAll('&', '&amp;')
        .replaceAll('<', '&lt;')
        .replaceAll('>', '&gt;')
        .replaceAll('"', '&quot;')
        .replaceAll("'", '&#039;');

    const renderAction = (item) => {
        if (typeof item.action_url === 'string' && item.action_url !== '') {
            return `<a href="${escapeHtml(item.action_url)}" class="btn btn-sm btn-outline-primary">${escapeHtml(item.action_label ?? 'Buka')}</a>`;
        }

        return escapeHtml(item.action_label ?? '-');
    };

    const renderItems = (items, summaryLabel, pagination) => {
        if (!Array.isArray(items) || items.length === 0) {
            tableBody.innerHTML = `
                <tr>
                    <td colspan="10" class="text-center text-muted py-4">
                        ${escapeHtml(summaryLabel || 'Belum ada data riwayat kasir.')}
                    </td>
                </tr>
            `;
        } else {
            const page = Number.parseInt(String(pagination?.page ?? 1), 10) || 1;
            const perPage = Number.parseInt(String(pagination?.per_page ?? 10), 10) || 10;

            tableBody.innerHTML = items.map((item, index) => `
                <tr>
                    <td>${((page - 1) * perPage) + index + 1}</td>
                    <td>${escapeHtml(item.transaction_date ?? '-')}</td>
                    <td>${escapeHtml(item.note_number ?? '-')}</td>
                    <td>${escapeHtml(item.customer_name ?? '-')}</td>
                    <td class="text-end">${escapeHtml(item.grand_total_text ?? '-')}</td>
                    <td class="text-end">${escapeHtml(item.total_paid_text ?? '-')}</td>
                    <td class="text-end">${escapeHtml(item.outstanding_text ?? '-')}</td>
                    <td>${escapeHtml(item.payment_status_label ?? '-')}</td>
                    <td>${escapeHtml(item.work_status_label ?? '-')}</td>
                    <td>${renderAction(item)}</td>
                </tr>
            `).join('');
        }

        summaryNode.textContent = summaryLabel || 'Riwayat kasir siap.';
        paginationNode.innerHTML = `
            <span class="text-muted small">
                Halaman ${pagination?.page ?? 1} dari ${pagination?.last_page ?? 1} • Total ${pagination?.total ?? 0} nota
            </span>
        `;
    };

    const loadTable = async (replaceUrl = false) => {
        if (endpoint === '') {
            renderError();
            return;
        }

        const currentRequest = ++requestCounter;
        renderLoading();

        const url = new URL(endpoint, window.location.origin);
        const params = paramsObject();

        Object.keys(params).forEach((key) => {
            url.searchParams.set(key, params[key]);
        });

        try {
            const response = await fetch(url.toString(), {
                headers: {
                    Accept: 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
            });

            const payload = await response.json();

            if (currentRequest !== requestCounter) {
                return;
            }

            if (!response.ok || payload?.success !== true) {
                renderError();
                return;
            }

            const data = payload?.data ?? {};
            const items = Array.isArray(data.items) ? data.items : [];
            const summaryLabel = typeof data?.summary?.label === 'string'
                ? data.summary.label
                : 'Riwayat kasir siap.';
            const pagination = typeof data.pagination === 'object' && data.pagination !== null
                ? data.pagination
                : { page: 1, per_page: 10, total: 0, last_page: 1 };

            renderItems(items, summaryLabel, pagination);
            fillControlsFromState();
            updateUrlState(replaceUrl);
        } catch (_error) {
            if (currentRequest !== requestCounter) {
                return;
            }

            renderError();
        }
    };

    searchForm.addEventListener('submit', (event) => {
        event.preventDefault();
        syncSearchState();
        loadTable();
    });

    searchInput.addEventListener('input', () => {
        clearTimeout(searchDebounceTimer);

        const value = normalize(searchInput.value);

        if (value.length === 0) {
            syncSearchState();
            searchDebounceTimer = window.setTimeout(() => loadTable(), 250);
            return;
        }

        if (value.length < 2) {
            return;
        }

        searchDebounceTimer = window.setTimeout(() => {
            syncSearchState();
            loadTable();
        }, 300);
    });

    openFilterButton.addEventListener('click', () => drawOpen(true));
    closeFilterButton.addEventListener('click', () => drawOpen(false));
    filterBackdrop.addEventListener('click', () => drawOpen(false));

    filterForm.addEventListener('submit', (event) => {
        event.preventDefault();
        syncDrawerState();
        drawOpen(false);
        loadTable();
    });

    resetFilterButton.addEventListener('click', () => {
        filterForm.reset();
        dateInput.value = normalize(filters.date);
        paymentStatusInput.value = '';
        workStatusInput.value = '';
        syncDrawerState();
        drawOpen(false);
        loadTable();
    });

    paginationNode.addEventListener('click', (event) => {
        const link = event.target.closest('[data-page]');
        if (!link || link.parentElement.classList.contains('disabled')) {
            return;
        }

        event.preventDefault();
        state.page = Number(link.dataset.page || 1);
        loadTable();
    });

    window.addEventListener('popstate', () => {
        Object.assign(state, stateFromUrl());
        fillControlsFromState();
        loadTable(true);
    });

    fillControlsFromState();
    loadTable(true);
});
