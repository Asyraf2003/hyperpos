(() => {
  const NS = (window.CashierNoteWorkspace = window.CashierNoteWorkspace || {});
  const digits = (value) => Number.parseInt(String(value || "").replace(/\D+/g, "") || "0", 10);
  const format = (value) => Number(value || 0).toLocaleString("id-ID");

  NS.rowTotal = (row) => {
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

  NS.currentRows = () =>
    Array.from(document.querySelectorAll("[data-line-item]")).map((row) => ({
      row,
      index: Number(row.dataset.rowIndex || "0"),
      title: row.querySelector("[data-line-title]")?.textContent?.trim() || "Rincian",
      total: NS.rowTotal(row),
    }));

  NS.updateSummary = () => {
    document.querySelectorAll("[data-line-item]").forEach((row) => NS.syncQtyGuard(row));
    const grandTotal = NS.currentRows().reduce((sum, item) => sum + item.total, 0);
    const totalText = document.getElementById("workspace-note-total-text");
    if (totalText) totalText.textContent = format(grandTotal);
    NS.refreshPaymentUi?.(grandTotal);
  };
})();
