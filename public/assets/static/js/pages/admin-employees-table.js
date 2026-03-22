(() => {
  const c = window.employeeTableConfig;
  if (!c) return;

  const $ = (id) => document.getElementById(id);
  const form = $('employee-search-form'), q = $('employee-search-input');
  const body = $('employee-table-body'), sum = $('employee-table-summary'), pag = $('employee-table-pagination');
  const allowedSortBy = new Set(['name', 'base_salary', 'pay_period', 'status']);
  const allowedSortDir = new Set(['asc', 'desc']);
  let timer = null, req = 0;

  const esc = (v) => String(v ?? '').replace(/[&<>"']/g, (m) => ({ '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;' }[m]));
  const trim = (v) => String(v ?? '').trim();
  const intOr = (v, f) => { const n = Number.parseInt(String(v ?? ''), 10); return Number.isNaN(n) || n < 1 ? f : n; };
  const detailUrl = (id) => c.detailBaseUrl.replace('__ID__', encodeURIComponent(id));
  const editUrl = (id) => c.editBaseUrl.replace('__ID__', encodeURIComponent(id));

  const stateFromUrl = () => {
    const p = new URLSearchParams(window.location.search);
    const sortBy = trim(p.get('sort_by')), sortDir = trim(p.get('sort_dir'));
    return {
      q: trim(p.get('q')),
      page: intOr(p.get('page'), 1),
      sort_by: allowedSortBy.has(sortBy) ? sortBy : 'name',
      sort_dir: allowedSortDir.has(sortDir) ? sortDir : 'asc',
    };
  };

  const s = stateFromUrl();
  const params = () => {
    const out = { page: String(s.page), per_page: '10', sort_by: s.sort_by, sort_dir: s.sort_dir };
    if (s.q) out.q = s.q;
    return out;
  };
  const paramsString = () => new URLSearchParams(params()).toString();
  const updateUrl = (replace = false) => {
    const url = new URL(window.location.href);
    url.search = paramsString();
    replace ? window.history.replaceState(null, '', url) : window.history.pushState(null, '', url);
  };

  const renderSummary = (m) => { sum.textContent = `Total: ${m.total} karyawan`; };
  const renderSort = () => {
    document.querySelectorAll('[data-sort-indicator]').forEach((n) => {
      const active = n.dataset.sortIndicator === s.sort_by;
      n.textContent = active ? (s.sort_dir === 'asc' ? '↑' : '↓') : '↕';
      n.classList.toggle('text-muted', !active);
    });
  };

  const renderPager = (m) => {
    if (m.last_page <= 1) { pag.innerHTML = ''; return; }
    const start = Math.max(1, m.page - 2), end = Math.min(m.last_page, m.page + 2);
    let html = `<nav><ul class="pagination pagination-primary mb-0">`;
    html += `<li class="page-item ${m.page === 1 ? 'disabled' : ''}"><a class="page-link" href="#" data-page="${m.page - 1}"><i class="bi bi-chevron-left"></i></a></li>`;
    for (let p = start; p <= end; p++) html += `<li class="page-item ${p === m.page ? 'active' : ''}"><a class="page-link" href="#" data-page="${p}">${p}</a></li>`;
    html += `<li class="page-item ${m.page === m.last_page ? 'disabled' : ''}"><a class="page-link" href="#" data-page="${m.page + 1}"><i class="bi bi-chevron-right"></i></a></li></ul></nav>`;
    pag.innerHTML = html;
  };

  const renderRows = (rows, m) => {
    if (!rows.length) {
      body.innerHTML = '<tr><td colspan="7" class="text-center text-muted py-4">Belum ada data karyawan.</td></tr>';
      return;
    }
    body.innerHTML = rows.map((row, i) => `
      <tr>
        <td>${(m.page - 1) * m.per_page + i + 1}</td>
        <td>${esc(row.name)}</td>
        <td>${esc(row.phone ?? '-')}</td>
        <td>Rp${esc(row.base_salary_formatted)}</td>
        <td>${esc(row.pay_period_label)}</td>
        <td>${esc(row.status_label)}</td>
        <td class="text-center">
          <div class="d-inline-flex gap-1">
            <a href="${detailUrl(row.id)}" class="btn btn-sm btn-light-primary">Detail</a>
            <a href="${editUrl(row.id)}" class="btn btn-sm btn-light-secondary">Edit</a>
          </div>
        </td>
      </tr>`).join('');
  };

  const load = async (replace = false) => {
    const current = ++req;
    body.innerHTML = '<tr><td colspan="7" class="text-center text-muted py-4">Memuat data...</td></tr>';

    const res = await fetch(`${c.endpoint}?${paramsString()}`, { headers: { Accept: 'application/json' } });
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
    q.value = s.q;
    updateUrl(replace);
  };

  form?.addEventListener('submit', (e) => {
    e.preventDefault();
    const value = trim(q.value);
    if (value.length === 0) { s.q = ''; s.page = 1; load(); return; }
    if (value.length >= 2) { s.q = value; s.page = 1; load(); }
  });

  q?.addEventListener('input', () => {
    const value = trim(q.value);
    clearTimeout(timer);
    if (value.length === 0) { s.q = ''; s.page = 1; timer = setTimeout(() => load(), 250); return; }
    if (value.length < 2) return;
    timer = setTimeout(() => { s.q = value; s.page = 1; load(); }, 300);
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
