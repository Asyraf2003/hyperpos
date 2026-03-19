(() => {
  const c = window.procurementInvoiceTableConfig;
  if (!c) return;

  const defaults = {
    q: "",
    page: 1,
    sort_by: "shipment_date",
    sort_dir: "desc",
    shipment_date_from: "",
    shipment_date_to: ""
  };

  const allowedSortBy = new Set([
    "shipment_date",
    "due_date",
    "nama_pt_pengirim",
    "grand_total_rupiah",
    "total_paid_rupiah",
    "outstanding_rupiah",
    "receipt_count",
    "total_received_qty"
  ]);
  const allowedSortDir = new Set(["asc", "desc"]);

  const $ = (id) => document.getElementById(id);
  const body = $("procurement-invoice-table-body");
  const pager = $("procurement-invoice-table-pagination");
  const summary = $("procurement-invoice-table-summary");
  const searchForm = $("procurement-search-form");
  const searchInput = $("procurement-search-input");
  const filterForm = $("procurement-filter-form");
  const resetFilter = $("reset-procurement-filter");

  let searchDebounceTimer = null;
  let requestCounter = 0;

  const esc = (v) => String(v ?? "").replace(/[&<>"']/g, (m) => ({
    "&": "&amp;",
    "<": "&lt;",
    ">": "&gt;",
    '"': "&quot;",
    "'": "&#39;"
  }[m]));

  const rupiah = (v) => "Rp " + Number(v || 0).toLocaleString("id-ID");
  const detailUrl = (id) => c.detailBaseUrl.replace("__ID__", encodeURIComponent(id));

  const trimValue = (v) => String(v ?? "").trim();

  const intOrDefault = (v, fallback) => {
    const n = Number.parseInt(String(v ?? ""), 10);
    return Number.isNaN(n) || n < 1 ? fallback : n;
  };

  const stateFromUrl = () => {
    const p = new URLSearchParams(window.location.search);
    const sortBy = trimValue(p.get("sort_by"));
    const sortDir = trimValue(p.get("sort_dir"));

    return {
      q: trimValue(p.get("q")),
      page: intOrDefault(p.get("page"), 1),
      sort_by: allowedSortBy.has(sortBy) ? sortBy : defaults.sort_by,
      sort_dir: allowedSortDir.has(sortDir) ? sortDir : defaults.sort_dir,
      shipment_date_from: trimValue(p.get("shipment_date_from")),
      shipment_date_to: trimValue(p.get("shipment_date_to"))
    };
  };

  const s = stateFromUrl();

  const syncInputsFromState = () => {
    searchInput.value = s.q;
    filterForm.elements["shipment_date_from"].value = s.shipment_date_from;
    filterForm.elements["shipment_date_to"].value = s.shipment_date_to;
  };

  const paramsObject = () => {
    const obj = {
      page: String(s.page),
      per_page: "10",
      sort_by: s.sort_by,
      sort_dir: s.sort_dir
    };

    ["q", "shipment_date_from", "shipment_date_to"].forEach((k) => {
      if (s[k]) obj[k] = s[k];
    });

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
      <td>${esc(r.supplier_invoice_id)}</td>
      <td>${esc(r.nama_pt_pengirim)}</td>
      <td>${esc(r.shipment_date)}</td>
      <td>${esc(r.due_date)}</td>
      <td>${rupiah(r.grand_total_rupiah)}</td>
      <td>${rupiah(r.total_paid_rupiah)}</td>
      <td>${rupiah(r.outstanding_rupiah)}</td>
      <td>${esc(r.receipt_count)}</td>
      <td>${esc(r.total_received_qty)}</td>
      <td class="text-center">
        <a href="${detailUrl(r.supplier_invoice_id)}" class="btn btn-sm btn-outline-secondary">Detail</a>
      </td>
    </tr>
  `;

  const renderRows = (rows, meta) => {
    if (!rows.length) {
      body.innerHTML = `<tr><td colspan="11" class="text-center text-muted py-4">Tidak ada nota supplier yang cocok.</td></tr>`;
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
    summary.textContent = `Total: ${meta.total} nota supplier`;
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

    body.innerHTML = `<tr><td colspan="11" class="text-center text-muted py-4">Memuat data...</td></tr>`;

    const res = await fetch(`${c.endpoint}?${paramsString()}`, {
      headers: { Accept: "application/json" }
    });

    const json = await res.json();

    if (currentRequest !== requestCounter) {
      return;
    }

    if (!res.ok || !json.success) {
      body.innerHTML = `<tr><td colspan="11" class="text-center text-danger py-4">Gagal memuat data.</td></tr>`;
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

  filterForm.addEventListener("submit", (e) => {
    e.preventDefault();

    const f = new FormData(filterForm);
    s.shipment_date_from = trimValue(f.get("shipment_date_from"));
    s.shipment_date_to = trimValue(f.get("shipment_date_to"));
    s.page = 1;
    load();
  });

  resetFilter.addEventListener("click", () => {
    s.shipment_date_from = "";
    s.shipment_date_to = "";
    s.page = 1;
    syncInputsFromState();
    load();
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
        s.sort_dir = key === "shipment_date" ? "desc" : "asc";
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
    s.shipment_date_from = nextState.shipment_date_from;
    s.shipment_date_to = nextState.shipment_date_to;
    load(true);
  });

  syncInputsFromState();
  load(true);
})();
