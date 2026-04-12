(() => {
  const c = window.employeeDebtTableConfig;
  const modalEl = document.getElementById('employee-debt-action-modal');

  if (!c || !modalEl || !window.bootstrap?.Modal) return;

  const modal = new window.bootstrap.Modal(modalEl);
  const title = document.getElementById('employee-debt-action-modal-title');
  const subtitle = document.getElementById('employee-debt-action-modal-subtitle');
  const detailLink = document.getElementById('employee-debt-action-detail-link');
  const createLink = document.getElementById('employee-debt-action-create-link');
  const payLink = document.getElementById('employee-debt-action-pay-link');
  const payrollLink = document.getElementById('employee-debt-action-payroll-link');
  const payEmpty = document.getElementById('employee-debt-action-pay-empty');

  const detailUrl = (id) => c.detailBaseUrl.replace('__ID__', encodeURIComponent(id));
  const createDebtUrl = (id) => `${c.createDebtUrl}?employee_id=${encodeURIComponent(id)}`;
  const debtShowUrl = (id) => c.debtShowBaseUrl.replace('__ID__', encodeURIComponent(id));
  const payrollDetailUrl = (id) => c.employeePayrollDetailBaseUrl.replace('__ID__', encodeURIComponent(id));

  document.addEventListener('click', (event) => {
    const button = event.target.closest('.js-open-employee-debt-action');
    if (!button) return;

    const employeeId = String(button.dataset.employeeId || '').trim();
    const employeeName = String(button.dataset.employeeName || 'Karyawan').trim();
    const debtStatusSummary = String(button.dataset.debtStatusSummary || '-').trim();
    const latestUnpaidDebtId = String(button.dataset.latestUnpaidDebtId || '').trim();

    if (employeeId === '') return;

    title.textContent = `Aksi Hutang: ${employeeName}`;
    subtitle.textContent = debtStatusSummary;

    detailLink.href = detailUrl(employeeId);
    createLink.href = createDebtUrl(employeeId);
    payrollLink.href = payrollDetailUrl(employeeId);

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
