(() => {
  const NS = (window.CashierNoteWorkspace = window.CashierNoteWorkspace || {});
  const timers = new WeakMap();

  const parseDigits = (value) => Number.parseInt(String(value || "").replace(/\D+/g, "") || "0", 10);

  const renderResults = (row, rows) => {
    const results = row.querySelector("[data-product-results]");
    results.innerHTML = "";
    results.classList.toggle("d-none", rows.length === 0);

    rows.forEach((item) => {
      const button = document.createElement("button");
      button.type = "button";
      button.className = "list-group-item list-group-item-action";
      button.textContent = `${item.label} · stok ${item.available_stock}`;
      button.addEventListener("click", () => NS.selectProduct(row, item));
      results.appendChild(button);
    });
  };

  NS.selectProduct = (row, item) => {
    const search = row.querySelector("[data-product-search]");
    const hidden = row.querySelector("[data-product-id]");
    const raw = row.querySelector('[name$="[unit_price_rupiah]"]');
    const display = raw?.closest("[data-money-input-group]")?.querySelector("[data-money-display]");
    search.value = item.label;
    hidden.value = item.id;
    NS.updateStockText(row, item.available_stock);
    if (raw && !parseDigits(raw.value)) raw.value = String(item.default_unit_price_rupiah || 0);
    if (display && !parseDigits(display.value)) display.value = String(item.default_unit_price_rupiah || 0);
    window.AdminMoneyInput?.bindBySelector?.(row);
    renderResults(row, []);
    NS.syncQtyGuard?.(row);
    NS.updateSummary?.();
  };

  NS.bindProductSearch = (row) => {
    const input = row.querySelector("[data-product-search]");
    if (!input) return;

    input.addEventListener("input", () => {
      clearTimeout(timers.get(input));
      timers.set(
        input,
        setTimeout(async () => {
          const query = input.value.trim();
          if (query.length < 2) return renderResults(row, []);
          const url = `${NS.config.productLookupEndpoint}?q=${encodeURIComponent(query)}`;
          const response = await fetch(url, { headers: { Accept: "application/json" } });
          const payload = await response.json();
          renderResults(row, payload?.data?.rows || []);
        }, 250)
      );
    });

    document.addEventListener("click", (event) => {
      if (!row.contains(event.target)) renderResults(row, []);
    });
  };
})();
