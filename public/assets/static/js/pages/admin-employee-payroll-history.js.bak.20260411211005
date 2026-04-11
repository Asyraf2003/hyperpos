(() => {
  const c = window.employeePayrollHistoryConfig;
  if (!c) return;

  const $ = (id) => document.getElementById(id);
  const body = $('employee-payroll-history-body');
  const sum = $('employee-payroll-history-summary');
  const pag = $('employee-payroll-history-pagination');

  let req = 0;
  let page = 1;

  const esc = (v) => String(v ?? '').replace(/[&<>"']/g, (m) => ({
    '&': '&amp;',
    '<': '&lt;',
    '>': '&gt;',
    '"': '&quot;',
    "'": '&#39;',
  }[m]));

  const renderSummary = (m) => {
    sum.textContent = `Total: ${m.total} riwayat gaji`;
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

  const renderRows = (rows, m) => {
    if (!rows.length) {
      body.innerHTML = '<tr><td colspan="6" class="text-center text-muted py-4">Belum ada riwayat gaji untuk karyawan ini.</td></tr>';
      return;
    }

    body.innerHTML = rows.map((r, i) => {
      const notesHtml = r.is_reversed && r.reversal_reason
        ? `${esc(r.notes ?? '-')}<div class="small text-danger mt-1">Dibatalkan: ${esc(r.reversal_reason)}</div>`
        : esc(r.notes ?? '-');

      const statusHtml = r.is_reversed
        ? `<span class="badge bg-light-danger text-danger">Dibatalkan</span>${r.reversal_created_at ? `<div class="small text-muted mt-1">${esc(r.reversal_created_at)}</div>` : ''}`
        : '<span class="badge bg-light-success text-success">Aktif</span>';

      return `
        <tr>
          <td>${(m.page - 1) * m.per_page + i + 1}</td>
          <td>${esc(r.disbursement_date)}</td>
          <td>Rp${esc(r.amount_formatted)}</td>
          <td>${esc(r.mode_label)}</td>
          <td>${notesHtml}</td>
          <td>${statusHtml}</td>
        </tr>
      `;
    }).join('');
  };

  const load = async () => {
    const current = ++req;
    body.innerHTML = '<tr><td colspan="6" class="text-center text-muted py-4">Memuat data...</td></tr>';

    const query = new URLSearchParams({
      page: String(page),
      per_page: '10',
    });

    const res = await fetch(`${c.endpoint}?${query.toString()}`, {
      headers: { Accept: 'application/json' },
    });

    const json = await res.json();

    if (current !== req) return;

    if (!res.ok || !json.success) {
      body.innerHTML = '<tr><td colspan="6" class="text-center text-danger py-4">Gagal memuat riwayat gaji.</td></tr>';
      return;
    }

    renderRows(json.data.rows || [], json.data.meta || {});
    renderSummary(json.data.meta || {});
    renderPager(json.data.meta || {});
  };

  pag?.addEventListener('click', (e) => {
    const button = e.target.closest('[data-page]');
    if (!button) return;

    e.preventDefault();
    page = Number(button.dataset.page || '1');
    load();
  });

  load();
})();
