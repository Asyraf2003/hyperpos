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
    const decision = document.querySelector('input[name="inline_payment[decision]"]:checked')?.value || "skip";
    const paidRaw = digits(document.getElementById("inline_payment_amount_paid_rupiah")?.value);
    const paidNow = decision === "pay_full" ? grandTotal : decision === "pay_partial" ? Math.min(paidRaw, grandTotal) : 0;

    document.getElementById("workspace-grand-total-text").textContent = format(grandTotal);
    document.getElementById("workspace-paid-now-text").textContent = format(paidNow);
    document.getElementById("workspace-outstanding-text").textContent = format(Math.max(grandTotal - paidNow, 0));
  };
})();
