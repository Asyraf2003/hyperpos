(() => {
    const form = document.getElementById('note-payment-form');

    if (!form) {
        return;
    }

    const rowSelectors = Array.from(document.querySelectorAll('.js-payment-row-selector'));
    const hiddenInputsContainer = document.getElementById('selected-payment-row-inputs');
    const amountPaidInput = document.getElementById('amount-paid');
    const amountReceivedInput = document.getElementById('amount-received');
    const paymentMethod = document.getElementById('payment-method');
    const selectedRowCount = document.getElementById('selected-payment-row-count');
    const selectedOutstandingTotal = document.getElementById('selected-payment-outstanding-total');
    const selectedPaymentTotal = document.getElementById('selected-payment-total');
    const paymentRemainingText = document.getElementById('payment-remaining-text');
    const paymentChangeText = document.getElementById('payment-change-text');
    const submitButton = document.getElementById('note-payment-submit');

    if (
        rowSelectors.length === 0
        || !hiddenInputsContainer
        || !selectedRowCount
        || !selectedOutstandingTotal
        || !selectedPaymentTotal
        || !paymentRemainingText
        || !paymentChangeText
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

    function selectedOutstandingAmount() {
        return selectedRows().reduce((total, input) => {
            const raw = input.getAttribute('data-outstanding-rupiah') || '0';
            return total + parseNumber(raw);
        }, 0);
    }

    function amountToPay() {
        const selectedOutstanding = selectedOutstandingAmount();
        const typedAmount = parseNumber(amountPaidInput ? amountPaidInput.value : '');
        return Math.min(typedAmount, selectedOutstanding);
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

    function updateSubmitState(selectedCount, paidNow, isCash, received) {
        let disabled = false;

        if (selectedCount <= 0) {
            disabled = true;
        }

        if (paidNow <= 0) {
            disabled = true;
        }

        if (isCash && received > 0 && received < paidNow) {
            disabled = true;
        }

        submitButton.disabled = disabled;
    }

    function update() {
        const selectedCount = selectedRows().length;
        const outstanding = selectedOutstandingAmount();
        const paidNow = amountToPay();
        const received = parseNumber(amountReceivedInput ? amountReceivedInput.value : '');
        const isCash = paymentMethod && paymentMethod.value === 'cash';

        syncHiddenSelectedRows();

        selectedRowCount.textContent = formatNumber(selectedCount);
        selectedOutstandingTotal.textContent = formatNumber(outstanding);
        selectedPaymentTotal.textContent = formatNumber(paidNow);
        paymentRemainingText.textContent = formatNumber(Math.max(outstanding - paidNow, 0));
        paymentChangeText.textContent = formatNumber(isCash ? Math.max(received - paidNow, 0) : 0);

        if (amountReceivedInput) {
            amountReceivedInput.disabled = !isCash;
        }

        updateSubmitState(selectedCount, paidNow, isCash, received);
    }

    rowSelectors.forEach((input) => {
        input.addEventListener('change', update);
    });

    form.addEventListener('input', update);
    form.addEventListener('change', update);

    update();
})();
