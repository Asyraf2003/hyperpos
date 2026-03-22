(() => {
  const c = window.employeePayrollTableConfig;
  if (!c) return;

  const $ = (id) => document.getElementById(id);
  const body = $('employee-payroll-table-body');
  const sum = $('employee-payroll-table-summary');
  const pag = $('employee-payroll-table-pagination');

  if (!body || !sum || !pag) return;

  let req = 0;

  const esc = (v) => String(v ?? '').replace(/[&<>"']/g, (m) => ({
    '&': '&amp;',
    '<': '&lt;',
    '>': '&gt;',
    '"': '&quot;',
    "'": '&#39;',
  }[m]));

  const intOr = (v, fallback) => {
    const n = Number.parseInt(String(v ?? ''), 10);
    return Number.isNaN(n) || n < 1 ? fallback : n;
  };

  const reverseUrl = (id) => c.reverseBaseUrl.replace('__ID__', encodeURIComponent(id));

  const stateFromUrl = () => {
    const p = new URLSearchParams(window.location.search);

    return {
      page: intOr(p.get('payroll_page'), 1),
    };
  };

  const s = stateFromUrl();

  const params = () => ({
    page: String(s.page),
    per_page: '10',
  });

  const paramsString = () => new URLSearchParams(params()).toString();

  const updateUrl = (replace = false) => {
    const url = new URL(window.location.href);

    if (s.page > 1) {
      url.searchParams.set('payroll_page', String(s.page));
    } else {
      url.searchParams.delete('payroll_page');
    }

    if (replace) {
      window.history.replaceState(null, '', url);
      return;
    }

    window.history.pushState(null, '', url);
  };

  const renderSummary = (m) => {
    sum.textContent = `Total: ${m.total ?? 0} riwayat gaji`;
  };

  const renderPager = (m) => {
    const page = intOr(m.page, 1);
    const lastPage = intOr(m.last_page, 1);

    if (lastPage <= 1) {
      pag.innerHTML = '';
      return;
    }

    const start = Math.max(1, page - 2);
    const end = Math.min(lastPage, page + 2);

    let html = '<nav><ul class="pagination pagination-primary mb-0">';
    html += `<li class="page-item ${page === 1 ? 'disabled' : ''}">`;
    html += `<a class="page-link" href="#" data-page="${page - 1}"><i class="bi bi-chevron-left"></i></a>`;
    html += '</li>';

    for (let p = start; p <= end; p += 1) {
      html += `<li class="page-item ${p === page ? 'active' : ''}">`;
      html += `<a class="page-link" href="#" data-page="${p}">${p}</a>`;
      html += '</li>';
    }

    html += `<li class="page-item ${page === lastPage ? 'disabled' : ''}">`;
    html += `<a class="page-link" href="#" data-page="${page + 1}"><i class="bi bi-chevron-right"></i></a>`;
    html += '</li>';
    html += '</ul></nav>';

    pag.innerHTML = html;
  };

  const renderRows = (rows, m) => {
    if (!Array.isArray(rows) || rows.length === 0) {
      body.innerHTML = '<tr><td colspan="7" class="text-center text-muted py-4">Belum ada riwayat gaji untuk karyawan ini.</td></tr>';
      return;
    }

    const page = intOr(m.page, 1);
    const perPage = intOr(m.per_page, 10);

    body.innerHTML = rows.map((row, i) => {
      const number = ((page - 1) * perPage) + i + 1;
      const statusHtml = row.is_reversed
        ? '<span class="text-danger">Direversal</span>'
        : '<span class="text-success">Aktif</span>';

      const notesHtml = row.is_reversed && row.reversal_reason
        ? `${esc(row.notes ?? '-')}<div class="small text-danger mt-1">Reversal: ${esc(row.reversal_reason)}</div>`
        : esc(row.notes ?? '-');

      const actionHtml = row.is_reversed
        ? '<span class="text-muted">-</span>'
        : `
          <form action="${reverseUrl(row.id)}" method="post" class="d-inline">
            <input type="hidden" name="_token" value="${esc(c.csrfToken)}">
            <input type="hidden" name="reason" value="Koreksi payout payroll">
            <button type="submit" class="btn btn-sm btn-light-danger">Reverse</button>
          </form>
        `;

      return `
        <tr>
          <td>${number}</td>
          <td>${esc(row.disbursement_date)}</td>
          <td>Rp${esc(row.amount_formatted)}</td>
          <td>${esc(row.mode_label)}</td>
          <td>${statusHtml}</td>
          <td>${notesHtml}</td>
          <td class="text-center">${actionHtml}</td>
        </tr>
      `;
    }).join('');
  };

  const load = async (replace = false) => {
    const current = ++req;
    body.innerHTML = '<tr><td colspan="7" class="text-center text-muted py-4">Memuat data...</td></tr>';

    try {
      const res = await fetch(`${c.endpoint}?${paramsString()}`, {
        headers: { Accept: 'application/json' },
      });

      const json = await res.json();

      if (current !== req) return;

      if (!res.ok || !json.success) {
        body.innerHTML = '<tr><td colspan="7" class="text-center text-danger py-4">Gagal memuat riwayat gaji.</td></tr>';
        return;
      }

      renderRows(json.data.rows || [], json.data.meta || {});
      renderSummary(json.data.meta || {});
      renderPager(json.data.meta || {});
      updateUrl(replace);
    } catch (_error) {
      if (current !== req) return;
      body.innerHTML = '<tr><td colspan="7" class="text-center text-danger py-4">Gagal memuat riwayat gaji.</td></tr>';
    }
  };

  pag.addEventListener('click', (e) => {
    const btn = e.target.closest('[data-page]');
    if (!btn) return;

    e.preventDefault();

    const nextPage = intOr(btn.dataset.page, s.page);
    if (nextPage < 1) return;

    s.page = nextPage;
    load();
  });

  window.addEventListener('popstate', () => {
    const next = stateFromUrl();
    s.page = next.page;
    load(true);
  });

  load(true);
})();
