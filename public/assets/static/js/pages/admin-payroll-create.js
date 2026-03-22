(() => {
  const search = document.getElementById('payroll-employee-search-input');
  const select = document.getElementById('employee_id');
  const mode = document.getElementById('mode_value');
  if (!search || !select || !mode) return;

  const config = window.payrollCreateConfig || {};
  const options = Array.from(select.querySelectorAll('option[value]')).map((option) => ({
    value: option.value,
    text: option.textContent.trim(),
    name: option.dataset.name || '',
    phone: option.dataset.phone || '',
    statusLabel: option.dataset.statusLabel || '-',
    payPeriodValue: option.dataset.payPeriodValue || '',
    payPeriodLabel: option.dataset.payPeriodLabel || '-',
    baseSalaryFormatted: option.dataset.baseSalaryFormatted || '-',
  }));

  const setText = (id, value) => {
    const el = document.getElementById(id);
    if (el) el.textContent = value && value !== '' ? value : '-';
  };

  const syncInfo = () => {
    const current = options.find((item) => item.value === select.value);
    setText('payroll-selected-employee-name', current?.name || '-');
    setText('payroll-selected-employee-phone', current?.phone || '-');
    setText('payroll-selected-employee-status', current?.statusLabel || '-');
    setText('payroll-selected-employee-period', current?.payPeriodLabel || '-');
    setText('payroll-selected-employee-salary', current ? `Rp${current.baseSalaryFormatted}` : '-');
    if (current && current.payPeriodValue) mode.value = current.payPeriodValue;
  };

  const renderOptions = (query) => {
    const currentValue = select.value;
    const terms = query.toLowerCase().split(/\s+/).filter(Boolean);
    const filtered = options.filter((item) => {
      const haystack = `${item.name} ${item.phone} ${item.payPeriodLabel} ${item.statusLabel}`.toLowerCase();
      return terms.every((term) => haystack.includes(term));
    });

    select.innerHTML = '<option value="">Pilih karyawan</option>' + filtered.map((item) => `
      <option value="${item.value}" ${item.value === currentValue ? 'selected' : ''}>
        ${item.text}
      </option>`).join('');
  };

  search.addEventListener('input', (event) => {
    renderOptions(event.target.value.trim());
  });

  select.addEventListener('change', () => {
    syncInfo();
  });

  if (config.hasOldMode !== true) {
    syncInfo();
  } else {
    const current = options.find((item) => item.value === select.value);
    setText('payroll-selected-employee-name', current?.name || '-');
    setText('payroll-selected-employee-phone', current?.phone || '-');
    setText('payroll-selected-employee-status', current?.statusLabel || '-');
    setText('payroll-selected-employee-period', current?.payPeriodLabel || '-');
    setText('payroll-selected-employee-salary', current ? `Rp${current.baseSalaryFormatted}` : '-');
  }
})();
