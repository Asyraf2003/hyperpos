(() => {
  const NS = (window.CashierNoteWorkspace = window.CashierNoteWorkspace || {});
  const digits = (value) => Number.parseInt(String(value || "").replace(/\D+/g, "") || "0", 10);
  const format = (value) => Number(value || 0).toLocaleString("id-ID");

  const rowTotal = (row) => {
    const type = row.dataset.itemType || "";
    const service = digits(row.querySelector('[name$="[service][price_rupiah]"]')?.value);
    const qty = digits(row.querySelector("[data-qty-input]")?.value || row.querySelector('input[name$="[external_purchase_lines][0][qty]"]')?.value);
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
    const paidRaw = digits(document.getElementById("inline_payment_amount_paid_rupiah")?.value);
    const paidNow = decision === "pay_full" ? grandTotal : decision === "pay_partial" ? Math.min(paidRaw, grandTotal) : 0;
    const outstanding = Math.max(grandTotal - paidNow, 0);

    const mappings = [
      ["workspace-grand-total-text", grandTotal],
      ["workspace-paid-now-text", paidNow],
      ["workspace-outstanding-text", outstanding],
      ["workspace-modal-grand-total-text", grandTotal],
      ["workspace-modal-paid-now-text", paidNow],
      ["workspace-modal-outstanding-text", outstanding],
    ];

    mappings.forEach(([id, value]) => {
      const el = document.getElementById(id);
      if (el) el.textContent = format(value);
    });

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
  };
})();
