(() => {
    const actionButtons = Array.from(document.querySelectorAll('.js-line-action'));
    const detailButtons = Array.from(document.querySelectorAll('.js-line-detail-focus'));

    if (actionButtons.length === 0 && detailButtons.length === 0) {
        return;
    }

    function focusElement(element) {
        if (!element) {
            return;
        }

        element.scrollIntoView({ behavior: 'smooth', block: 'start' });

        if (typeof element.focus === 'function') {
            try {
                element.focus({ preventScroll: true });
            } catch (_error) {
                element.focus();
            }
        }
    }

    actionButtons.forEach((button) => {
        button.addEventListener('click', () => {
            const selectorId = (button.getAttribute('data-selector-id') || '').trim();
            const targetPanelSelector = (button.getAttribute('data-target-panel') || '').trim();

            if (selectorId === '') {
                return;
            }

            const selector = document.getElementById(selectorId);

            if (!(selector instanceof HTMLInputElement)) {
                return;
            }

            selector.checked = true;
            selector.dispatchEvent(new Event('change', { bubbles: true }));

            if (targetPanelSelector !== '') {
                const panel = document.querySelector(targetPanelSelector);

                if (panel instanceof HTMLElement) {
                    focusElement(panel);
                    return;
                }
            }

            focusElement(selector);
        });
    });

    detailButtons.forEach((button) => {
        button.addEventListener('click', () => {
            const rowId = (button.getAttribute('data-target-row-id') || '').trim();

            if (rowId === '') {
                return;
            }

            const paymentSelector = document.getElementById('payment-row-' + rowId);
            const refundSelector = document.getElementById('refund-row-' + rowId);

            if (paymentSelector instanceof HTMLElement) {
                focusElement(paymentSelector);
                return;
            }

            if (refundSelector instanceof HTMLElement) {
                focusElement(refundSelector);
            }
        });
    });
})();
