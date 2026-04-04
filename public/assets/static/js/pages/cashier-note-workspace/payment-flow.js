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

  const hiddenValue = (id) => byId(id)?.value || "";
  const partialAmountInput = () => byId("inline_payment_amount_paid_display");
  const receivedAmountInput = () => byId("inline_payment_amount_received_display");

  const grandTotal = () => {
    if (typeof NS.currentRows !== "function") return 0;
    const rows = NS.currentRows();
    if (!Array.isArray(rows)) return 0;

    return rows.reduce((sum, item) => sum + Number(item?.total || 0), 0);
  };

  const partialAmount = (total) => {
    const inputValue = digits(partialAmountInput()?.value || hiddenValue("inline_payment_amount_paid_rupiah"));
    return Math.min(inputValue, total);
  };

  const payableAmount = (total) => (
    NS.paymentState.mode === "full"
      ? total
      : partialAmount(total)
  );

  const syncPartialAmount = (total) => {
    const amount = partialAmount(total);
    updateHidden("inline_payment_amount_paid_rupiah", amount > 0 ? amount : "");

    const input = partialAmountInput();
    if (input && document.activeElement !== input) {
      input.value = amount > 0 ? format(amount) : "";
    }

    setText("workspace-partial-selected-total-text", amount);
  };

  const syncReceivedAmount = () => {
    const hidden = digits(hiddenValue("inline_payment_amount_received_rupiah"));
    const input = receivedAmountInput();

    if (input && document.activeElement !== input) {
      input.value = hidden > 0 ? format(hidden) : "";
    }
  };

  const hydrateStateFromHidden = () => {
    const decision = hiddenValue("inline_payment_decision_hidden");
    const paymentMethod = hiddenValue("inline_payment_method_hidden");

    NS.paymentState.mode = decision === "pay_partial" ? "partial" : (decision === "pay_full" ? "full" : "skip");
    NS.paymentState.cashStep = paymentMethod === "cash";
  };

  NS.refreshPaymentUi = (total = grandTotal()) => {
    const noteDate = byId("note_transaction_date")?.value || "";
    updateHidden("inline_payment_paid_at_hidden", noteDate);

    if (NS.paymentState.mode !== "partial") {
      updateHidden("inline_payment_amount_paid_rupiah", "");
      const input = partialAmountInput();
      if (input && document.activeElement !== input) input.value = "";
      setText("workspace-partial-selected-total-text", 0);
    } else {
      syncPartialAmount(total);
    }

    syncReceivedAmount();

    const payable = payableAmount(total);
    const remaining = Math.max(total - payable, 0);
    const received = digits(hiddenValue("inline_payment_amount_received_rupiah"));

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

  const reopenModalIfNeeded = () => {
    const modalEl = byId("workspace-payment-modal");
    if (!modalEl || typeof bootstrap === "undefined" || !bootstrap.Modal) return;

    const decision = hiddenValue("inline_payment_decision_hidden");
    if (!["pay_full", "pay_partial"].includes(decision)) return;

    hydrateStateFromHidden();
    NS.refreshPaymentUi();

    const modal = new bootstrap.Modal(modalEl);
    modal.show();
  };

  NS.openPaymentModal = (mode) => {
    const modalEl = byId("workspace-payment-modal");
    if (!modalEl || typeof bootstrap === "undefined" || !bootstrap.Modal) return;

    NS.paymentState.mode = mode;
    NS.paymentState.cashStep = false;

    updateHidden("inline_payment_decision_hidden", mode === "full" ? "pay_full" : "pay_partial");
    updateHidden("inline_payment_method_hidden", "");
    updateHidden("inline_payment_amount_received_rupiah", "");

    const receivedDisplay = receivedAmountInput();
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
      updateHidden("inline_payment_decision_hidden", "skip");
      updateHidden("inline_payment_method_hidden", "");
      updateHidden("inline_payment_amount_paid_rupiah", "");
      updateHidden("inline_payment_amount_received_rupiah", "");
      const partialInput = partialAmountInput();
      if (partialInput) partialInput.value = "";
      const receivedInput = receivedAmountInput();
      if (receivedInput) receivedInput.value = "";
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
    }
  });

  document.addEventListener("input", (event) => {
    if (
      event.target.id === "inline_payment_amount_paid_display"
      || event.target.id === "inline_payment_amount_received_display"
      || event.target.id === "note_transaction_date"
    ) {
      updateHidden("inline_payment_amount_received_rupiah", digits(receivedAmountInput()?.value || ""));
      NS.refreshPaymentUi();
    }
  });

  hydrateStateFromHidden();
  NS.refreshPaymentUi();
  document.addEventListener("DOMContentLoaded", reopenModalIfNeeded);
  reopenModalIfNeeded();
})();
