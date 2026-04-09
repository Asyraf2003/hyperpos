(() => {
  const c = window.procurementInvoiceTableConfig;
  if (!c) return;

  const defaults = {
    q: "",
    nama_pt: "",
    payment_status: "all",
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
  const allowedPaymentStatus = new Set(["all", "outstanding", "paid"]);

  const $ = (id) => document.getElementById(id);
  const body = $("procurement-invoice-table-body");
  const pager = $("procurement-invoice-table-pagination");
  const summary = $("procurement-invoice-table-summary");
  const searchForm = $("procurement-search-form");
  const searchInput = $("procurement-search-input");
  const filterForm = $("procurement-filter-form");
  const resetFilter = $("reset-procurement-filter");
  const drawer = $("procurement-filter-drawer");
  const backdrop = $("procurement-filter-backdrop");

  const actionModalElement = $("procurement-action-modal");
  const actionModalSubtitle = $("procurement-action-modal-subtitle");
  const actionDetailLink = $("procurement-action-detail-link");
  const actionPaymentLink = $("procurement-action-payment-link");
  const actionPaymentTitle = $("procurement-action-payment-title");
  const actionPaymentDescription = $("procurement-action-payment-description");
  const actionProofLink = $("procurement-action-proof-link");
  const actionProofTitle = $("procurement-action-proof-title");
  const actionProofDescription = $("procurement-action-proof-description");

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

  const trimValue = (v) => String(v ?? "").trim();
  const rupiah = (v) => "Rp " + Number(v || 0).toLocaleString("id-ID");
  const detailUrl = (id) => c.detailBaseUrl.replace("__ID__", encodeURIComponent(id));
  const paymentSectionUrl = (id) => `${detailUrl(id)}#payment-form-section`;
  const proofSectionUrl = (id) => `${detailUrl(id)}#payment-proof-section`;

  const intOrDefault = (v, fallback) => {
    const n = Number.parseInt(String(v ?? ""), 10);
    return Number.isNaN(n) || n < 1 ? fallback : n;
  };

  const supplierCellHtml = (row) => {
    const currentName = trimValue(row.supplier_nama_pt_pengirim_current);
    const snapshotName = trimValue(row.supplier_nama_pt_pengirim_snapshot);

    const primary = currentName || snapshotName || "-";
    const showSnapshot = snapshotName && currentName && snapshotName !== currentName;

    return `
      <div class="fw-semibold">${esc(primary)}</div>
      ${showSnapshot ? `<div class="small text-muted">saat nota dibuat: ${esc(snapshotName)}</div>` : ""}
    `;
  };

  const invoiceCellHtml = (row) => {
    const nomorFaktur = trimValue(row.nomor_faktur);
    return `<div class="fw-semibold">${esc(nomorFaktur || "-")}</div>`;
  };

  const stateFromUrl = () => {
    const p = new URLSearchParams(window.location.search);
    const sortBy = trimValue(p.get("sort_by"));
    const sortDir = trimValue(p.get("sort_dir"));
    const paymentStatus = trimValue(p.get("payment_status"));

    return {
      q: trimValue(p.get("q")),
      nama_pt: trimValue(p.get("nama_pt")),
      payment_status: allowedPaymentStatus.has(paymentStatus) ? paymentStatus : defaults.payment_status,
      page: intOrDefault(p.get("page"), 1),
      sort_by: allowedSortBy.has(sortBy) ? sortBy : defaults.sort_by,
      sort_dir: allowedSortDir.has(sortDir) ? sortDir : defaults.sort_dir,
      shipment_date_from: trimValue(p.get("shipment_date_from")),
      shipment_date_to: trimValue(p.get("shipment_date_to"))
    };
  };

  const s = stateFromUrl();

  const syncInputsFromState = () => {
    if (searchInput) {
      searchInput.value = s.q;
    }

    if (filterForm?.elements["nama_pt"]) {
      filterForm.elements["nama_pt"].value = s.nama_pt;
    }

    if (filterForm?.elements["payment_status"]) {
      filterForm.elements["payment_status"].value = s.payment_status;
    }

    if (filterForm?.elements["shipment_date_from"]) {
      filterForm.elements["shipment_date_from"].value = s.shipment_date_from;
    }

    if (filterForm?.elements["shipment_date_to"]) {
      filterForm.elements["shipment_date_to"].value = s.shipment_date_to;
    }

    window.AdminDateInput?.refreshWithin(filterForm);
  };

  const paramsObject = () => {
    const obj = {
      page: String(s.page),
      per_page: "10",
      sort_by: s.sort_by,
      sort_dir: s.sort_dir,
      payment_status: s.payment_status
    };

    ["q", "nama_pt", "shipment_date_from", "shipment_date_to"].forEach((k) => {
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
    if (drawer) drawer.classList.toggle("d-none", !open);
    if (backdrop) backdrop.classList.toggle("d-none", !open);
  };

  const configureActionModal = (row) => {
    if (
      !actionModalSubtitle ||
      !actionDetailLink ||
      !actionPaymentLink ||
      !actionPaymentTitle ||
      !actionPaymentDescription ||
      !actionProofLink ||
      !actionProofTitle ||
      !actionProofDescription
    ) {
      return;
    }

    const supplierName = trimValue(row.supplier_nama_pt_pengirim_current)
      || trimValue(row.supplier_nama_pt_pengirim_snapshot)
      || "-";
    const nomorFaktur = trimValue(row.nomor_faktur) || "-";

    actionModalSubtitle.textContent = `${nomorFaktur} • ${supplierName}`;
    actionDetailLink.href = detailUrl(row.supplier_invoice_id);

    if (row.can_record_payment) {
      actionPaymentLink.href = paymentSectionUrl(row.supplier_invoice_id);
      actionPaymentTitle.textContent = "Bayar";
      actionPaymentDescription.textContent = "Buka bagian pembayaran pada detail nota.";
    } else {
      actionPaymentLink.href = detailUrl(row.supplier_invoice_id);
      actionPaymentTitle.textContent = "Lihat Pembayaran";
      actionPaymentDescription.textContent = "Nota ini sudah lunas. Buka detail untuk melihat riwayat pembayaran.";
    }

    if (row.payment_count < 1) {
      actionProofLink.href = paymentSectionUrl(row.supplier_invoice_id);
      actionProofTitle.textContent = "Catat Pembayaran Dulu";
      actionProofDescription.textContent = "Bukti bayar baru bisa diunggah setelah ada pembayaran.";
    } else if (row.has_uploaded_proof) {
      actionProofLink.href = proofSectionUrl(row.supplier_invoice_id);
      actionProofTitle.textContent = "Lihat Bukti Bayar";
      actionProofDescription.textContent = "Buka riwayat bukti pembayaran pada detail nota.";
    } else {
      actionProofLink.href = proofSectionUrl(row.supplier_invoice_id);
      actionProofTitle.textContent = "Unggah Bukti Bayar";
      actionProofDescription.textContent = "Buka bagian unggah bukti pada detail nota.";
    }
  };

  const rowHtml = (row, index, meta) => `
    <tr>
      <td>${(meta.page - 1) * meta.per_page + index + 1}</td>
      <td>${invoiceCellHtml(row)}</td>
      <td>${supplierCellHtml(row)}</td>
      <td>${esc(row.shipment_date)}</td>
      <td>${esc(row.due_date)}</td>
      <td>${rupiah(row.grand_total_rupiah)}</td>
      <td>${rupiah(row.total_paid_rupiah)}</td>
      <td>${rupiah(row.outstanding_rupiah)}</td>
      <td>${esc(row.receipt_count)}</td>
      <td>${esc(row.total_received_qty)}</td>
      <td class="text-center">
        <button
          type="button"
          class="btn btn-sm btn-outline-primary"
          data-procurement-action="open"
          data-row='${JSON.stringify(row).replace(/'/g, "&apos;")}'
        >
          Aksi
        </button>
      </td>
    </tr>
  `;

  const renderRows = (rows, meta) => {
    if (!rows.length) {
      body.innerHTML = `<tr><td colspan="11" class="text-center text-muted py-4">Tidak ada nota supplier yang cocok.</td></tr>`;
      return;
    }

    body.innerHTML = rows.map((row, index) => rowHtml(row, index, meta)).join("");
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

  $("open-procurement-filter")?.addEventListener("click", () => drawOpen(true));
  $("close-procurement-filter")?.addEventListener("click", () => drawOpen(false));
  backdrop?.addEventListener("click", () => drawOpen(false));

  searchForm?.addEventListener("submit", (e) => {
    e.preventDefault();
    const value = trimValue(searchInput?.value);

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

  searchInput?.addEventListener("input", () => {
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

  filterForm?.addEventListener("submit", (e) => {
    e.preventDefault();

    const f = new FormData(filterForm);
    const paymentStatus = trimValue(f.get("payment_status"));

    s.nama_pt = trimValue(f.get("nama_pt"));
    s.payment_status = allowedPaymentStatus.has(paymentStatus) ? paymentStatus : "all";
    s.shipment_date_from = trimValue(f.get("shipment_date_from"));
    s.shipment_date_to = trimValue(f.get("shipment_date_to"));
    s.page = 1;

    drawOpen(false);
    load();
  });

  resetFilter?.addEventListener("click", () => {
    s.nama_pt = "";
    s.payment_status = "all";
    s.shipment_date_from = "";
    s.shipment_date_to = "";
    s.page = 1;
    syncInputsFromState();
    drawOpen(false);
    load();
  });

  document.querySelector("#procurement-invoice-table thead")?.addEventListener("click", (e) => {
    const button = e.target.closest("[data-sort-by]");
    if (!button) return;

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

  pager?.addEventListener("click", (e) => {
    const link = e.target.closest("[data-page]");
    if (!link) return;

    e.preventDefault();

    const nextPage = intOrDefault(link.dataset.page, s.page);
    if (nextPage === s.page) {
      return;
    }

    s.page = nextPage;
    load();
  });

  body?.addEventListener("click", (e) => {
    const button = e.target.closest("[data-procurement-action='open']");
    if (!button) return;

    const raw = button.getAttribute("data-row");
    if (!raw) return;

    const row = JSON.parse(raw.replace(/&apos;/g, "'"));
    configureActionModal(row);

    if (actionModal) {
      actionModal.show();
      return;
    }

    window.location.assign(detailUrl(row.supplier_invoice_id));
  });

  window.addEventListener("popstate", () => {
    Object.assign(s, stateFromUrl());
    load(true);
  });

  syncInputsFromState();
  renderSortIndicators();
  load(true);
})();
