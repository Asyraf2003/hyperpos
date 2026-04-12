(() => {
  const c = window.employeeDebtTableConfig;
  const modalEl = document.getElementById('employee-debt-action-modal');

  if (!c || !modalEl || !window.bootstrap?.Modal) return;

  const modal = new window.bootstrap.Modal(modalEl);
  const title = document.getElementById('employee-debt-action-modal-title');
  const subtitle = document.getElementById('employee-debt-action-modal-subtitle');
  const detailLink = document.getElementById('employee-debt-action-detail-link');
  const addLink = document.getElementById('employee-debt-action-add-link');
  const payLink = document.getElementById('employee-debt-action-pay-link');
  const debtLink = document.getElementById('employee-debt-action-debt-link');
  const payEmpty = document.getElementById('employee-debt-action-pay-empty');

  const detailUrl = (id) => c.detailBaseUrl.replace('__ID__', encodeURIComponent(id));
  const createDebtUrl = (id) => `${c.createDebtUrl}?employee_id=${encodeURIComponent(id)}`;
  const debtShowUrl = (id) => c.debtShowBaseUrl.replace('__ID__', encodeURIComponent(id));
  const principalUrl = (id) => c.principalBaseUrl.replace('__ID__', encodeURIComponent(id));

  document.addEventListener('click', (event) => {
    const button = event.target.closest('.js-open-employee-debt-action');
    if (!button) return;

    const employeeId = String(button.dataset.employeeId || '').trim();
    const employeeName = String(button.dataset.employeeName || 'Karyawan').trim();
    const debtStatusSummary = String(button.dataset.debtStatusSummary || '-').trim();
    const debtDetailId = String(button.dataset.debtDetailId || '').trim();
    const latestUnpaidDebtId = String(button.dataset.latestUnpaidDebtId || '').trim();

    if (employeeId === '') return;

    title.textContent = `Aksi Hutang: ${employeeName}`;
    subtitle.textContent = debtStatusSummary;

    detailLink.href = detailUrl(employeeId);
    addLink.href = debtDetailId !== ''
      ? principalUrl(debtDetailId)
      : createDebtUrl(employeeId);

    if (debtDetailId !== '') {
      debtLink.href = debtShowUrl(debtDetailId);
      debtLink.classList.remove('disabled');
      debtLink.removeAttribute('aria-disabled');
    } else {
      debtLink.href = '#';
      debtLink.classList.add('disabled');
      debtLink.setAttribute('aria-disabled', 'true');
    }

    if (latestUnpaidDebtId !== '') {
      payLink.href = debtShowUrl(latestUnpaidDebtId);
      payLink.classList.remove('disabled');
      payLink.removeAttribute('aria-disabled');

      if (payEmpty) payEmpty.classList.add('d-none');
    } else {
      payLink.href = '#';
      payLink.classList.add('disabled');
      payLink.setAttribute('aria-disabled', 'true');

      if (payEmpty) payEmpty.classList.remove('d-none');
    }

    modal.show();
  });
})();
