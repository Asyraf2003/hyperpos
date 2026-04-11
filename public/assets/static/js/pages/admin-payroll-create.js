(() => {
  const c = window.payrollCreateConfig;
  if (!c || !Array.isArray(c.employees)) return;

  const $ = (id) => document.getElementById(id);

  const form = $('payroll-create-form');
  const employeeId = $('employee_id');
  const employeeSearch = $('employee_search');
  const results = $('payroll-employee-search-results');
  const selected = $('payroll-selected-employee');
  const amount = $('amount_display');
  const date = $('disbursement_date_string');
  const mode = $('mode_value');
  const notes = $('notes');

  if (!form || !employeeId || !employeeSearch || !results || !selected || !amount || !date || !mode || !notes) {
    return;
  }

  const esc = (v) => String(v ?? '').replace(/[&<>"']/g, (m) => ({
    '&': '&amp;',
    '<': '&lt;',
    '>': '&gt;',
    '"': '&quot;',
    "'": '&#39;',
  }[m]));

  const norm = (v) => String(v ?? '').trim().toLowerCase();

  const formatMoney = (value) => {
    const num = Number.parseInt(String(value ?? ''), 10);
    if (Number.isNaN(num) || num < 1) return '-';
    return `Rp${num.toLocaleString('id-ID')}`;
  };

  const basisLabel = (value) => {
    switch (String(value ?? '')) {
      case 'daily':
        return 'Harian';
      case 'weekly':
        return 'Mingguan';
      case 'monthly':
        return 'Bulanan';
      case 'manual':
        return 'Manual';
      default:
        return String(value ?? '-') || '-';
    }
  };

  const employees = c.employees.map((employee) => ({
    id: String(employee.id ?? ''),
    employee_name: String(employee.employee_name ?? ''),
    phone: employee.phone ?? null,
    salary_basis_type: employee.salary_basis_type ?? null,
    default_salary_amount: employee.default_salary_amount ?? null,
  }));

  let filtered = [];
  let activeIndex = 0;
  let timer = null;

  const hideResults = () => {
    results.innerHTML = '';
    results.classList.add('d-none');
  };

  const renderSelected = (employee) => {
    if (!employee) {
      selected.innerHTML = 'Belum ada karyawan dipilih.';
      selected.classList.add('text-muted');
      return;
    }

    selected.classList.remove('text-muted');
    selected.innerHTML = `
      <div class="fw-semibold mb-1">${esc(employee.employee_name)}</div>
      <div class="small text-muted mb-1">Telepon: ${esc(employee.phone ?? '-')}</div>
      <div class="small text-muted mb-1">Basis Gaji: ${esc(basisLabel(employee.salary_basis_type))}</div>
      <div class="small text-muted">Default Gaji: ${esc(formatMoney(employee.default_salary_amount))}</div>
    `;
  };

  const renderResults = () => {
    if (!filtered.length) {
      results.innerHTML = '<div class="list-group-item text-muted">Karyawan tidak ditemukan.</div>';
      results.classList.remove('d-none');
      return;
    }

    results.innerHTML = filtered.map((employee, index) => `
      <button
        type="button"
        class="list-group-item list-group-item-action ${index === activeIndex ? 'active' : ''}"
        data-index="${index}"
      >
        <div class="fw-semibold">${esc(employee.employee_name)}</div>
        <div class="small ${index === activeIndex ? 'text-white' : 'text-muted'}">
          ${esc(employee.phone ?? '-')} · ${esc(basisLabel(employee.salary_basis_type))} · ${esc(formatMoney(employee.default_salary_amount))}
        </div>
      </button>
    `).join('');

    results.classList.remove('d-none');
  };

  const selectEmployee = (employee) => {
    employeeId.value = employee.id;
    employeeSearch.value = employee.employee_name;
    renderSelected(employee);
    hideResults();
    amount.focus();
    amount.select();
  };

  const runSearch = () => {
    const query = norm(employeeSearch.value);

    if (query.length < 2) {
      filtered = [];
      activeIndex = 0;
      employeeId.value = '';
      renderSelected(null);
      hideResults();
      return;
    }

    filtered = employees.filter((employee) => {
      const haystack = [
        employee.employee_name,
        employee.phone,
        employee.salary_basis_type,
      ].map(norm).join(' ');
      return haystack.includes(query);
    }).slice(0, 8);

    activeIndex = 0;
    employeeId.value = '';
    renderSelected(null);
    renderResults();
  };

  const queueSearch = () => {
    window.clearTimeout(timer);
    timer = window.setTimeout(runSearch, 250);
  };

  const moveFocusOnEnter = (current, next) => {
    current.addEventListener('keydown', (e) => {
      if (e.key !== 'Enter') return;
      e.preventDefault();
      next.focus();
      if (typeof next.select === 'function') {
        next.select();
      }
    });
  };

  employeeSearch.addEventListener('input', queueSearch);

  employeeSearch.addEventListener('keydown', (e) => {
    if (e.key === 'ArrowDown' && filtered.length) {
      e.preventDefault();
      activeIndex = Math.min(activeIndex + 1, filtered.length - 1);
      renderResults();
      return;
    }

    if (e.key === 'ArrowUp' && filtered.length) {
      e.preventDefault();
      activeIndex = Math.max(activeIndex - 1, 0);
      renderResults();
      return;
    }

    if (e.key === 'Enter') {
      if (!filtered.length) {
        e.preventDefault();
        return;
      }

      e.preventDefault();
      selectEmployee(filtered[activeIndex]);
    }

    if (e.key === 'Escape') {
      hideResults();
    }
  });

  results.addEventListener('click', (e) => {
    const button = e.target.closest('[data-index]');
    if (!button) return;

    const index = Number.parseInt(button.dataset.index ?? '', 10);
    if (Number.isNaN(index) || !filtered[index]) return;

    selectEmployee(filtered[index]);
  });

  moveFocusOnEnter(amount, date);
  moveFocusOnEnter(date, mode);
  moveFocusOnEnter(mode, notes);

  notes.addEventListener('keydown', (e) => {
    if (e.key !== 'Enter') return;
    e.preventDefault();
    form.requestSubmit();
  });

  const oldEmployee = employees.find((employee) => employee.id === employeeId.value);
  if (oldEmployee) {
    employeeSearch.value = oldEmployee.employee_name;
    renderSelected(oldEmployee);
  } else {
    renderSelected(null);
  }

  employeeSearch.focus();
  employeeSearch.select();
})();
