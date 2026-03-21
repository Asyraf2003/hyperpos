(() => {
  const c = window.supplierTableConfig;
  if (!c) return;

  const defaults = {
    q: "",
    page: 1,
    sort_by: "nama_pt_pengirim",
    sort_dir: "asc"
  };

  const allowedSortBy = new Set([
    "nama_pt_pengirim",
    "invoice_count",
    "outstanding_rupiah",
    "invoice_unpaid_count",
    "last_shipment_date"
  ]);
  const allowedSortDir = new Set(["asc", "desc"]);

  const $ = (id) => document.getElementById(id);
  const body = $("supplier-table-body");
  const pager = $("supplier-table-pagination");
  const summary = $("supplier-table-summary");
  const searchForm = $("supplier-search-form");
  const searchInput = $("supplier-search-input");

  let searchDebounceTimer = null;
  let requestCounter = 0;

  const esc = (v) => String(v ?? "").replace(/[&<>\"']/g, (m) => ({
    "&": "&amp;",
    "<": "&lt;",
    ">": "&gt;",
    '"': "&quot;",
    "'": "&#39;"
  }[m]));

  const rupiah = (v) => "Rp " + Number(v || 0).toLocaleString("id-ID");
  const angka = (v) => Number(v || 0).toLocaleString("id-ID");

  const tanggalId = (v) => {
    if (!v) {
      return "-";
    }

    const date = new Date(`${v}T00:00:00`);

    if (Number.isNaN(date.getTime())) {
      return esc(v);
    }

    return new Intl.DateTimeFormat("id-ID", {
      day: "2-digit",
      month: "short",
      year: "numeric"
    }).format(date);
  };

  const trimValue = (v) => String(v ?? "").trim();

  const intOrDefault = (v, fallback) => {
    const n = Number.parseInt(String(v ?? ""), 10);
    return Number.isNaN(n) || n < 1 ? fallback : n;
  };

  const editUrl = (supplierId) => `${String(c.editBaseUrl || "").replace(/\/$/, "")}/${encodeURIComponent(supplierId)}/edit`;

  const stateFromUrl = () => {
    const p = new URLSearchParams(window.location.search);

    const sortBy = trimValue(p.get("sort_by"));
    const sortDir = trimValue(p.get("sort_dir"));

    return {
      q: trimValue(p.get("q")),
      page: intOrDefault(p.get("page"), 1),
      sort_by: allowedSortBy.has(sortBy) ? sortBy : defaults.sort_by,
      sort_dir: allowedSortDir.has(sortDir) ? sortDir : defaults.sort_dir
    };
  };

  const s = stateFromUrl();

  const syncInputsFromState = () => {
    searchInput.value = s.q;
  };

  const paramsObject = () => {
    const obj = {
      page: String(s.page),
      per_page: "10",
      sort_by: s.sort_by,
      sort_dir: s.sort_dir
    };

    if (s.q) obj.q = s.q;

    return obj;
  };

  const paramsString = () => new URLSearchParams(paramsObject()).toString();

  const updateUrl = (replace = false) => {
    const url = new URL(window.location.href);
    url.search = paramsString();

    if (replace) {
      window.history.replaceState(null, "", url);
      return;
    }

    window.history.pushState(null, "", url);
  };

  const rowHtml = (r, i, meta) => `
    <tr>
      <td>${(meta.page - 1) * meta.per_page + i + 1}</td>
      <td>${esc(r.nama_pt_pengirim)}</td>
      <td class="text-end">${angka(r.invoice_count)}</td>
      <td class="text-end">${rupiah(r.outstanding_rupiah)}</td>
      <td class="text-end">${angka(r.invoice_unpaid_count)}</td>
      <td>${tanggalId(r.last_shipment_date)}</td>
      <td>
        <a href="${editUrl(r.id)}" class="btn btn-sm btn-outline-primary">
          Edit
        </a>
      </td>
    </tr>
  `;

  const renderRows = (rows, meta) => {
    if (!rows.length) {
      body.innerHTML = `<tr><td colspan="7" class="text-center text-muted py-4">Tidak ada supplier yang cocok.</td></tr>`;
      return;
    }

    body.innerHTML = rows.map((r, i) => rowHtml(r, i, meta)).join("");
  };

  const renderPager = (meta) => {
    if (meta.last_page <= 1) {
      pager.innerHTML = "";
      return;
    }

    const start = Math.max(1, meta.page - 2);
    const end = Math.min(meta.last_page, meta.page + 2);

    let html = `<nav><ul class="pagination pagination-primary mb-0">`;
    html += `<li class="page-item ${meta.page === 1 ? "disabled" : ""}"><a class="page-link" href="#" data-page="${meta.page - 1}"><i class="bi bi-chevron-left"></i></a></li>`;

    for (let p = start; p <= end; p++) {
      html += `<li class="page-item ${p === meta.page ? "active" : ""}"><a class="page-link" href="#" data-page="${p}">${p}</a></li>`;
    }

    html += `<li class="page-item ${meta.page === meta.last_page ? "disabled" : ""}"><a class="page-link" href="#" data-page="${meta.page + 1}"><i class="bi bi-chevron-right"></i></a></li></ul></nav>`;
    pager.innerHTML = html;
  };

  const renderSummary = (meta) => {
    summary.textContent = `Total: ${meta.total} supplier`;
  };

  const renderSortIndicators = () => {
    document.querySelectorAll("[data-sort-indicator]").forEach((node) => {
      const key = node.dataset.sortIndicator;

      if (key === s.sort_by) {
        node.textContent = s.sort_dir === "asc" ? "↑" : "↓";
        node.classList.remove("text-muted");
      } else {
        node.textContent = "↕";
        node.classList.add("text-muted");
      }
    });
  };

  const load = async (replaceUrl = false) => {
    const currentRequest = ++requestCounter;

    body.innerHTML = `<tr><td colspan="7" class="text-center text-muted py-4">Memuat data...</td></tr>`;

    const res = await fetch(`${c.endpoint}?${paramsString()}`, {
      headers: { Accept: "application/json" }
    });

    const json = await res.json();

    if (currentRequest !== requestCounter) {
      return;
    }

    if (!res.ok || !json.success) {
      body.innerHTML = `<tr><td colspan="7" class="text-center text-danger py-4">Gagal memuat data.</td></tr>`;
      return;
    }

    renderRows(json.data.rows || [], json.data.meta || {});
    renderSummary(json.data.meta || {});
    renderPager(json.data.meta || {});
    renderSortIndicators();
    syncInputsFromState();
    updateUrl(replaceUrl);
  };

  searchForm.addEventListener("submit", (e) => {
    e.preventDefault();

    const value = trimValue(searchInput.value);

    if (value.length === 0) {
      s.q = "";
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

  searchInput.addEventListener("input", () => {
    const value = trimValue(searchInput.value);

    clearTimeout(searchDebounceTimer);

    if (value.length === 0) {
      s.q = "";
      s.page = 1;
      searchDebounceTimer = setTimeout(() => load(), 250);
      return;
    }

    if (value.length < 2) {
      return;
    }

    searchDebounceTimer = setTimeout(() => {
      s.q = value;
      s.page = 1;
      load();
    }, 300);
  });

  document.querySelectorAll("[data-sort-by]").forEach((button) => {
    button.addEventListener("click", () => {
      const key = button.dataset.sortBy;

      if (!allowedSortBy.has(key)) {
        return;
      }

      if (s.sort_by === key) {
        s.sort_dir = s.sort_dir === "asc" ? "desc" : "asc";
      } else {
        s.sort_by = key;
        s.sort_dir = key === "last_shipment_date" ? "desc" : "asc";
      }

      s.page = 1;
      load();
    });
  });

  pager.addEventListener("click", (e) => {
    const link = e.target.closest("[data-page]");
    if (!link) return;

    e.preventDefault();

    const nextPage = intOrDefault(link.dataset.page, s.page);
    if (nextPage === s.page) return;

    s.page = nextPage;
    load();
  });

  window.addEventListener("popstate", () => {
    const nextState = stateFromUrl();
    s.q = nextState.q;
    s.page = nextState.page;
    s.sort_by = nextState.sort_by;
    s.sort_dir = nextState.sort_dir;
    load(true);
  });

  syncInputsFromState();
  load(true);
})();
