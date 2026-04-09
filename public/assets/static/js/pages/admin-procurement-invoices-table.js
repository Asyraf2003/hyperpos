(() => {
  const c = window.procurementInvoiceTableConfig;
  if (!c) return;

  const defaults = {
    q: "",
    nomor_faktur: "",
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
  const activeFilters = $("procurement-active-filters");
  const activeFilterChips = $("procurement-active-filter-chips");
  const resetAllFilters = $("procurement-reset-all-filters");
  const searchForm = $("procurement-search-form");
  const searchInput = $("procurement-search-input");
  const filterForm = $("procurement-filter-form");
  const resetFilter = $("reset-procurement-filter");
  const drawer = $("procurement-filter-drawer");
  const backdrop = $("procurement-filter-backdrop");

  const actionModalElement = $("procurement-action-modal");
  const actionModalSubtitle = $("procurement-action-modal-subtitle");
  const actionDetailLink = $("procurement-action-detail-link");
  const actionPaymentButton = $("procurement-action-payment-link");
  const actionPaymentTitle = $("procurement-action-payment-title");
  const actionPaymentDescription = $("procurement-action-payment-description");
  const actionProofCol = $("procurement-action-proof-col");
  const actionProofLink = $("procurement-action-proof-link");
  const actionProofTitle = $("procurement-action-proof-title");
  const actionProofDescription = $("procurement-action-proof-description");
  const actionEditLink = $("procurement-action-edit-link");
  const actionEditTitle = $("procurement-action-edit-title");
  const actionEditDescription = $("procurement-action-edit-description");

  const setLinkDisabledState = (node, disabled) => {
    if (!node) return;

    node.classList.toggle("disabled", disabled);
    node.classList.toggle("btn-outline-secondary", disabled);
    node.classList.toggle("text-muted", disabled);
    node.classList.toggle("border-secondary", disabled);

    if (!disabled) {
      node.classList.remove("btn-outline-secondary", "text-muted", "border-secondary");
      if (!node.classList.contains("btn-outline-primary")) {
        node.classList.add("btn-outline-primary");
      }
    }

    node.setAttribute("aria-disabled", disabled ? "true" : "false");
    node.tabIndex = disabled ? -1 : 0;
  };

  const paymentModalElement = $("procurement-payment-modal");
  const paymentModalSubtitle = $("procurement-payment-modal-subtitle");
  const paymentForm = $("procurement-payment-form");
  const paymentInvoiceIdInput = $("procurement-payment-invoice-id");
  const paymentDateInput = $("procurement-payment-date");
  const paymentAmountRaw = $("procurement-payment-amount");
  const paymentAmountDisplay = $("procurement-payment-amount-display");
  const paymentAmountHelp = $("procurement-payment-amount-help");

  const actionModal = actionModalElement && window.bootstrap && window.bootstrap.Modal
    ? new window.bootstrap.Modal(actionModalElement)
    : null;

  const paymentModal = paymentModalElement && window.bootstrap && window.bootstrap.Modal
    ? new window.bootstrap.Modal(paymentModalElement)
    : null;

  let searchDebounceTimer = null;
  let requestCounter = 0;
  let pendingPaymentAction = null;

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
  const editUrl = (id) => `${detailUrl(id)}/edit`;
  const paymentStoreUrl = (id) => c.paymentStoreBaseUrl.replace("__ID__", encodeURIComponent(id));
  const paymentSectionUrl = (id) => `${detailUrl(id)}#payment-form-section`;
  const proofSectionUrl = (id) => `${detailUrl(id)}#payment-proof-section`;

  const intOrDefault = (v, fallback) => {
    const n = Number.parseInt(String(v ?? ""), 10);
    return Number.isNaN(n) || n < 1 ? fallback : n;
  };

  const todayYmd = () => new Date().toISOString().slice(0, 10);

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

  const activeFilterEntries = () => {
    const entries = [];

    if (trimValue(s.q) !== "") {
      entries.push({ label: "Keyword", value: s.q });
    }

    if (trimValue(s.nomor_faktur) !== "") {
      entries.push({ label: "No Faktur", value: s.nomor_faktur });
    }

    if (trimValue(s.nama_pt) !== "") {
      entries.push({ label: "Nama PT", value: s.nama_pt });
    }

    if (s.payment_status === "outstanding") {
      entries.push({ label: "Status", value: "Masih Punya Tagihan" });
    }

    if (s.payment_status === "paid") {
      entries.push({ label: "Status", value: "Sudah Lunas" });
    }

    if (trimValue(s.shipment_date_from) !== "" || trimValue(s.shipment_date_to) !== "") {
      const from = trimValue(s.shipment_date_from) || "...";
      const to = trimValue(s.shipment_date_to) || "...";
      entries.push({ label: "Tanggal Kirim", value: `${from} s.d. ${to}` });
    }

    return entries;
  };

  const renderActiveFilters = () => {
    if (!activeFilters || !activeFilterChips) return;

    const entries = activeFilterEntries();

    if (!entries.length) {
      activeFilters.classList.add("d-none");
      activeFilterChips.innerHTML = "";
      return;
    }

    activeFilters.classList.remove("d-none");
    activeFilterChips.innerHTML = entries.map((entry) => `
      <span class="badge bg-light-primary text-primary border px-3 py-2">
        ${esc(entry.label)}: ${esc(entry.value)}
      </span>
    `).join("");
  };

  const stateFromUrl = () => {
    const p = new URLSearchParams(window.location.search);
    const sortBy = trimValue(p.get("sort_by"));
    const sortDir = trimValue(p.get("sort_dir"));
    const paymentStatus = trimValue(p.get("payment_status"));

    return {
      q: trimValue(p.get("q")),
      nomor_faktur: trimValue(p.get("nomor_faktur")),
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

    if (filterForm?.elements["nomor_faktur"]) {
      filterForm.elements["nomor_faktur"].value = s.nomor_faktur;
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

    ["q", "nomor_faktur", "nama_pt", "shipment_date_from", "shipment_date_to"].forEach((k) => {
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

  const openPaymentModal = (row, preserveOldInput = false) => {
    if (!paymentForm || !paymentModal) {
      window.location.assign(paymentSectionUrl(row.supplier_invoice_id));
      return;
    }

    const supplierName = trimValue(row.supplier_nama_pt_pengirim_current)
      || trimValue(row.supplier_nama_pt_pengirim_snapshot)
      || "-";
    const nomorFaktur = trimValue(row.nomor_faktur) || "-";

    paymentForm.action = paymentStoreUrl(row.supplier_invoice_id);

    if (paymentInvoiceIdInput) {
      paymentInvoiceIdInput.value = row.supplier_invoice_id;
    }

    if (paymentModalSubtitle) {
      paymentModalSubtitle.textContent = `${nomorFaktur} • ${supplierName}`;
    }

    if (paymentAmountHelp) {
      paymentAmountHelp.textContent = `Maksimal sebesar sisa tagihan ${rupiah(row.outstanding_rupiah || 0)}.`;
    }

    if (!preserveOldInput) {
      if (paymentDateInput) {
        paymentDateInput.value = c.oldPaymentInvoiceId ? (c.oldPaymentDate || todayYmd()) : todayYmd();
      }

      if (paymentAmountRaw) {
        paymentAmountRaw.value = String(row.outstanding_rupiah || "");
      }

      if (paymentAmountDisplay) {
        paymentAmountDisplay.value = String(Number(row.outstanding_rupiah || 0).toLocaleString("id-ID"));
      }
    }

    paymentModal.show();
  };

  const configureActionModal = (row) => {
    if (
      !actionModalSubtitle ||
      !actionDetailLink ||
      !actionPaymentButton ||
      !actionPaymentTitle ||
      !actionPaymentDescription ||
      !actionProofLink ||
      !actionProofTitle ||
      !actionProofDescription ||
      !actionEditLink ||
      !actionEditTitle ||
      !actionEditDescription
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
      pendingPaymentAction = { mode: "modal", row };
      actionPaymentTitle.textContent = "Bayar";
      actionPaymentDescription.textContent = "Catat pembayaran langsung dari daftar nota.";
    } else {
      pendingPaymentAction = { mode: "detail", row };
      actionPaymentTitle.textContent = "Riwayat Pembayaran";
      actionPaymentDescription.textContent = "Nota ini sudah lunas. Buka detail untuk melihat riwayat pembayaran.";
    }

    if (Number(row.payment_count || 0) < 1) {
      actionProofLink.href = "#";
      actionProofTitle.textContent = "Bukti Bayar";
      actionProofDescription.textContent = "Tersedia setelah ada pembayaran pertama.";
      setLinkDisabledState(actionProofLink, true);
    } else if (row.has_uploaded_proof) {
      actionProofLink.href = proofSectionUrl(row.supplier_invoice_id);
      actionProofTitle.textContent = "Bukti Bayar";
      actionProofDescription.textContent = "Lihat atau unduh lampiran bukti pembayaran.";
      setLinkDisabledState(actionProofLink, false);
    } else {
      actionProofLink.href = proofSectionUrl(row.supplier_invoice_id);
      actionProofTitle.textContent = "Unggah Bukti Bayar";
      actionProofDescription.textContent = "Buka bagian unggah bukti pembayaran.";
      setLinkDisabledState(actionProofLink, false);
    }

    const isEditable = Number(row.payment_count || 0) < 1 && Number(row.receipt_count || 0) < 1;

    if (isEditable) {
      actionEditLink.href = editUrl(row.supplier_invoice_id);
      actionEditTitle.textContent = "Edit Nota";
      actionEditDescription.textContent = "Ubah header, qty, dan total rincian untuk nota pre-effect.";
      setLinkDisabledState(actionEditLink, false);
    } else {
      actionEditLink.href = "#";
      actionEditTitle.textContent = "Edit Nota";
      actionEditDescription.textContent = "Tidak tersedia setelah ada receipt atau payment.";
      setLinkDisabledState(actionEditLink, true);
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
    renderActiveFilters();
    updateUrl(replaceUrl);

    if (c.oldPaymentInvoiceId) {
      const failedRow = (json.data.rows || []).find((row) => row.supplier_invoice_id === c.oldPaymentInvoiceId);
      if (failedRow) {
        openPaymentModal(failedRow, true);
        c.oldPaymentInvoiceId = "";
      }
    }
  };

  $("open-procurement-filter")?.addEventListener("click", () => drawOpen(true));
  $("close-procurement-filter")?.addEventListener("click", () => drawOpen(false));
  backdrop?.addEventListener("click", () => drawOpen(false));

  actionProofLink?.addEventListener("click", (event) => {
    if (actionProofLink.getAttribute("aria-disabled") === "true") {
      event.preventDefault();
    }
  });

  actionEditLink?.addEventListener("click", (event) => {
    if (actionEditLink.getAttribute("aria-disabled") === "true") {
      event.preventDefault();
    }
  });

  actionPaymentButton?.addEventListener("click", () => {
    if (!pendingPaymentAction) return;

    if (pendingPaymentAction.mode === "detail") {
      window.location.assign(paymentSectionUrl(pendingPaymentAction.row.supplier_invoice_id));
      return;
    }

    actionModal?.hide();
    openPaymentModal(pendingPaymentAction.row);
  });

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

    s.nomor_faktur = trimValue(f.get("nomor_faktur"));
    s.nama_pt = trimValue(f.get("nama_pt"));
    s.payment_status = allowedPaymentStatus.has(paymentStatus) ? paymentStatus : "all";
    s.shipment_date_from = trimValue(f.get("shipment_date_from"));
    s.shipment_date_to = trimValue(f.get("shipment_date_to"));
    s.page = 1;

    drawOpen(false);
    load();
  });

  resetFilter?.addEventListener("click", () => {
    s.nomor_faktur = "";
    s.nama_pt = "";
    s.payment_status = "all";
    s.shipment_date_from = "";
    s.shipment_date_to = "";
    s.page = 1;
    syncInputsFromState();
    drawOpen(false);
    load();
  });

  resetAllFilters?.addEventListener("click", () => {
    s.q = "";
    s.nomor_faktur = "";
    s.nama_pt = "";
    s.payment_status = "all";
    s.shipment_date_from = "";
    s.shipment_date_to = "";
    s.page = 1;
    syncInputsFromState();
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

  if (window.AdminMoneyInput) {
    window.AdminMoneyInput.bindMoneyPair(paymentAmountDisplay, paymentAmountRaw);
  }

  syncInputsFromState();
  renderSortIndicators();
  renderActiveFilters();
  load(true);
})();
