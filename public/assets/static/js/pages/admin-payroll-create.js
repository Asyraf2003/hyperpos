(() => {
  const config = window.payrollCreateConfig || {};
  const employees = Array.isArray(config.employees) ? config.employees : [];
  const oldRows = Array.isArray(config.oldRows) ? config.oldRows : [];

  const searchInput = document.getElementById('payroll-batch-employee-search-input');
  const list = document.getElementById('payroll-batch-employee-list');
  const rows = document.getElementById('payroll-batch-selected-rows');
  const summary = document.getElementById('payroll-batch-selected-summary');
  const clearButton = document.getElementById('payroll-batch-clear-button');

  if (!searchInput || !list || !rows || !summary || !clearButton) return;

  const esc = (value) => String(value ?? '').replace(/[&<>"']/g, (char) => ({
    '&': '&amp;',
    '<': '&lt;',
    '>': '&gt;',
    '"': '&quot;',
    "'": '&#39;'
  }[char]));

  const salaryToInt = (formatted) => {
    const digits = String(formatted ?? '').replace(/\D/g, '');
    const value = Number.parseInt(digits, 10);
    return Number.isNaN(value) ? 0 : value;
  };

  const selected = [];

  const findEmployee = (employeeId) => employees.find((item) => item.id === employeeId);

  const pushRow = (employeeId, amount, modeOverride = '', notesOverride = '') => {
    if (!findEmployee(employeeId)) return;
    if (selected.some((item) => item.employee_id === employeeId)) return;

    selected.push({
      employee_id: employeeId,
      amount: amount > 0 ? amount : salaryToInt(findEmployee(employeeId).base_salary_formatted),
      mode_value_override: modeOverride,
      notes_override: notesOverride,
    });
  };

  oldRows.forEach((row) => {
    pushRow(
      String(row.employee_id || ''),
      Number.parseInt(String(row.amount || '0'), 10),
      String(row.mode_value_override || ''),
      String(row.notes_override || '')
    );
  });

  const filteredEmployees = (query) => {
    const terms = query.toLowerCase().split(/\s+/).filter(Boolean);

    return employees.filter((employee) => {
      const haystack = [
        employee.name,
        employee.phone,
        employee.pay_period_label,
        employee.status_label,
      ].join(' ').toLowerCase();

      return terms.every((term) => haystack.includes(term));
    });
  };

  const renderList = () => {
    const query = searchInput.value.trim();
    const items = filteredEmployees(query);

    if (!items.length) {
      list.innerHTML = '<div class="text-muted">Tidak ada karyawan yang cocok.</div>';
      return;
    }

    list.innerHTML = items.map((employee) => {
      const alreadySelected = selected.some((row) => row.employee_id === employee.id);

      return `
        <div class="border rounded p-3">
          <div class="d-flex justify-content-between align-items-start gap-3">
            <div>
              <div class="fw-semibold">${esc(employee.name)}</div>
              <div class="small text-muted">${esc(employee.phone || '-')}</div>
              <div class="small text-muted">
                ${esc(employee.pay_period_label)} • ${esc(employee.status_label)} • Rp${esc(employee.base_salary_formatted)}
              </div>
            </div>
            <button
              type="button"
              class="btn btn-sm ${alreadySelected ? 'btn-light-secondary' : 'btn-primary'}"
              data-add-employee-id="${esc(employee.id)}"
              ${alreadySelected ? 'disabled' : ''}
            >
              ${alreadySelected ? 'Dipilih' : 'Tambah'}
            </button>
          </div>
        </div>
      `;
    }).join('');
  };

  const renderSelectedRows = () => {
    if (!selected.length) {
      rows.innerHTML = `
        <div class="border rounded p-4 text-center text-muted">
          Belum ada karyawan dipilih. Tambahkan dari panel kiri.
        </div>
      `;
      summary.textContent = 'Belum ada karyawan dipilih.';
      return;
    }

    summary.textContent = `${selected.length} karyawan dipilih untuk batch ini.`;

    rows.innerHTML = selected.map((row, index) => {
      const employee = findEmployee(row.employee_id);
      if (!employee) return '';

      return `
        <div class="card border">
          <div class="card-body">
            <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-2 mb-3">
              <div>
                <h6 class="mb-1">${esc(employee.name)}</h6>
                <div class="small text-muted">
                  ${esc(employee.phone || '-')} • ${esc(employee.pay_period_label)} • ${esc(employee.status_label)}
                </div>
                <div class="small text-muted">
                  Gaji pokok referensi: Rp${esc(employee.base_salary_formatted)}
                </div>
              </div>

              <button
                type="button"
                class="btn btn-sm btn-light-danger"
                data-remove-employee-id="${esc(employee.id)}"
              >
                Hapus
              </button>
            </div>

            <input type="hidden" name="rows[${index}][employee_id]" value="${esc(employee.id)}">

            <div class="row">
              <div class="col-12 col-md-4">
                <div class="form-group mb-3" data-money-input-group>
                  <label class="form-label">Nominal</label>
                  <input type="hidden" name="rows[${index}][amount]" value="${esc(row.amount)}" data-money-raw>
                  <input type="text" value="${esc(row.amount)}" class="form-control" inputmode="numeric" data-money-display required>
                </div>
              </div>

              <div class="col-12 col-md-4">
                <div class="form-group mb-3">
                  <label class="form-label">Override Mode</label>
                  <select name="rows[${index}][mode_value_override]" class="form-select">
                    <option value="">Ikuti mode batch</option>
                    <option value="monthly" ${row.mode_value_override === 'monthly' ? 'selected' : ''}>Bulanan</option>
                    <option value="weekly" ${row.mode_value_override === 'weekly' ? 'selected' : ''}>Mingguan</option>
                    <option value="daily" ${row.mode_value_override === 'daily' ? 'selected' : ''}>Harian</option>
                  </select>
                </div>
              </div>

              <div class="col-12 col-md-4">
                <div class="form-group mb-3">
                  <label class="form-label">Catatan Override</label>
                  <input
                    type="text"
                    name="rows[${index}][notes_override]"
                    value="${esc(row.notes_override)}"
                    class="form-control"
                    placeholder="Opsional"
                  >
                </div>
              </div>
            </div>
          </div>
        </div>
      `;
    }).join('');

    window.AdminMoneyInput?.bindBySelector(rows);
  };

  searchInput.addEventListener('input', () => {
    renderList();
  });

  list.addEventListener('click', (event) => {
    const button = event.target.closest('[data-add-employee-id]');
    if (!button) return;

    const employeeId = button.dataset.addEmployeeId;
    const employee = findEmployee(employeeId);
    if (!employee) return;

    pushRow(employeeId, salaryToInt(employee.base_salary_formatted));
    renderSelectedRows();
    renderList();
  });

  rows.addEventListener('click', (event) => {
    const button = event.target.closest('[data-remove-employee-id]');
    if (!button) return;

    const employeeId = button.dataset.removeEmployeeId;
    const index = selected.findIndex((item) => item.employee_id === employeeId);
    if (index === -1) return;

    selected.splice(index, 1);
    renderSelectedRows();
    renderList();
  });

  clearButton.addEventListener('click', () => {
    selected.splice(0, selected.length);
    renderSelectedRows();
    renderList();
  });

  renderSelectedRows();
  renderList();
})();
