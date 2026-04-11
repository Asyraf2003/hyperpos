(() => {
  const c = window.employeeDebtTableConfig;
  const modalEl = document.getElementById('employee-debt-action-modal');

  if (!c || !modalEl || !window.bootstrap?.Modal) return;

  const modal = new window.bootstrap.Modal(modalEl);
  const title = document.getElementById('employee-debt-action-modal-title');
  const subtitle = document.getElementById('employee-debt-action-modal-subtitle');
  const detailLink = document.getElementById('employee-debt-action-detail-link');
  const createLink = document.getElementById('employee-debt-action-create-link');
  const payrollLink = document.getElementById('employee-debt-action-payroll-link');

  const detailUrl = (id) => c.detailBaseUrl.replace('__ID__', encodeURIComponent(id));

  document.addEventListener('click', (event) => {
    const button = event.target.closest('.js-open-employee-debt-action');
    if (!button) return;

    const employeeId = String(button.dataset.employeeId || '').trim();
    const employeeName = String(button.dataset.employeeName || 'Karyawan').trim();
    const debtStatusSummary = String(button.dataset.debtStatusSummary || '-').trim();

    if (employeeId === '') return;

    title.textContent = `Aksi Hutang: ${employeeName}`;
    subtitle.textContent = debtStatusSummary;

    detailLink.href = detailUrl(employeeId);
    createLink.href = c.createDebtUrl;
    payrollLink.href = c.payrollIndexUrl;

    modal.show();
  });
})();
