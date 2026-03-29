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

    const filters = typeof config.filters === 'object' && config.filters !== null
        ? config.filters
        : {};

    const normalize = (value) => String(value ?? '').trim();

    const state = {
        date: normalize(filters.date),
        search: normalize(filters.search),
        payment_status: normalize(filters.payment_status),
        work_status: normalize(filters.work_status),
    };

    const fillControlsFromState = () => {
        searchInput.value = state.search;
        dateInput.value = state.date;
        paymentStatusInput.value = state.payment_status;
        workStatusInput.value = state.work_status;
    };

    const renderPlaceholder = () => {
        tableBody.innerHTML = `
            <tr>
                <td colspan="10" class="text-center text-muted py-4">
                    Data riwayat kasir belum dihubungkan. Filter sudah siap dipakai untuk query string.
                </td>
            </tr>
        `;
    };

    const buildSummary = () => {
        const parts = [];

        parts.push(`Tanggal acuan: ${state.date || '-'}`);
        parts.push(`Pembayaran: ${state.payment_status || 'semua'}`);
        parts.push(`Pengerjaan: ${state.work_status || 'semua'}`);

        if (state.search !== '') {
            parts.push(`Pencarian: "${state.search}"`);
        }

        return parts.join(' • ');
    };

    const renderSummary = () => {
        summaryNode.textContent = buildSummary();
        paginationNode.innerHTML = `
            <span class="text-muted small">
                Pagination akan aktif setelah endpoint riwayat kasir dihubungkan.
            </span>
        `;
    };

    const syncStateFromControls = () => {
        state.date = normalize(dateInput.value);
        state.search = normalize(searchInput.value);
        state.payment_status = normalize(paymentStatusInput.value);
        state.work_status = normalize(workStatusInput.value);
    };

    const applyQueryString = () => {
        const url = new URL(window.location.href);

        ['date', 'search', 'payment_status', 'work_status'].forEach((key) => {
            const value = normalize(state[key]);

            if (value === '') {
                url.searchParams.delete(key);
                return;
            }

            url.searchParams.set(key, value);
        });

        window.location.assign(url.toString());
    };

    let searchDebounceTimer = null;

    const queueSearchApply = () => {
        window.clearTimeout(searchDebounceTimer);
        searchDebounceTimer = window.setTimeout(() => {
            syncStateFromControls();
            applyQueryString();
        }, 400);
    };

    searchInput.addEventListener('keydown', (event) => {
        if (event.key !== 'Enter') {
            return;
        }

        event.preventDefault();
        syncStateFromControls();
        applyQueryString();
    });

    searchInput.addEventListener('input', () => {
        queueSearchApply();
    });

    [dateInput, paymentStatusInput, workStatusInput].forEach((input) => {
        input.addEventListener('change', () => {
            syncStateFromControls();
            applyQueryString();
        });
    });

    fillControlsFromState();
    renderPlaceholder();
    renderSummary();
});
