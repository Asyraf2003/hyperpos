document.addEventListener('DOMContentLoaded', () => {
    const configNode = document.getElementById('admin-note-index-config');
    const dateFromInput = document.getElementById('admin-note-date-from');
    const dateToInput = document.getElementById('admin-note-date-to');
    const searchInput = document.getElementById('admin-note-search-input');
    const paymentStatusInput = document.getElementById('admin-note-payment-status');
    const editabilityInput = document.getElementById('admin-note-editability');
    const workSummaryInput = document.getElementById('admin-note-work-summary');
    const summaryNode = document.getElementById('admin-note-table-summary');
    const paginationNode = document.getElementById('admin-note-table-pagination');

    if (!configNode || !dateFromInput || !dateToInput || !searchInput || !paymentStatusInput || !editabilityInput || !workSummaryInput || !summaryNode || !paginationNode) {
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
        date_from: normalize(filters.date_from),
        date_to: normalize(filters.date_to),
        search: normalize(filters.search),
        payment_status: normalize(filters.payment_status),
        editability: normalize(filters.editability),
        work_summary: normalize(filters.work_summary),
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

    const buildSummary = () => {
        const parts = [];

        parts.push(`Rentang: ${state.date_from || '-'} s.d. ${state.date_to || '-'}`);
        parts.push(`Pembayaran: ${state.payment_status || 'semua'}`);
        parts.push(`Mode edit: ${state.editability || 'semua'}`);
        parts.push(`Ringkasan kerja: ${state.work_summary || 'semua'}`);

        if (state.search !== '') {
            parts.push(`Pencarian: "${state.search}"`);
        }

        return parts.join(' • ');
    };

    const renderSummary = () => {
        summaryNode.textContent = buildSummary();
        paginationNode.innerHTML = `
            <span class="text-muted small">
                Pagination admin history akan aktif setelah endpoint data dihubungkan.
            </span>
        `;
    };

    let searchDebounceTimer = null;

    const queueReload = () => {
        window.clearTimeout(searchDebounceTimer);
        searchDebounceTimer = window.setTimeout(() => {
            syncStateFromControls();
            updateUrlState();
            renderSummary();
        }, 400);
    };

    searchInput.addEventListener('keydown', (event) => {
        if (event.key !== 'Enter') {
            return;
        }

        event.preventDefault();
        syncStateFromControls();
        updateUrlState();
        renderSummary();
    });

    searchInput.addEventListener('input', () => {
        queueReload();
    });

    [dateFromInput, dateToInput, paymentStatusInput, editabilityInput, workSummaryInput].forEach((input) => {
        input.addEventListener('change', () => {
            syncStateFromControls();
            updateUrlState();
            renderSummary();
        });
    });

    fillControlsFromState();
    renderSummary();
});
