(() => {
  const cfg = window.employeeTableConfig;
  if (!cfg) return;
  const el = {
    q: document.getElementById('employee-search-input'),
    body: document.getElementById('employee-table-body'),
    sum: document.getElementById('employee-table-summary'),
    pag: document.getElementById('employee-table-pagination'),
  };
  const s = { q: '', page: 1, sort_by: 'name', sort_dir: 'asc' };
  let timer = null;

  const fetchRows = async () => {
    const url = new URL(cfg.endpoint, window.location.origin);
    Object.entries(s).forEach(([k, v]) => url.searchParams.set(k, String(v)));
    const res = await fetch(url, { headers: { Accept: 'application/json' } });
    const payload = await res.json();
    render(payload.data);
  };

  const render = ({ rows, meta }) => {
    el.sum.textContent = `Total: ${meta.total}`;
    el.body.innerHTML = rows.length ? rows.map((row, i) => `
      <tr>
        <td>${(meta.page - 1) * meta.per_page + i + 1}</td>
        <td>${row.name}</td><td>${row.phone ?? '-'}</td>
        <td>Rp${row.base_salary_formatted}</td>
        <td>${row.pay_period_label}</td><td>${row.status_label}</td>
        <td class="text-center"><a href="${cfg.editBaseUrl.replace('__ID__', row.id)}" class="btn btn-sm btn-light-primary">Edit</a></td>
      </tr>`).join('') : '<tr><td colspan="7" class="text-center text-muted py-4">Belum ada data karyawan.</td></tr>';
    el.pag.innerHTML = Array.from({ length: meta.last_page }, (_, i) => i + 1).map(page => `
      <button type="button" class="btn btn-sm ${page === meta.page ? 'btn-primary' : 'btn-light'} me-1" data-page="${page}">${page}</button>`).join('');
    document.querySelectorAll('[data-sort-indicator]').forEach((n) => n.textContent = '↕');
    const active = document.querySelector(`[data-sort-indicator="${meta.sort_by}"]`);
    if (active) active.textContent = meta.sort_dir === 'asc' ? '↑' : '↓';
  };

  el.q?.addEventListener('input', (e) => {
    clearTimeout(timer);
    timer = setTimeout(() => { s.q = e.target.value.trim(); s.page = 1; fetchRows(); }, 300);
  });

  document.querySelectorAll('[data-sort-by]').forEach((btn) => btn.addEventListener('click', () => {
    const sortBy = btn.dataset.sortBy;
    s.sort_dir = s.sort_by === sortBy && s.sort_dir === 'asc' ? 'desc' : 'asc';
    s.sort_by = sortBy;
    fetchRows();
  }));

  el.pag?.addEventListener('click', (e) => {
    const btn = e.target.closest('[data-page]');
    if (!btn) return;
    s.page = Number(btn.dataset.page);
    fetchRows();
  });

  fetchRows();
})();
