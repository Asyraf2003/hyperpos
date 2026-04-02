(() => {
  const NS = (window.CashierNoteWorkspace = window.CashierNoteWorkspace || {});
  const digits = (value) => Number.parseInt(String(value || "").replace(/\D+/g, "") || "0", 10);
  const format = (value) => Number(value || 0).toLocaleString("id-ID");
  const byId = (id) => document.getElementById(id);
  const setText = (id, value) => {
    const el = byId(id);
    if (el) el.textContent = format(value);
  };
  const toggle = (id, show) => {
    const el = byId(id);
    if (el) el.classList.toggle("d-none", !show);
  };
  const toggleFlex = (id, show) => {
    const el = byId(id);
    if (!el) return;
    el.classList.toggle("d-none", !show);
    el.classList.toggle("d-flex", show);
  };

  NS.paymentState = NS.paymentState || { mode: "skip", cashStep: false };

  const updateHidden = (id, value) => {
    const el = byId(id);
    if (el) el.value = String(value ?? "");
  };

  const currentRows = () => {
    if (typeof NS.currentRows !== "function") return [];
    const rows = NS.currentRows();
    return Array.isArray(rows) ? rows : [];
  };

  const grandTotal = () =>
    currentRows().reduce((sum, item) => sum + Number(item?.total || 0), 0);

  const clearPayNow = () => {
    document.querySelectorAll("[data-pay-now]").forEach((input) => {
      input.value = "0";
    });
  };

  const totalSelected = () =>
    currentRows()
      .filter(({ row }) => row?.querySelector("[data-pay-now]")?.value === "1")
      .reduce((sum, item) => sum + Number(item?.total || 0), 0);

  const payableAmount = (total) =>
    NS.paymentState.mode === "full" ? total : totalSelected();

  const buildPartialList = () => {
    const root = byId("workspace-partial-selection-list");
    if (!root) return;

    root.innerHTML = "";

    currentRows().forEach(({ row, index, title, total }) => {
      const hidden = row?.querySelector("[data-pay-now]");
      const checked = hidden?.value === "1";

      const wrapper = document.createElement("label");
      wrapper.className = "border rounded p-3 d-flex justify-content-between align-items-center gap-3";
      wrapper.innerHTML =
        '<span class="d-flex align-items-center gap-2">' +
          '<input type="checkbox" class="form-check-input mt-0" data-partial-check="' + index + '"' + (checked ? " checked" : "") + ">" +
          '<span><span class="fw-semibold d-block">' + title + '</span><small class="text-muted">Pilih untuk dibayar sekarang</small></span>' +
        '</span>' +
        '<span class="fw-semibold">' + format(total) + '</span>';

      root.appendChild(wrapper);
    });

    const selected = totalSelected();
    updateHidden("inline_payment_amount_paid_rupiah", selected > 0 ? selected : "");
    setText("workspace-partial-selected-total-text", selected);
  };

  NS.refreshPaymentUi = (total = grandTotal()) => {
    const noteDate = byId("note_transaction_date")?.value || "";
    updateHidden("inline_payment_paid_at_hidden", noteDate);

    if (NS.paymentState.mode !== "partial") clearPayNow();
    if (NS.paymentState.mode === "partial") buildPartialList();

    const payable = payableAmount(total);
    const remaining = Math.max(total - payable, 0);
    const received = digits(byId("inline_payment_amount_received_rupiah")?.value);

    setText("workspace-modal-total-text", total);
    setText("workspace-modal-payable-text", payable);
    setText("workspace-modal-remaining-text", remaining);
    setText("workspace-cash-payable-text", payable);
    setText("workspace-cash-received-text", received);
    setText("workspace-cash-change-text", Math.max(received - payable, 0));

    const modeText = byId("workspace-payment-mode-text");
    if (modeText) modeText.textContent = NS.paymentState.mode === "full" ? "Bayar Penuh" : "Bayar Sebagian";

    const cashBadge = byId("workspace-cash-status-badge");
    if (cashBadge) cashBadge.textContent = NS.paymentState.cashStep ? "Aktif" : "Siaga";

    toggle("workspace-payment-panel-full", NS.paymentState.mode === "full");
    toggle("workspace-payment-panel-partial", NS.paymentState.mode === "partial");
    toggle("workspace-payment-panel-cash", NS.paymentState.cashStep);
    toggle("workspace-cash-shell-hint", !NS.paymentState.cashStep);
    toggleFlex("workspace-payment-footer-main", !NS.paymentState.cashStep);
    toggleFlex("workspace-payment-footer-cash", NS.paymentState.cashStep);

    const transferButton = byId("workspace-payment-submit-transfer");
    const cashButton = byId("workspace-payment-open-cash");
    const partialInvalid = NS.paymentState.mode === "partial" && payable <= 0;

    if (transferButton) transferButton.disabled = partialInvalid;
    if (cashButton) cashButton.disabled = partialInvalid;
  };

  NS.openPaymentModal = (mode) => {
    const modalEl = byId("workspace-payment-modal");
    if (!modalEl || typeof bootstrap === "undefined" || !bootstrap.Modal) return;

    NS.paymentState.mode = mode;
    NS.paymentState.cashStep = false;

    updateHidden("inline_payment_decision_hidden", mode === "full" ? "pay_full" : "pay_partial");
    updateHidden("inline_payment_method_hidden", "");
    updateHidden("inline_payment_amount_received_rupiah", "");

    const receivedDisplay = byId("inline_payment_amount_received_display");
    if (receivedDisplay) receivedDisplay.value = "";

    NS.refreshPaymentUi();
    new bootstrap.Modal(modalEl).show();
  };

  document.addEventListener("click", (event) => {
    const open = event.target.closest("[data-open-payment]");
    if (open) {
      NS.openPaymentModal(open.dataset.openPayment || "full");
      return;
    }

    if (event.target.closest("#workspace-submit-skip")) {
      clearPayNow();
      updateHidden("inline_payment_decision_hidden", "skip");
      updateHidden("inline_payment_method_hidden", "");
      updateHidden("inline_payment_amount_paid_rupiah", "");
      updateHidden("inline_payment_amount_received_rupiah", "");
      byId("cashier-note-workspace-form")?.requestSubmit();
      return;
    }

    if (event.target.closest("#workspace-payment-submit-transfer")) {
      updateHidden("inline_payment_method_hidden", "transfer");
      return;
    }

    if (event.target.closest("#workspace-payment-open-cash")) {
      NS.paymentState.cashStep = true;
      updateHidden("inline_payment_method_hidden", "cash");
      NS.refreshPaymentUi();
      return;
    }

    if (event.target.closest("#workspace-payment-back-cash")) {
      NS.paymentState.cashStep = false;
      updateHidden("inline_payment_method_hidden", "");
      NS.refreshPaymentUi();
      return;
    }

    const checkbox = event.target.closest("[data-partial-check]");
    if (checkbox) {
      const row = document.querySelector('[data-row-index="' + checkbox.dataset.partialCheck + '"]');
      const hidden = row?.querySelector("[data-pay-now]");
      if (hidden) hidden.value = checkbox.checked ? "1" : "0";
      NS.refreshPaymentUi();
    }
  });

  document.addEventListener("input", (event) => {
    if (event.target.id === "inline_payment_amount_received_display" || event.target.id === "note_transaction_date") {
      updateHidden("inline_payment_amount_received_rupiah", digits(byId("inline_payment_amount_received_display")?.value || ""));
      NS.refreshPaymentUi();
    }
  });
})();
