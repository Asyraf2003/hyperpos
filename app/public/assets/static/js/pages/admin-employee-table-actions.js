(() => {
  const c = window.employeeTableConfig;
  const modalEl = document.getElementById('employee-action-modal');

  if (!c || !modalEl || !window.bootstrap?.Modal) return;

  const modal = new window.bootstrap.Modal(modalEl);
  const title = document.getElementById('employee-action-modal-title');
  const subtitle = document.getElementById('employee-action-modal-subtitle');
  const detailLink = document.getElementById('employee-action-detail-link');
  const editLink = document.getElementById('employee-action-edit-link');
  const payrollLink = document.getElementById('employee-action-payroll-link');
  const debtLink = document.getElementById('employee-action-debt-link');

  const detailUrl = (id) => c.detailBaseUrl.replace('__ID__', encodeURIComponent(id));
  const editUrl = (id) => c.editBaseUrl.replace('__ID__', encodeURIComponent(id));
  const payrollDetailUrl = (id) => c.payrollDetailBaseUrl.replace('__ID__', encodeURIComponent(id));
  const debtShowUrl = (id) => c.debtShowBaseUrl.replace('__ID__', encodeURIComponent(id));
  const createDebtUrl = (id) => `${c.createDebtUrl}?employee_id=${encodeURIComponent(id)}`;

  document.addEventListener('click', (event) => {
    const button = event.target.closest('.js-open-employee-action');
    if (!button) return;

    const employeeId = String(button.dataset.employeeId || '').trim();
    const employeeName = String(button.dataset.employeeName || 'Karyawan').trim();
    const salaryBasisLabel = String(button.dataset.salaryBasisLabel || '-').trim();
    const employmentStatusLabel = String(button.dataset.employmentStatusLabel || '-').trim();
    const debtDetailId = String(button.dataset.debtDetailId || '').trim();

    if (employeeId === '') return;

    title.textContent = `Aksi Karyawan: ${employeeName}`;
    subtitle.textContent = `${salaryBasisLabel} • ${employmentStatusLabel}`;

    detailLink.href = detailUrl(employeeId);
    editLink.href = editUrl(employeeId);
    payrollLink.href = payrollDetailUrl(employeeId);
    debtLink.href = debtDetailId !== ''
      ? debtShowUrl(debtDetailId)
      : createDebtUrl(employeeId);

    modal.show();
  });
})();
