document.addEventListener('DOMContentLoaded', () => {
    const configNode = document.getElementById('cashier-note-index-config');
    const searchInput = document.getElementById('cashier-note-search-input');
    const dateInput = document.getElementById('cashier-note-date');
    const paymentStatusInput = document.getElementById('cashier-note-payment-status');
    const workStatusInput = document.getElementById('cashier-note-work-status');
    const tableBody = document.getElementById('cashier-note-table-body');
    const summaryNode = document.getElementById('cashier-note-table-summary');
    const paginationNode = document.getElementById('cashier-note-table-pagination');

    if (!configNode || !searchInput || !dateInput || !paymentStatusInput || !workStatusInput || !tableBody || !summaryNode || !paginationNode) {
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

    const state = {
        date: normalize(filters.date),
        search: normalize(filters.search),
        payment_status: normalize(filters.payment_status),
        work_status: normalize(filters.work_status),
        page: 1,
        per_page: 10,
    };

    const fillControlsFromState = () => {
        searchInput.value = state.search;
        dateInput.value = state.date;
        paymentStatusInput.value = state.payment_status;
        workStatusInput.value = state.work_status;
    };

    const syncStateFromControls = () => {
        state.date = normalize(dateInput.value);
        state.search = normalize(searchInput.value);
        state.payment_status = normalize(paymentStatusInput.value);
        state.work_status = normalize(workStatusInput.value);
    };

    const updateUrlState = () => {
        const url = new URL(window.location.href);

        ['date', 'search', 'payment_status', 'work_status'].forEach((key) => {
            const value = normalize(state[key]);

            if (value === '') {
                url.searchParams.delete(key);
                return;
            }

            url.searchParams.set(key, value);
        });

        window.history.replaceState({}, '', url.toString());
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
            tableBody.innerHTML = items.map((item, index) => `
                <tr>
                    <td>${index + 1}</td>
                    <td>${escapeHtml(item.transaction_date ?? '-')}</td>
                    <td>${escapeHtml(item.note_number ?? '-')}</td>
                    <td>${escapeHtml(item.customer_name ?? '-')}</td>
                    <td class="text-end">${escapeHtml(item.grand_total_text ?? '-')}</td>
                    <td class="text-end">${escapeHtml(item.total_paid_text ?? '-')}</td>
                    <td class="text-end">${escapeHtml(item.outstanding_text ?? '-')}</td>
                    <td>${escapeHtml(item.payment_status_label ?? '-')}</td>
                    <td>${escapeHtml(item.work_status_label ?? '-')}</td>
                    <td>${escapeHtml(item.action_label ?? '-')}</td>
                </tr>
            `).join('');
        }

        summaryNode.textContent = summaryLabel || 'Riwayat kasir siap.';
        paginationNode.innerHTML = `
            <span class="text-muted small">
                Halaman ${pagination?.page ?? 1} dari ${pagination?.last_page ?? 1}
            </span>
        `;
    };

    const loadTable = async () => {
        if (endpoint === '') {
            renderError();
            return;
        }

        renderLoading();

        const url = new URL(endpoint, window.location.origin);

        ['date', 'search', 'payment_status', 'work_status', 'page', 'per_page'].forEach((key) => {
            const value = normalize(state[key]);
            if (value !== '') {
                url.searchParams.set(key, value);
            }
        });

        try {
            const response = await fetch(url.toString(), {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
            });

            if (!response.ok) {
                throw new Error(`HTTP ${response.status}`);
            }

            const payload = await response.json();
            const data = payload?.data ?? {};
            const items = Array.isArray(data.items) ? data.items : [];
            const summaryLabel = typeof data?.summary?.label === 'string'
                ? data.summary.label
                : 'Riwayat kasir siap.';
            const pagination = typeof data.pagination === 'object' && data.pagination !== null
                ? data.pagination
                : { page: 1, last_page: 1 };

            renderItems(items, summaryLabel, pagination);
        } catch (_error) {
            renderError();
        }
    };

    let searchDebounceTimer = null;

    const queueReload = () => {
        window.clearTimeout(searchDebounceTimer);
        searchDebounceTimer = window.setTimeout(() => {
            syncStateFromControls();
            updateUrlState();
            loadTable();
        }, 400);
    };

    searchInput.addEventListener('keydown', (event) => {
        if (event.key !== 'Enter') {
            return;
        }

        event.preventDefault();
        syncStateFromControls();
        updateUrlState();
        loadTable();
    });

    searchInput.addEventListener('input', () => {
        queueReload();
    });

    [dateInput, paymentStatusInput, workStatusInput].forEach((input) => {
        input.addEventListener('change', () => {
            syncStateFromControls();
            updateUrlState();
            loadTable();
        });
    });

    fillControlsFromState();
    loadTable();
});
