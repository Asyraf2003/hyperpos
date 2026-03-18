(() => {
  const c = window.productTableConfig; if (!c) return;
  const s = { q: "", page: 1, sort_by: "nama_barang", sort_dir: "asc", merek: "", ukuran_min: "", ukuran_max: "", harga_min: "", harga_max: "" };
  const $ = (id) => document.getElementById(id), body = $("product-table-body"), pager = $("product-table-pagination"), summary = $("product-table-summary");
  const searchForm = $("product-search-form"), searchInput = $("product-search-input"), filterForm = $("product-filter-form");
  const drawer = $("product-filter-drawer"), backdrop = $("product-filter-backdrop");
  const esc = (v) => String(v ?? "").replace(/[&<>"']/g, (m) => ({ "&": "&amp;", "<": "&lt;", ">": "&gt;", '"': "&quot;", "'": "&#39;" }[m]));
  const rupiah = (v) => "Rp " + Number(v || 0).toLocaleString("id-ID");
  const editUrl = (id) => c.editBaseUrl.replace("__ID__", encodeURIComponent(id));
  const drawOpen = (open) => { drawer.classList.toggle("d-none", !open); backdrop.classList.toggle("d-none", !open); };
  const params = () => {
    const p = new URLSearchParams({ page: String(s.page), per_page: "10", sort_by: s.sort_by, sort_dir: s.sort_dir });
    ["q", "merek", "ukuran_min", "ukuran_max", "harga_min", "harga_max"].forEach((k) => { if (s[k]) p.set(k, s[k]); });
    return p.toString();
  };
  const rowHtml = (r, i, meta) => `<tr><td>${(meta.page - 1) * meta.per_page + i + 1}</td><td>${esc(r.kode_barang || "-")}</td><td>${esc(r.nama_barang)}</td><td>${esc(r.merek)}</td><td>${esc(r.ukuran ?? "-")}</td><td>${rupiah(r.harga_jual)}</td><td>${esc(r.stok_saat_ini)}</td><td class="text-center"><a href="${editUrl(r.id)}" class="btn btn-sm btn-outline-secondary">Edit</a></td></tr>`;
  const renderRows = (rows, meta) => {
    if (!rows.length) return body.innerHTML = `<tr><td colspan="8" class="text-center text-muted py-4">Tidak ada product yang cocok.</td></tr>`;
    body.innerHTML = rows.map((r, i) => rowHtml(r, i, meta)).join("");
  };
  const renderPager = (meta) => {
    if (meta.last_page <= 1) return pager.innerHTML = "";
    const start = Math.max(1, meta.page - 2), end = Math.min(meta.last_page, meta.page + 2);
    let html = `<nav><ul class="pagination pagination-primary mb-0">`;
    html += `<li class="page-item ${meta.page === 1 ? "disabled" : ""}"><a class="page-link" href="#" data-page="${meta.page - 1}"><i class="bi bi-chevron-left"></i></a></li>`;
    for (let p = start; p <= end; p++) html += `<li class="page-item ${p === meta.page ? "active" : ""}"><a class="page-link" href="#" data-page="${p}">${p}</a></li>`;
    html += `<li class="page-item ${meta.page === meta.last_page ? "disabled" : ""}"><a class="page-link" href="#" data-page="${meta.page + 1}"><i class="bi bi-chevron-right"></i></a></li></ul></nav>`;
    pager.innerHTML = html;
  };
  const renderSummary = (meta) => { summary.textContent = `Total: ${meta.total} product`; };
  const load = async () => {
    body.innerHTML = `<tr><td colspan="8" class="text-center text-muted py-4">Memuat data...</td></tr>`;
    const res = await fetch(`${c.endpoint}?${params()}`, { headers: { Accept: "application/json" } });
    const json = await res.json();
    if (!res.ok || !json.success) return body.innerHTML = `<tr><td colspan="8" class="text-center text-danger py-4">Gagal memuat data.</td></tr>`;
    renderRows(json.data.rows || [], json.data.meta || {}); renderSummary(json.data.meta || {}); renderPager(json.data.meta || {});
  };
  searchForm.addEventListener("submit", (e) => { e.preventDefault(); s.q = searchInput.value.trim(); s.page = 1; load(); });
  $("open-product-filter").addEventListener("click", () => drawOpen(true));
  $("close-product-filter").addEventListener("click", () => drawOpen(false));
  backdrop.addEventListener("click", () => drawOpen(false));
  filterForm.addEventListener("submit", (e) => {
    e.preventDefault(); const f = new FormData(filterForm);
    ["merek", "ukuran_min", "ukuran_max", "harga_min", "harga_max"].forEach((k) => s[k] = String(f.get(k) || "").trim());
    s.page = 1; drawOpen(false); load();
  });
  $("reset-product-filter").addEventListener("click", () => {
    filterForm.reset(); ["merek", "ukuran_min", "ukuran_max", "harga_min", "harga_max"].forEach((k) => s[k] = "");
    s.page = 1; drawOpen(false); load();
  });
  document.querySelector("#product-table thead").addEventListener("click", (e) => {
    const btn = e.target.closest("[data-sort-by]"); if (!btn) return;
    const key = btn.dataset.sortBy; s.sort_dir = s.sort_by === key && s.sort_dir === "asc" ? "desc" : "asc"; s.sort_by = key; s.page = 1; load();
  });
  pager.addEventListener("click", (e) => {
    const link = e.target.closest("[data-page]"); if (!link || link.parentElement.classList.contains("disabled")) return;
    e.preventDefault(); s.page = Number(link.dataset.page || 1); load();
  });
  load();
})();
