(() => {
  const c = window.employeeDebtTableConfig;
  const actionModalEl = document.getElementById('employee-debt-action-modal');
  const paymentModalEl = document.getElementById('employee-debt-payment-modal');

  if (!c || !actionModalEl || !paymentModalEl || !window.bootstrap?.Modal) return;

  const byAnyId = (...ids) => {
    for (const id of ids) {
      const el = document.getElementById(id);
      if (el) return el;
    }
    return null;
  };

  const actionModal = new window.bootstrap.Modal(actionModalEl);
  const paymentModal = new window.bootstrap.Modal(paymentModalEl);

  const actionTitle = byAnyId('employee-debt-action-modal-title');
  const actionSubtitle = byAnyId('employee-debt-action-modal-subtitle');
  const detailLink = byAnyId('employee-debt-action-detail-link');
  const addLink = byAnyId('employee-debt-action-add-link', 'employee-debt-action-create-link');
  const payButton = byAnyId('employee-debt-action-pay-button', 'employee-debt-action-pay-link');
  const debtLink = byAnyId('employee-debt-action-debt-link', 'employee-debt-action-payroll-link');
  const payEmpty = byAnyId('employee-debt-action-pay-empty');

  const paymentTitle = byAnyId('employee-debt-payment-modal-title');
  const paymentSubtitle = byAnyId('employee-debt-payment-modal-subtitle');
  const paymentForm = byAnyId('employee-debt-payment-form');
  const paymentAmountRaw = byAnyId('employee-debt-payment-amount');
  const paymentAmountDisplay = byAnyId('employee-debt-payment-amount-display');
  const paymentNotes = byAnyId('employee-debt-payment-notes');

  const detailUrl = (id) => c.detailBaseUrl.replace('__ID__', encodeURIComponent(id));
  const createDebtUrl = (id) => `${c.createDebtUrl}?employee_id=${encodeURIComponent(id)}`;
  const debtShowUrl = (id) => c.debtShowBaseUrl.replace('__ID__', encodeURIComponent(id));
  const principalUrl = (id) => c.principalBaseUrl.replace('__ID__', encodeURIComponent(id));
  const paymentStoreUrl = (id) => c.paymentStoreBaseUrl.replace('__ID__', encodeURIComponent(id));

  let currentPaymentDebtId = '';
  let currentEmployeeName = '';
  let currentDebtStatus = '';

  const resetPaymentForm = () => {
    currentPaymentDebtId = '';
    currentEmployeeName = '';
    currentDebtStatus = '';

    if (paymentForm) paymentForm.action = '#';
    if (paymentAmountRaw) paymentAmountRaw.value = '';
    if (paymentAmountDisplay) paymentAmountDisplay.value = '';
    if (paymentNotes) paymentNotes.value = '';
    if (paymentTitle) paymentTitle.textContent = 'Bayar Hutang';
    if (paymentSubtitle) paymentSubtitle.textContent = 'Catat pembayaran hutang karyawan.';

    window.AdminMoneyInput?.bindBySelector(document);
  };

  const setPayButtonEnabled = (enabled) => {
    if (!payButton) return;

    if (enabled) {
      payButton.classList.remove('disabled');
      payButton.removeAttribute('aria-disabled');
      if ('disabled' in payButton) payButton.disabled = false;
      return;
    }

    payButton.classList.add('disabled');
    payButton.setAttribute('aria-disabled', 'true');
    if ('disabled' in payButton) payButton.disabled = true;
  };

  document.addEventListener('click', (event) => {
    const button = event.target.closest('.js-open-employee-debt-action');
    if (!button) return;

    const employeeId = String(button.dataset.employeeId || '').trim();
    const employeeName = String(button.dataset.employeeName || 'Karyawan').trim();
    const debtStatusSummary = String(button.dataset.debtStatusSummary || '-').trim();
    const debtDetailId = String(button.dataset.debtDetailId || '').trim();
    const latestUnpaidDebtId = String(button.dataset.latestUnpaidDebtId || '').trim();

    if (employeeId === '') return;

    resetPaymentForm();

    if (actionTitle) actionTitle.textContent = `Aksi Hutang: ${employeeName}`;
    if (actionSubtitle) actionSubtitle.textContent = debtStatusSummary;

    if (detailLink) {
      detailLink.href = detailUrl(employeeId);
    }

    if (addLink) {
      addLink.href = debtDetailId !== ''
        ? principalUrl(debtDetailId)
        : createDebtUrl(employeeId);
    }

    if (debtLink) {
      if (debtDetailId !== '') {
        debtLink.href = debtShowUrl(debtDetailId);
        debtLink.classList.remove('disabled');
        debtLink.removeAttribute('aria-disabled');
      } else {
        debtLink.href = '#';
        debtLink.classList.add('disabled');
        debtLink.setAttribute('aria-disabled', 'true');
      }
    }

    if (latestUnpaidDebtId !== '') {
      currentPaymentDebtId = latestUnpaidDebtId;
      currentEmployeeName = employeeName;
      currentDebtStatus = debtStatusSummary;

      setPayButtonEnabled(true);

      if (payEmpty) payEmpty.classList.add('d-none');
    } else {
      setPayButtonEnabled(false);

      if (payEmpty) payEmpty.classList.remove('d-none');
    }

    actionModal.show();
  });

  payButton?.addEventListener('click', () => {
    if (currentPaymentDebtId === '' || !paymentForm) return;

    paymentForm.action = paymentStoreUrl(currentPaymentDebtId);

    if (paymentTitle) {
      paymentTitle.textContent = `Bayar Hutang: ${currentEmployeeName}`;
    }

    if (paymentSubtitle) {
      paymentSubtitle.textContent = currentDebtStatus !== ''
        ? `Status saat ini: ${currentDebtStatus}`
        : 'Catat pembayaran hutang karyawan.';
    }

    actionModal.hide();

    window.setTimeout(() => {
      window.AdminMoneyInput?.bindBySelector(document);
      paymentModal.show();
    }, 150);
  });

  paymentModalEl.addEventListener('hidden.bs.modal', () => {
    resetPaymentForm();
  });
})();
