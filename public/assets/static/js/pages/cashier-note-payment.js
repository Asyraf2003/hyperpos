(() => {
  const modal = document.getElementById("note-payment-modal");
  const form = document.getElementById("note-payment-form");
  if (!modal || !form) return;

  const byId = (id) => document.getElementById(id);
  const digits = (value) => Number.parseInt(String(value || "").replace(/\D+/g, "") || "0", 10);
  const format = (value) => new Intl.NumberFormat("id-ID").format(Number.isFinite(value) ? value : 0);
  const rows = () => Array.from(document.querySelectorAll("[data-payment-row-source]"));
  const outstanding = () => selectedRows().reduce((sum, row) => sum + digits(row.dataset.outstandingRupiah), 0);
  const hiddenRows = () => byId("payment-selected-row-ids");
  const amountPaidHidden = () => byId("detail-payment-amount-paid");
  const amountReceivedHidden = () => byId("detail-payment-amount-received");
  const methodHidden = () => byId("detail-payment-method");
  const intentHidden = () => byId("detail-payment-intent");
  const paidInput = () => byId("detail-payment-amount-paid-display");
  const receivedInput = () => byId("detail-payment-amount-received-display");

  let intent = intentHidden()?.value === "settle" ? "settle" : "pay";
  let cashStep = false;
  let selected = [];

  const setText = (id, value) => {
    const el = byId(id);
    if (el) el.textContent = format(value);
  };

  const escapeHtml = (value) =>
    String(value || "")
      .replaceAll("&", "&amp;")
      .replaceAll("<", "&lt;")
      .replaceAll(">", "&gt;")
      .replaceAll('"', "&quot;")
      .replaceAll("'", "&#039;");

  const setMethod = (method) => {
    const input = methodHidden();
    if (input) input.value = method;
  };

  const setAmountPaid = (amount) => {
    const input = amountPaidHidden();
    if (input) input.value = amount > 0 ? String(amount) : "";
  };

  const setAmountReceived = (amount) => {
    const input = amountReceivedHidden();
    if (input) input.value = amount > 0 ? String(amount) : "";
  };

  const selectedRows = () => selected;

  const chooseRows = () => {
    const available = rows();
    selected = available;

    const container = hiddenRows();
    if (!container) return;

    container.innerHTML = selected
      .map((row) => `<input type="hidden" name="selected_row_ids[]" value="${escapeHtml(row.dataset.rowId)}">`)
      .join("");
  };

  const payable = () => {
    const total = outstanding();

    if (intent === "settle") {
      return total;
    }

    const typed = digits(paidInput()?.value || amountPaidHidden()?.value || "");
    return typed > 0 ? Math.min(typed, total) : total;
  };

  const syncAmountInput = () => {
    const input = paidInput();
    if (!input || intent !== "pay") return;

    const current = digits(input.value || "");
    if (document.activeElement === input) {
      setAmountPaid(Math.min(current, outstanding()));
      return;
    }

    const amount = current > 0 ? Math.min(current, outstanding()) : outstanding();
    input.value = amount > 0 ? format(amount) : "";
    setAmountPaid(amount);
  };

  const syncHidden = () => {
    const intentInput = intentHidden();
    if (intentInput) intentInput.value = intent;

    if (intent === "settle") {
      setAmountPaid(0);
    } else {
      syncAmountInput();
    }

    if (!cashStep) {
      setAmountReceived(0);
    }
  };

  const renderRows = () => {
    const container = byId("detail-payment-line-summary");
    if (!container) return;

    if (selectedRows().length === 0) {
      container.innerHTML = '<div class="p-3 text-muted small">Belum ada tagihan outstanding.</div>';
      return;
    }

    container.innerHTML = selectedRows()
      .map((row) => `
        <div class="p-3 border rounded">
          <div class="d-flex justify-content-between align-items-start gap-3">
            <div>
              <div class="fw-semibold">${escapeHtml(row.dataset.label || "Tagihan")}</div>
              <div class="small text-muted">${escapeHtml(row.dataset.typeLabel || "-")}</div>
            </div>
            <strong>${format(digits(row.dataset.outstandingRupiah))}</strong>
          </div>
        </div>
      `)
      .join("");
  };

  const updateUi = () => {
    chooseRows();
    syncHidden();
    renderRows();

    const total = outstanding();
    const paid = payable();
    const received = digits(receivedInput()?.value || amountReceivedHidden()?.value || "");

    const dialog = byId("note-payment-modal-dialog");
    if (dialog) {
      dialog.classList.toggle("modal-xl", !cashStep);
      dialog.style.maxWidth = cashStep ? "560px" : "";
      dialog.style.width = cashStep ? "calc(100% - 2rem)" : "";
    }

    const title = byId("note-payment-modal-title");
    if (title) title.textContent = cashStep ? "Pembayaran Cash" : "Proses Pembayaran";

    const subtitle = byId("note-payment-modal-subtitle");
    if (subtitle) {
      subtitle.textContent = cashStep
        ? "Masukkan uang pelanggan, cek kembalian, lalu simpan cash."
        : "Pilih transfer atau cash. Sistem otomatis memilih tagihan aktif yang masih outstanding.";
    }

    const badge = byId("detail-payment-mode-badge");
    if (badge) badge.textContent = intent === "settle" ? "Lunasi" : "Bayar Sebagian";

    const cashMode = byId("detail-payment-cash-mode-text");
    if (cashMode) cashMode.textContent = intent === "settle" ? "Lunasi" : "Bayar Sebagian";

    const standard = byId("detail-payment-standard-view");
    const cash = byId("detail-payment-cash-view");
    const partialPanel = byId("detail-payment-partial-panel");
    const footerMain = byId("detail-payment-footer-main");
    const footerCash = byId("detail-payment-footer-cash");

    if (standard) standard.classList.toggle("d-none", cashStep);
    if (cash) cash.classList.toggle("d-none", !cashStep);
    if (partialPanel) partialPanel.classList.toggle("d-none", intent !== "pay");
    if (footerMain) footerMain.classList.toggle("d-none", cashStep);
    if (footerCash) footerCash.classList.toggle("d-none", !cashStep);

    setText("detail-payment-selected-total", total);
    setText("detail-payment-payable-text", paid);
    setText("detail-payment-remaining-text", Math.max(total - paid, 0));
    setText("detail-payment-cash-payable-text", paid);
    setText("detail-payment-change-text", Math.max(received - paid, 0));

    const transfer = byId("detail-payment-submit-transfer");
    const openCash = byId("detail-payment-open-cash");
    const submitCash = byId("detail-payment-submit-cash");

    if (transfer) transfer.disabled = paid <= 0 || selectedRows().length === 0;
    if (openCash) openCash.disabled = paid <= 0 || selectedRows().length === 0;
    if (submitCash) submitCash.disabled = paid <= 0 || received < paid || selectedRows().length === 0;
  };

  const openCash = () => {
    cashStep = true;
    setMethod("cash");
    updateUi();
    window.requestAnimationFrame(() => receivedInput()?.focus());
  };

  const closeCash = () => {
    cashStep = false;
    setMethod("");
    setAmountReceived(0);
    if (receivedInput()) receivedInput().value = "";
    updateUi();
  };

  document.addEventListener("click", (event) => {
    const trigger = event.target.closest(".js-open-payment-intent");
    if (trigger) {
      intent = trigger.dataset.paymentIntent === "settle" ? "settle" : "pay";
      cashStep = false;
      setMethod("");
      setAmountReceived(0);
      if (receivedInput()) receivedInput().value = "";
      updateUi();
      return;
    }

    if (event.target.closest("#detail-payment-open-cash")) {
      event.preventDefault();
      openCash();
      return;
    }

    if (event.target.closest("#detail-payment-back-cash")) {
      event.preventDefault();
      closeCash();
      return;
    }

    if (event.target.closest("#detail-payment-submit-transfer")) {
      setMethod("tf");
      updateUi();
      return;
    }

    if (event.target.closest("#detail-payment-submit-cash")) {
      setMethod("cash");
      setAmountReceived(digits(receivedInput()?.value || ""));
      updateUi();
    }
  });

  form.addEventListener("submit", () => {
    if (cashStep) {
      setMethod("cash");
      setAmountReceived(digits(receivedInput()?.value || ""));
    }

    if (methodHidden()?.value !== "cash") {
      setMethod("tf");
    }

    updateUi();
  });

  document.addEventListener("input", (event) => {
    if (event.target.id === "detail-payment-amount-paid-display") {
      const numeric = Math.min(digits(event.target.value || ""), outstanding());
      event.target.value = numeric > 0 ? format(numeric) : "";
      setAmountPaid(numeric);
      updateUi();
      return;
    }

    if (event.target.id === "detail-payment-amount-received-display") {
      const numeric = digits(event.target.value || "");
      event.target.value = numeric > 0 ? format(numeric) : "";
      setAmountReceived(numeric);
      updateUi();
    }
  });

  modal.addEventListener("shown.bs.modal", () => {
    updateUi();

    if (intent === "pay" && paidInput()) {
      paidInput()?.focus();
      paidInput()?.select();
    }
  });

  chooseRows();
  updateUi();
})();
