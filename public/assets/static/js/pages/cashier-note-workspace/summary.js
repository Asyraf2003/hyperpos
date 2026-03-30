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

  const selectedMethod = () =>
    document.getElementById("inline_payment_method")?.value || "cash";

  const paymentDecisionLabel = (value) =>
    ({ skip: "Skip", pay_full: "Bayar Penuh", pay_partial: "Bayar Sebagian" })[value] || "Skip";

  const paymentMethodLabel = (value) =>
    ({ cash: "Cash", transfer: "Transfer" })[value] || "Belum diatur";

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

    const grandTotal = Array.from(document.querySelectorAll("[data-line-item]")).reduce((sum, row) => sum + rowTotal(row), 0);
    const decision = selectedDecision();
    const paidInput = document.getElementById("inline_payment_amount_paid_rupiah");
    const receivedInput = document.getElementById("inline_payment_amount_received_rupiah");
    const paidRaw = digits(paidInput?.value);
    const receivedRaw = digits(receivedInput?.value);
    const paidNow = decision === "pay_full" ? grandTotal : decision === "pay_partial" ? Math.min(paidRaw, grandTotal) : 0;
    const outstanding = Math.max(grandTotal - paidNow, 0);
    const isSkip = decision === "skip";
    const isFull = decision === "pay_full";
    const isPartial = decision === "pay_partial";
    const needsCash = !isSkip && selectedMethod() === "cash";

    document.querySelectorAll("[data-line-item]").forEach((row) => NS.syncQtyGuard(row));

    [
      ["workspace-grand-total-text", grandTotal],
      ["workspace-paid-now-text", paidNow],
      ["workspace-outstanding-text", outstanding],
      ["workspace-modal-grand-total-text", grandTotal],
      ["workspace-modal-paid-now-text", paidNow],
      ["workspace-modal-outstanding-text", outstanding],
      ["workspace-modal-full-paid-text", grandTotal],
      ["workspace-modal-full-change-text", isFull && needsCash ? Math.max(receivedRaw - grandTotal, 0) : 0],
      ["workspace-modal-partial-before-text", grandTotal],
      ["workspace-modal-partial-paid-text", paidNow],
      ["workspace-modal-partial-after-text", outstanding],
    ].forEach(([id, value]) => setText(id, value));

    const decisionText = paymentDecisionLabel(decision);
    const methodText =
      decision === "skip"
        ? "Belum diatur"
        : `${paymentMethodLabel(selectedMethod())}${decision === "pay_partial" ? " · nominal parsial" : ""}`;

    const decisionTargets = [
      document.getElementById("workspace-payment-decision-text"),
      document.getElementById("workspace-modal-payment-decision-text"),
    ];

    const methodTargets = [
      document.getElementById("workspace-payment-method-text"),
      document.getElementById("workspace-modal-payment-method-text"),
    ];

    decisionTargets.forEach((el) => {
      if (el) el.textContent = decisionText;
    });

    methodTargets.forEach((el) => {
      if (el) el.textContent = methodText;
    });

    toggle("workspace-payment-panel-skip", isSkip);
    toggle("workspace-payment-panel-full", isFull);
    toggle("workspace-payment-panel-partial", isPartial);
    toggle("workspace-modal-payment-fields", !isSkip);
    toggle("workspace-modal-amount-paid-group", isPartial);
    toggle("workspace-modal-amount-received-group", needsCash);

    if (paidInput) paidInput.disabled = !isPartial;
    if (receivedInput) receivedInput.disabled = !needsCash;
  };
})();
