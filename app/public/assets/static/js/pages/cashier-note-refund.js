(() => {
    const form = document.getElementById('note-refund-form');

    if (!form) {
        return;
    }

    const rowSelectors = Array.from(document.querySelectorAll('.js-refund-row-selector'));
    const hiddenInputsContainer = document.getElementById('selected-refund-row-inputs');
    const amountInput = document.getElementById('refund_amount_rupiah');
    const selectedRowCount = document.getElementById('selected-refund-row-count');
    const selectedRefundableTotal = document.getElementById('selected-refund-refundable-total');
    const selectedRefundTotal = document.getElementById('selected-refund-total');
    const submitButton = document.getElementById('note-refund-submit');

    if (
        rowSelectors.length === 0
        || !hiddenInputsContainer
        || !amountInput
        || !selectedRowCount
        || !selectedRefundableTotal
        || !selectedRefundTotal
        || !submitButton
    ) {
        return;
    }

    function parseNumber(value) {
        if (typeof value !== 'string') {
            return 0;
        }

        const cleaned = value.replace(/[^0-9]/g, '');
        return cleaned === '' ? 0 : Number.parseInt(cleaned, 10);
    }

    function formatNumber(value) {
        return new Intl.NumberFormat('id-ID').format(Number.isFinite(value) ? value : 0);
    }

    function selectedRows() {
        return rowSelectors.filter((input) => input.checked);
    }

    function selectedRefundableAmount() {
        return selectedRows().reduce((total, input) => {
            const raw = input.getAttribute('data-refundable-rupiah') || '0';
            return total + parseNumber(raw);
        }, 0);
    }

    function amountToRefund() {
        const selectedRefundable = selectedRefundableAmount();
        const typedAmount = parseNumber(amountInput.value);
        return Math.min(typedAmount, selectedRefundable);
    }

    function syncHiddenSelectedRows() {
        hiddenInputsContainer.innerHTML = '';

        selectedRows().forEach((input) => {
            const rowId = (input.getAttribute('data-row-id') || '').trim();

            if (rowId === '') {
                return;
            }

            const hidden = document.createElement('input');
            hidden.type = 'hidden';
            hidden.name = 'selected_row_ids[]';
            hidden.value = rowId;
            hiddenInputsContainer.appendChild(hidden);
        });
    }

    function updateSubmitState(selectedCount, refundNow) {
        submitButton.disabled = selectedCount <= 0 || refundNow <= 0;
    }

    function update() {
        const selectedCount = selectedRows().length;
        const refundable = selectedRefundableAmount();
        const refundNow = amountToRefund();

        syncHiddenSelectedRows();

        selectedRowCount.textContent = formatNumber(selectedCount);
        selectedRefundableTotal.textContent = formatNumber(refundable);
        selectedRefundTotal.textContent = formatNumber(refundNow);

        updateSubmitState(selectedCount, refundNow);
    }

    rowSelectors.forEach((input) => {
        input.addEventListener('change', update);
    });

    form.addEventListener('input', update);
    form.addEventListener('change', update);

    update();
})();
