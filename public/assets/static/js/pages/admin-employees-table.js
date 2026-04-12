(() => {
  const c = window.employeeTableConfig;
  if (!c) return;

  const $ = (id) => document.getElementById(id);
  const form = $('employee-search-form');
  const q = $('employee-search-input');
  const body = $('employee-table-body');
  const sum = $('employee-table-summary');
  const pag = $('employee-table-pagination');

  const allowedSortBy = new Set([
    'employee_name',
    'default_salary_amount',
    'salary_basis_type',
    'employment_status',
  ]);
  const allowedSortDir = new Set(['asc', 'desc']);

  let timer = null;
  let req = 0;

  const esc = (v) => String(v ?? '').replace(/[&<>"']/g, (m) => ({
    '&': '&amp;',
    '<': '&lt;',
    '>': '&gt;',
    '"': '&quot;',
    "'": '&#39;',
  }[m]));

  const trim = (v) => String(v ?? '').trim();

  const intOr = (v, f) => {
    const n = Number.parseInt(String(v ?? ''), 10);
    return Number.isNaN(n) || n < 1 ? f : n;
  };

  const stateFromUrl = () => {
    const p = new URLSearchParams(window.location.search);
    const sortBy = trim(p.get('sort_by'));
    const sortDir = trim(p.get('sort_dir'));

    return {
      q: trim(p.get('q')),
      page: intOr(p.get('page'), 1),
      sort_by: allowedSortBy.has(sortBy) ? sortBy : 'employee_name',
      sort_dir: allowedSortDir.has(sortDir) ? sortDir : 'asc',
    };
  };

  const s = stateFromUrl();

  const params = () => {
    const out = {
      page: String(s.page),
      per_page: '10',
      sort_by: s.sort_by,
      sort_dir: s.sort_dir,
    };

    if (s.q) out.q = s.q;

    return out;
  };

  const paramsString = () => new URLSearchParams(params()).toString();

  const updateUrl = (replace = false) => {
    const url = new URL(window.location.href);
    url.search = paramsString();

    if (replace) {
      window.history.replaceState(null, '', url);
      return;
    }

    window.history.pushState(null, '', url);
  };

  const renderSummary = (m) => {
    sum.textContent = `Total: ${m.total} karyawan`;
  };

  const renderSort = () => {
    document.querySelectorAll('[data-sort-indicator]').forEach((n) => {
      const active = n.dataset.sortIndicator === s.sort_by;
      n.textContent = active ? (s.sort_dir === 'asc' ? '↑' : '↓') : '↕';
      n.classList.toggle('text-muted', !active);
    });
  };

  const renderPager = (m) => {
    if (m.last_page <= 1) {
      pag.innerHTML = '';
      return;
    }

    const start = Math.max(1, m.page - 2);
    const end = Math.min(m.last_page, m.page + 2);

    let html = '<nav><ul class="pagination pagination-primary mb-0">';
    html += `<li class="page-item ${m.page === 1 ? 'disabled' : ''}"><a class="page-link" href="#" data-page="${m.page - 1}"><i class="bi bi-chevron-left"></i></a></li>`;

    for (let p = start; p <= end; p += 1) {
      html += `<li class="page-item ${p === m.page ? 'active' : ''}"><a class="page-link" href="#" data-page="${p}">${p}</a></li>`;
    }

    html += `<li class="page-item ${m.page === m.last_page ? 'disabled' : ''}"><a class="page-link" href="#" data-page="${m.page + 1}"><i class="bi bi-chevron-right"></i></a></li>`;
    html += '</ul></nav>';

    pag.innerHTML = html;
  };

  const renderSalary = (row) => {
    if (row.default_salary_amount_formatted === null || row.default_salary_amount_formatted === undefined) {
      return '-';
    }

    return `Rp${esc(row.default_salary_amount_formatted)}`;
  };

  const renderRows = (rows, m) => {
    if (!rows.length) {
      body.innerHTML = '<tr><td colspan="7" class="text-center text-muted py-4">Belum ada data karyawan.</td></tr>';
      return;
    }

    body.innerHTML = rows.map((row, i) => `
      <tr>
        <td>${(m.page - 1) * m.per_page + i + 1}</td>
        <td>${esc(row.employee_name)}</td>
        <td>${esc(row.phone ?? '-')}</td>
        <td>${renderSalary(row)}</td>
        <td>${esc(row.salary_basis_label)}</td>
        <td>${esc(row.employment_status_label)}</td>
        <td class="text-center">
          <button
            type="button"
            class="btn btn-sm btn-light-primary js-open-employee-action"
            data-employee-id="${esc(row.id)}"
            data-employee-name="${esc(row.employee_name)}"
            data-salary-basis-label="${esc(row.salary_basis_label)}"
            data-employment-status-label="${esc(row.employment_status_label)}"
            data-latest-unpaid-debt-id="${esc(row.latest_unpaid_debt_id ?? '')}"
          >
            Aksi
          </button>
        </td>
      </tr>
    `).join('');
  };

  const load = async (replace = false) => {
    const current = ++req;
    body.innerHTML = '<tr><td colspan="7" class="text-center text-muted py-4">Memuat data...</td></tr>';

    const res = await fetch(`${c.endpoint}?${paramsString()}`, {
      headers: { Accept: 'application/json' },
    });
    const json = await res.json();

    if (current !== req) return;

    if (!res.ok || !json.success) {
      body.innerHTML = '<tr><td colspan="7" class="text-center text-danger py-4">Gagal memuat data.</td></tr>';
      return;
    }

    renderRows(json.data.rows || [], json.data.meta || {});
    renderSummary(json.data.meta || {});
    renderPager(json.data.meta || {});
    renderSort();

    if (q) {
      q.value = s.q;
    }

    updateUrl(replace);
  };

  form?.addEventListener('submit', (e) => {
    e.preventDefault();
    const value = trim(q?.value);

    if (value.length === 0) {
      s.q = '';
      s.page = 1;
      load();
      return;
    }

    if (value.length >= 2) {
      s.q = value;
      s.page = 1;
      load();
    }
  });

  q?.addEventListener('input', () => {
    const value = trim(q.value);
    clearTimeout(timer);

    if (value.length === 0) {
      s.q = '';
      s.page = 1;
      timer = setTimeout(() => load(), 250);
      return;
    }

    if (value.length < 2) return;

    timer = setTimeout(() => {
      s.q = value;
      s.page = 1;
      load();
    }, 300);
  });

  document.querySelectorAll('[data-sort-by]').forEach((b) => b.addEventListener('click', () => {
    const key = b.dataset.sortBy;
    s.sort_dir = s.sort_by === key && s.sort_dir === 'asc' ? 'desc' : 'asc';
    s.sort_by = key;
    load();
  }));

  pag?.addEventListener('click', (e) => {
    const b = e.target.closest('[data-page]');
    if (!b) return;
    s.page = Number(b.dataset.page);
    load();
  });

  load(true);
})();
