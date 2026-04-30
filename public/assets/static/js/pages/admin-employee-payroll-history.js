(() => {
  const c = window.employeePayrollHistoryConfig;
  if (!c) return;

  const $ = (id) => document.getElementById(id);
  const body = $('employee-payroll-history-body');
  const sum = $('employee-payroll-history-summary');
  const pag = $('employee-payroll-history-pagination');

  const reversalModalEl = $('employee-payroll-reversal-modal');
  const reversalForm = $('employee-payroll-reversal-form');
  const reversalReason = $('employee-payroll-reversal-reason');
  const reversalSubtitle = $('employee-payroll-reversal-modal-subtitle');
  const reversalModal = reversalModalEl && window.bootstrap?.Modal
    ? new window.bootstrap.Modal(reversalModalEl)
    : null;

  let req = 0;
  let page = 1;

  const esc = (v) => String(v ?? '').replace(/[&<>"']/g, (m) => ({

    '&': '&amp;',
    '<': '&lt;',
    '>': '&gt;',
    '"': '&quot;',
    "'": '&#39;',
  }[m]));

  const tanggalId = (value) => {
    if (value === null || value === undefined || value === "") {
      return "-";
    }

    const text = String(value);

    if (/^\d{2}\/\d{2}\/\d{4}/.test(text)) {
      return text;
    }

    const match = text.match(/^(\d{4})-(\d{2})-(\d{2})/);
    if (!match) {
      return text;
    }

    return `${match[3]}/${match[2]}/${match[1]}`;
  };


  const trim = (v) => String(v ?? '').trim();
  const reverseStoreUrl = (id) => c.reverseStoreBaseUrl.replace('__ID__', encodeURIComponent(id));

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
      body.innerHTML = '<tr><td colspan="7" class="text-center text-muted py-4">Belum ada riwayat gaji untuk karyawan ini.</td></tr>';
      return;
    }

    body.innerHTML = rows.map((r, i) => {
      const notesHtml = r.is_reversed && r.reversal_reason
        ? `${esc(r.notes ?? '-')}<div class="small text-danger mt-1">Dibatalkan: ${esc(r.reversal_reason)}</div>`
        : esc(r.notes ?? '-');

      const statusHtml = r.is_reversed
        ? `<span class="badge bg-light-danger text-danger">Dibatalkan</span>${r.reversal_created_at ? `<div class="small text-muted mt-1">${esc(r.reversal_created_at)}</div>` : ''}`
        : '<span class="badge bg-light-success text-success">Aktif</span>';

      const actionHtml = r.is_reversed
        ? '<span class="text-muted">-</span>'
        : `
          <button
            type="button"
            class="btn btn-sm btn-light-danger js-open-employee-payroll-reversal"
            data-payroll-id="${esc(r.id)}"
            data-disbursement-date="${esc(r.disbursement_date)}"
            data-amount-formatted="${esc(r.amount_formatted)}"
          >
            Reversal
          </button>
        `;

      return `
        <tr>
          <td>${(m.page - 1) * m.per_page + i + 1}</td>
          <td>${esc(tanggalId(r.disbursement_date))}</td>
          <td>Rp${esc(r.amount_formatted)}</td>
          <td>${esc(r.mode_label)}</td>
          <td>${notesHtml}</td>
          <td>${statusHtml}</td>
          <td class="text-center">${actionHtml}</td>
        </tr>
      `;
    }).join('');
  };

  const load = async () => {
    const current = ++req;
    body.innerHTML = '<tr><td colspan="7" class="text-center text-muted py-4">Memuat data...</td></tr>';

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
      body.innerHTML = '<tr><td colspan="7" class="text-center text-danger py-4">Gagal memuat riwayat gaji.</td></tr>';
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

  document.addEventListener('click', (e) => {
    const button = e.target.closest('.js-open-employee-payroll-reversal');
    if (!button || !reversalModal || !reversalForm) return;

    const payrollId = trim(button.dataset.payrollId);
    const disbursementDate = trim(button.dataset.disbursementDate) || '-';
    const amountFormatted = trim(button.dataset.amountFormatted) || '0';

    if (payrollId === '') return;

    reversalForm.action = reverseStoreUrl(payrollId);

    if (reversalReason) {
      reversalReason.value = '';
    }

    if (reversalSubtitle) {
      reversalSubtitle.textContent = `${disbursementDate} • Rp${amountFormatted}`;
    }

    reversalModal.show();
  });

  reversalModalEl?.addEventListener('hidden.bs.modal', () => {
    if (reversalForm) {
      reversalForm.action = '#';
    }

    if (reversalReason) {
      reversalReason.value = '';
    }

    if (reversalSubtitle) {
      reversalSubtitle.textContent = 'Isi alasan pembatalan pencairan gaji.';
    }
  });

  load();
})();
