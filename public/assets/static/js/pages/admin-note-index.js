document.addEventListener('DOMContentLoaded', () => {
    const configNode = document.getElementById('admin-note-index-config');
    const dateFromInput = document.getElementById('admin-note-date-from');
    const dateToInput = document.getElementById('admin-note-date-to');
    const searchInput = document.getElementById('admin-note-search-input');
    const paymentStatusInput = document.getElementById('admin-note-payment-status');
    const editabilityInput = document.getElementById('admin-note-editability');
    const workSummaryInput = document.getElementById('admin-note-work-summary');
    const tableBody = document.getElementById('admin-note-table-body');
    const summaryNode = document.getElementById('admin-note-table-summary');
    const paginationNode = document.getElementById('admin-note-table-pagination');

    if (
        !configNode
        || !dateFromInput
        || !dateToInput
        || !searchInput
        || !paymentStatusInput
        || !editabilityInput
        || !workSummaryInput
        || !tableBody
        || !summaryNode
        || !paginationNode
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

    const state = {
        date_from: normalize(filters.date_from),
        date_to: normalize(filters.date_to),
        search: normalize(filters.search),
        payment_status: normalize(filters.payment_status),
        editability: normalize(filters.editability),
        work_summary: normalize(filters.work_summary),
        page: 1,
        per_page: 10,
    };

    const fillControlsFromState = () => {
        dateFromInput.value = state.date_from;
        dateToInput.value = state.date_to;
        searchInput.value = state.search;
        paymentStatusInput.value = state.payment_status;
        editabilityInput.value = state.editability;
        workSummaryInput.value = state.work_summary;
    };

    const syncStateFromControls = () => {
        state.date_from = normalize(dateFromInput.value);
        state.date_to = normalize(dateToInput.value);
        state.search = normalize(searchInput.value);
        state.payment_status = normalize(paymentStatusInput.value);
        state.editability = normalize(editabilityInput.value);
        state.work_summary = normalize(workSummaryInput.value);
        state.page = 1;
    };

    const updateUrlState = () => {
        const url = new URL(window.location.href);

        ['date_from', 'date_to', 'search', 'payment_status', 'editability', 'work_summary'].forEach((key) => {
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
                <td colspan="11" class="text-center text-muted py-4">Sedang memuat riwayat nota admin...</td>
            </tr>
        `;
        summaryNode.textContent = 'Memuat ringkasan riwayat admin...';
        paginationNode.innerHTML = '<span class="text-muted small">Memuat pagination...</span>';
    };

    const renderError = () => {
        tableBody.innerHTML = `
            <tr>
                <td colspan="11" class="text-center text-danger py-4">Riwayat nota admin gagal dimuat.</td>
            </tr>
        `;
        summaryNode.textContent = 'Gagal memuat ringkasan riwayat admin.';
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
                    <td colspan="11" class="text-center text-muted py-4">
                        ${escapeHtml(summaryLabel || 'Belum ada data riwayat admin.')}
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
                    <td>${escapeHtml(item.editability_label ?? '-')}</td>
                    <td>${renderAction(item)}</td>
                </tr>
            `).join('');
        }

        summaryNode.textContent = summaryLabel || 'Riwayat admin siap.';
        paginationNode.innerHTML = `
            <span class="text-muted small">
                Halaman ${pagination?.page ?? 1} dari ${pagination?.last_page ?? 1} • Total ${pagination?.total ?? 0} nota
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

        ['date_from', 'date_to', 'search', 'payment_status', 'editability', 'work_summary', 'page', 'per_page'].forEach((key) => {
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
                : 'Riwayat admin siap.';
            const pagination = typeof data.pagination === 'object' && data.pagination !== null
                ? data.pagination
                : { page: 1, per_page: 10, total: 0, last_page: 1 };

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

    [dateFromInput, dateToInput, paymentStatusInput, editabilityInput, workSummaryInput].forEach((input) => {
        input.addEventListener('change', () => {
            syncStateFromControls();
            updateUrlState();
            loadTable();
        });
    });

    fillControlsFromState();
    loadTable();
});
