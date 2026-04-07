(() => {
  const c = window.productTableConfig;
  if (!c) return;

  const defaults = {
    q: "",
    page: 1,
    sort_by: "nama_barang",
    sort_dir: "asc",
    merek: "",
    ukuran_min: "",
    ukuran_max: "",
    harga_min: "",
    harga_max: ""
  };

  const allowedSortBy = new Set(["nama_barang", "merek", "ukuran", "harga_jual", "stok_saat_ini"]);
  const allowedSortDir = new Set(["asc", "desc"]);

  const $ = (id) => document.getElementById(id);
  const body = $("product-table-body");
  const pager = $("product-table-pagination");
  const summary = $("product-table-summary");
  const searchForm = $("product-search-form");
  const searchInput = $("product-search-input");
  const filterForm = $("product-filter-form");
  const drawer = $("product-filter-drawer");
  const backdrop = $("product-filter-backdrop");

  const actionModalElement = $("product-action-modal");
  const actionModalSubtitle = $("product-action-modal-subtitle");
  const actionEditLink = $("product-action-edit-link");
  const actionStockLink = $("product-action-stock-link");
  const actionDeleteForm = $("product-action-delete-form");

  const actionModal = actionModalElement && window.bootstrap && window.bootstrap.Modal
    ? new window.bootstrap.Modal(actionModalElement)
    : null;

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
  const editUrl = (id) => c.editBaseUrl.replace("__ID__", encodeURIComponent(id));
  const deleteUrl = (id) => c.deleteBaseUrl.replace("__ID__", encodeURIComponent(id));
  const editIdentityUrl = (id) => `${editUrl(id)}${c.editIdentityAnchor || ""}`;
  const stockAdjustmentUrl = (id) => `${editUrl(id)}${c.stockAdjustmentAnchor || ""}`;

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
      merek: trimValue(p.get("merek")),
      ukuran_min: trimValue(p.get("ukuran_min")),
      ukuran_max: trimValue(p.get("ukuran_max")),
      harga_min: trimValue(p.get("harga_min")),
      harga_max: trimValue(p.get("harga_max"))
    };
  };

  const s = stateFromUrl();

  const syncInputsFromState = () => {
    searchInput.value = s.q;
    filterForm.elements["merek"].value = s.merek;
    filterForm.elements["ukuran_min"].value = s.ukuran_min;
    filterForm.elements["ukuran_max"].value = s.ukuran_max;
    filterForm.elements["harga_min"].value = s.harga_min;
    filterForm.elements["harga_max"].value = s.harga_max;
  };

  const paramsObject = () => {
    const obj = {
      page: String(s.page),
      per_page: "10",
      sort_by: s.sort_by,
      sort_dir: s.sort_dir
    };

    ["q", "merek", "ukuran_min", "ukuran_max", "harga_min", "harga_max"].forEach((k) => {
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

  const drawOpen = (open) => {
    drawer.classList.toggle("d-none", !open);
    backdrop.classList.toggle("d-none", !open);
  };

  const configureActionModal = (product) => {
    if (!actionEditLink || !actionStockLink || !actionDeleteForm || !actionModalSubtitle) {
      return;
    }

    actionModalSubtitle.textContent = `${product.nama_barang} • ${product.kode_barang || "-"}`;
    actionEditLink.href = editIdentityUrl(product.id);
    actionStockLink.href = stockAdjustmentUrl(product.id);
    actionDeleteForm.action = deleteUrl(product.id);
  };

  const rowHtml = (r, i, meta) => `
    <tr>
      <td>${(meta.page - 1) * meta.per_page + i + 1}</td>
      <td>${esc(r.kode_barang || "-")}</td>
      <td>${esc(r.nama_barang)}</td>
      <td>${esc(r.merek)}</td>
      <td>${esc(r.ukuran ?? "-")}</td>
      <td>${rupiah(r.harga_jual)}</td>
      <td>${esc(r.stok_saat_ini)}</td>
      <td class="text-center">
        <button
          type="button"
          class="btn btn-sm btn-outline-secondary"
          data-product-action="open"
          data-product-id="${esc(r.id)}"
          data-product-kode="${esc(r.kode_barang || "")}"
          data-product-nama="${esc(r.nama_barang)}"
        >
          Aksi
        </button>
      </td>
    </tr>
  `;

  const renderRows = (rows, meta) => {
    if (!rows.length) {
      body.innerHTML = `<tr><td colspan="8" class="text-center text-muted py-4">Tidak ada product yang cocok.</td></tr>`;
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
    summary.textContent = `Total: ${meta.total} product`;
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

    body.innerHTML = `<tr><td colspan="8" class="text-center text-muted py-4">Memuat data...</td></tr>`;

    const res = await fetch(`${c.endpoint}?${paramsString()}`, {
      headers: { Accept: "application/json" }
    });

    const json = await res.json();

    if (currentRequest !== requestCounter) {
      return;
    }

    if (!res.ok || !json.success) {
      body.innerHTML = `<tr><td colspan="8" class="text-center text-danger py-4">Gagal memuat data.</td></tr>`;
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

  $("open-product-filter").addEventListener("click", () => drawOpen(true));
  $("close-product-filter").addEventListener("click", () => drawOpen(false));
  backdrop.addEventListener("click", () => drawOpen(false));

  filterForm.addEventListener("submit", (e) => {
    e.preventDefault();

    const f = new FormData(filterForm);

    ["merek", "ukuran_min", "ukuran_max", "harga_min", "harga_max"].forEach((k) => {
      s[k] = trimValue(f.get(k));
    });

    s.page = 1;
    drawOpen(false);
    load();
  });

  $("reset-product-filter").addEventListener("click", () => {
    filterForm.reset();

    ["merek", "ukuran_min", "ukuran_max", "harga_min", "harga_max"].forEach((k) => {
      s[k] = "";
    });

    s.page = 1;
    drawOpen(false);
    load();
  });

  document.querySelector("#product-table thead").addEventListener("click", (e) => {
    const btn = e.target.closest("[data-sort-by]");
    if (!btn) return;

    const key = btn.dataset.sortBy;
    s.sort_dir = s.sort_by === key && s.sort_dir === "asc" ? "desc" : "asc";
    s.sort_by = key;
    s.page = 1;
    load();
  });

  body.addEventListener("click", (e) => {
    const btn = e.target.closest("[data-product-action='open']");
    if (!btn) return;

    const product = {
      id: btn.dataset.productId,
      kode_barang: btn.dataset.productKode || "",
      nama_barang: btn.dataset.productNama || ""
    };

    configureActionModal(product);

    if (actionModal) {
      actionModal.show();
      return;
    }

    window.location.href = editIdentityUrl(product.id);
  });

  pager.addEventListener("click", (e) => {
    const link = e.target.closest("[data-page]");
    if (!link || link.parentElement.classList.contains("disabled")) return;

    e.preventDefault();
    s.page = Number(link.dataset.page || 1);
    load();
  });

  window.addEventListener("popstate", () => {
    Object.assign(s, stateFromUrl());
    syncInputsFromState();
    renderSortIndicators();
    load(true);
  });

  syncInputsFromState();
  renderSortIndicators();
  load(true);
})();
