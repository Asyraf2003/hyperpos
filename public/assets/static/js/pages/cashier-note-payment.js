(() => {
    const form = document.getElementById('note-payment-form');

    if (!form) {
        return;
    }

    const outstandingInput = document.getElementById('note-outstanding-display');
    const amountPaidInput = document.getElementById('amount-paid');
    const amountReceivedInput = document.getElementById('amount-received');
    const paymentMethod = document.getElementById('payment-method');
    const selectedPaymentTotal = document.getElementById('selected-payment-total');
    const paymentRemainingText = document.getElementById('payment-remaining-text');
    const paymentChangeText = document.getElementById('payment-change-text');
    const scopeInputs = document.querySelectorAll('input[name="payment_scope"]');

    if (!outstandingInput || !selectedPaymentTotal || !paymentRemainingText || !paymentChangeText) {
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

    function selectedScope() {
        const checked = Array.from(scopeInputs).find((input) => input.checked);
        return checked ? checked.value : 'full';
    }

    function outstandingAmount() {
        const raw = outstandingInput.getAttribute('data-outstanding-rupiah') || '0';
        return parseNumber(raw);
    }

    function amountToPay() {
        if (selectedScope() === 'full') {
            return outstandingAmount();
        }

        return parseNumber(amountPaidInput ? amountPaidInput.value : '');
    }

    function update() {
        const outstanding = outstandingAmount();
        const paidNow = Math.min(amountToPay(), outstanding);
        const received = parseNumber(amountReceivedInput ? amountReceivedInput.value : '');
        const isCash = paymentMethod && paymentMethod.value === 'cash';

        selectedPaymentTotal.value = formatNumber(paidNow);
        paymentRemainingText.textContent = formatNumber(Math.max(outstanding - paidNow, 0));
        paymentChangeText.textContent = formatNumber(isCash ? Math.max(received - paidNow, 0) : 0);

        if (amountPaidInput) {
            amountPaidInput.disabled = selectedScope() !== 'partial';
        }

        if (amountReceivedInput) {
            amountReceivedInput.disabled = !isCash;
        }
    }

    form.addEventListener('input', update);
    form.addEventListener('change', update);

    update();
})();
