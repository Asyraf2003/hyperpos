(() => {
  const NS = (window.CashierNoteWorkspace = window.CashierNoteWorkspace || {});
  const digits = (value) => Number.parseInt(String(value || "").replace(/\D+/g, "") || "0", 10);
  const format = (value) => Number(value || 0).toLocaleString("id-ID");
  const setText = (id, value) => {
    const el = document.getElementById(id);
    if (el) el.textContent = format(value);
  };
  const toggle = (id, show) => {
    const el = document.getElementById(id);
    if (el) el.classList.toggle("d-none", !show);
  };
  const toggleFlex = (id, show) => {
    const el = document.getElementById(id);
    if (!el) return;
    el.classList.toggle("d-none", !show);
    el.classList.toggle("d-flex", show);
  };

  NS.paymentUiState = NS.paymentUiState || { cashStep: false };

  const rowTotal = (row) => {
    const type = row.dataset.itemType || "";
    const service = digits(row.querySelector('[name$="[service][price_rupiah]"]')?.value);
    const qty = digits(
      row.querySelector("[data-qty-input]")?.value ||
      row.querySelector('input[name$="[external_purchase_lines][0][qty]"]')?.value
    );
    const product = digits(row.querySelector('input[name$="[product_lines][0][unit_price_rupiah]"]')?.value);
    const external = digits(row.querySelector('input[name$="[external_purchase_lines][0][unit_cost_rupiah]"]')?.value);

    if (type === "product") return qty * product;
    if (type === "service_store_stock") return service + (qty * product);
    if (type === "service_external") return service + (qty * external);
    return service;
  };

  const selectedDecision = () =>
    document.querySelector('input[name="inline_payment[decision]"]:checked')?.value || "skip";

  const paymentDecisionLabel = (value) =>
    ({ skip: "Skip", pay_full: "Bayar Penuh", pay_partial: "Bayar Sebagian" })[value] || "Skip";

  const paymentMethodLabel = (value) =>
    ({ cash: "Cash", transfer: "Transfer" })[value] || "Belum dipilih";

  const getRawPaid = () => digits(document.getElementById("inline_payment_amount_paid_rupiah")?.value);
  const getRawReceived = () => digits(document.getElementById("inline_payment_amount_received_rupiah")?.value);

  const getPayableAmount = (grandTotal, decision) => {
    if (decision === "pay_full") {
      return grandTotal;
    }

    if (decision === "pay_partial") {
      return Math.min(getRawPaid(), grandTotal);
    }

    return 0;
  };

  const syncHiddenPaidAt = () => {
    const noteDate = document.getElementById("note_transaction_date")?.value || "";
    const hiddenPaidAt = document.getElementById("inline_payment_paid_at_hidden");
    if (hiddenPaidAt) hiddenPaidAt.value = noteDate;
  };

  const syncHiddenPaymentMethod = (value) => {
    const hidden = document.getElementById("inline_payment_method_hidden");
    if (hidden) hidden.value = value;
  };

  const updateFooterState = (decision, cashStep, partialAmount) => {
    toggle("workspace-payment-submit-skip", decision === "skip" && !cashStep);
    toggle("workspace-payment-submit-transfer", decision !== "skip" && !cashStep);
    toggle("workspace-payment-open-cash", decision !== "skip" && !cashStep);
    toggleFlex("workspace-payment-footer-default", !cashStep);
    toggleFlex("workspace-payment-footer-cash", cashStep);

    const transferButton = document.getElementById("workspace-payment-submit-transfer");
    const openCashButton = document.getElementById("workspace-payment-open-cash");

    const partialInvalid = decision === "pay_partial" && partialAmount <= 0;

    if (transferButton) transferButton.disabled = partialInvalid;
    if (openCashButton) openCashButton.disabled = partialInvalid;
  };

  const updatePanels = (decision, cashStep, grandTotal, paidNow, outstanding) => {
    toggle("workspace-payment-panel-skip", decision === "skip" && !cashStep);
    toggle("workspace-payment-panel-full", decision === "pay_full" && !cashStep);
    toggle("workspace-payment-panel-partial", decision === "pay_partial" && !cashStep);
    toggle("workspace-payment-panel-cash", cashStep);

    const fullMethodText = document.getElementById("workspace-modal-full-method-text");
    if (fullMethodText) {
      fullMethodText.textContent = cashStep ? "Cash" : "Belum dipilih";
    }

    setText("workspace-modal-full-paid-text", grandTotal);
    setText("workspace-modal-partial-before-text", grandTotal);
    setText("workspace-modal-partial-paid-text", paidNow);
    setText("workspace-modal-partial-after-text", outstanding);
    setText("workspace-modal-cash-payable-text", paidNow);
    setText("workspace-modal-cash-received-text", getRawReceived());
    setText("workspace-modal-cash-change-text", Math.max(getRawReceived() - paidNow, 0));
  };

  NS.syncQtyGuard = (row) => {
    const input = row.querySelector("[data-qty-input]");
    const error = row.querySelector("[data-stock-error]");
    if (!input || !error) return;

    const available = digits(row.dataset.availableStock || "0");
    const qty = digits(input.value);
    const invalid = available > 0 && qty > available;

    input.classList.toggle("is-invalid", invalid);
    error.classList.toggle("d-none", !invalid);
  };

  NS.updateSummary = () => {
    document.querySelectorAll("[data-line-item]").forEach((row) => NS.syncQtyGuard(row));

    syncHiddenPaidAt();

    const grandTotal = Array.from(document.querySelectorAll("[data-line-item]")).reduce((sum, row) => sum + rowTotal(row), 0);
    const decision = selectedDecision();
    const paidNow = getPayableAmount(grandTotal, decision);
    const outstanding = Math.max(grandTotal - paidNow, 0);

    [
      ["workspace-grand-total-text", grandTotal],
      ["workspace-paid-now-text", paidNow],
      ["workspace-outstanding-text", outstanding],
      ["workspace-modal-grand-total-text", grandTotal],
      ["workspace-modal-paid-now-text", paidNow],
      ["workspace-modal-outstanding-text", outstanding],
    ].forEach(([id, value]) => setText(id, value));

    const decisionText = paymentDecisionLabel(decision);
    const methodHiddenValue = document.getElementById("inline_payment_method_hidden")?.value || "";
    const methodText = decision === "skip" ? "Belum diatur" : paymentMethodLabel(methodHiddenValue || "Belum dipilih");

    ["workspace-payment-decision-text", "workspace-modal-payment-decision-text"].forEach((id) => {
      const el = document.getElementById(id);
      if (el) el.textContent = decisionText;
    });

    ["workspace-payment-method-text", "workspace-modal-payment-method-text"].forEach((id) => {
      const el = document.getElementById(id);
      if (el) el.textContent = methodText;
    });

    updatePanels(decision, NS.paymentUiState.cashStep, grandTotal, paidNow, outstanding);
    updateFooterState(decision, NS.paymentUiState.cashStep, getRawPaid());
  };

  document.addEventListener("change", (event) => {
    if (event.target.matches('input[name="inline_payment[decision]"]')) {
      NS.paymentUiState.cashStep = false;
      syncHiddenPaymentMethod("");
      NS.updateSummary();
    }
  });

  document.addEventListener("click", (event) => {
    const openCash = event.target.closest("#workspace-payment-open-cash");
    if (openCash) {
      NS.paymentUiState.cashStep = true;
      syncHiddenPaymentMethod("cash");
      NS.updateSummary();
      return;
    }

    const backCash = event.target.closest("#workspace-payment-back-from-cash");
    if (backCash) {
      NS.paymentUiState.cashStep = false;
      syncHiddenPaymentMethod("");
      NS.updateSummary();
      return;
    }

    const submitTransfer = event.target.closest("#workspace-payment-submit-transfer");
    if (submitTransfer) {
      syncHiddenPaymentMethod("transfer");
      return;
    }

    const submitCash = event.target.closest("#workspace-payment-submit-cash");
    if (submitCash) {
      syncHiddenPaymentMethod("cash");
      return;
    }

    const submitSkip = event.target.closest("#workspace-payment-submit-skip");
    if (submitSkip) {
      syncHiddenPaymentMethod("");
    }
  });

  document.addEventListener("input", (event) => {
    if (
      event.target.id === "inline_payment_amount_paid_display" ||
      event.target.id === "inline_payment_amount_received_display" ||
      event.target.id === "note_transaction_date"
    ) {
      NS.updateSummary();
    }
  });

  const bootScript = document.querySelector('script[src*="boot.js"]');
  if (bootScript) {
      const parent = bootScript.parentNode;
      parent.removeChild(bootScript);
      const newBoot = document.createElement('script');
      newBoot.src = bootScript.src;
      parent.appendChild(newBoot);
  }

})();
