(() => {
  const NS = (window.CashierNoteWorkspace = window.CashierNoteWorkspace || {});
  const timers = new WeakMap();
  const requestTokens = new WeakMap();

  const parseDigits = (value) => Number.parseInt(String(value || "").replace(/\D+/g, "") || "0", 10);

  NS.syncFloorPriceGuard = (row) => {
    const raw = row.querySelector('[name$="[unit_price_rupiah]"]');
    const display = row.querySelector("[data-price-input]");
    const warning = row.querySelector("[data-min-price-warning]");
    const text = row.querySelector("[data-min-price-text]");

    const floor = parseDigits(row.dataset.minimumUnitPriceRupiah || "0");
    const current = parseDigits(raw?.value || display?.value || "0");
    const invalid = floor > 0 && current > 0 && current < floor;

    if (text) {
      text.textContent = floor > 0 ? `Harga minimum: ${floor.toLocaleString("id-ID")}` : "Harga minimum: -";
    }

    if (display) {
      display.classList.toggle("is-invalid", invalid);
    }

    if (warning) {
      warning.classList.toggle("d-none", !invalid);
    }
  };

  const renderResults = (row, rows) => {
    const results = row.querySelector("[data-product-results]");
    if (!results) return;

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
    const display = row.querySelector("[data-price-input]");

    if (!search || !hidden) return;

    search.value = item.label;
    hidden.value = item.id;
    row.dataset.minimumUnitPriceRupiah = String(item.minimum_unit_price_rupiah || item.default_unit_price_rupiah || 0);
    NS.updateStockText?.(row, item.available_stock);

    if (raw && !parseDigits(raw.value)) raw.value = String(item.default_unit_price_rupiah || 0);
    if (display && !parseDigits(display.value)) display.value = String(item.default_unit_price_rupiah || 0);

    window.AdminMoneyInput?.bindBySelector?.(row);
    NS.syncFloorPriceGuard?.(row);
    renderResults(row, []);
    NS.syncQtyGuard?.(row);
    NS.updateSummary?.();
  };

  NS.bindProductSearch = (row) => {
    const input = row.querySelector("[data-product-search]");
    const priceInput = row.querySelector("[data-price-input]");

    if (!input) return;
    if (input.dataset.searchBound === "1") return;
    input.dataset.searchBound = "1";

    input.addEventListener("input", () => {
      clearTimeout(timers.get(input));

      timers.set(
        input,
        setTimeout(async () => {
          const query = input.value.trim();
          const endpoint = NS.config?.productLookupEndpoint;

          if (query.length < 2 || !endpoint) {
            renderResults(row, []);
            return;
          }

          const token = Symbol("product-search");
          requestTokens.set(input, token);

          try {
            const url = `${endpoint}?q=${encodeURIComponent(query)}`;
            const response = await fetch(url, { headers: { Accept: "application/json" } });

            if (!response.ok) {
              throw new Error(`Product lookup failed with status ${response.status}`);
            }

            const payload = await response.json();

            if (requestTokens.get(input) !== token) {
              return;
            }

            renderResults(row, payload?.data?.rows || []);
          } catch {
            if (requestTokens.get(input) !== token) {
              return;
            }

            renderResults(row, []);
          }
        }, 250)
      );
    });

    if (priceInput) {
      priceInput.addEventListener("input", () => {
        NS.syncFloorPriceGuard?.(row);
        NS.updateSummary?.();
      });

      priceInput.addEventListener("blur", () => {
        NS.syncFloorPriceGuard?.(row);
      });
    }

    if (row.dataset.searchOutsideBound !== "1") {
      row.dataset.searchOutsideBound = "1";

      document.addEventListener("click", (event) => {
        if (!row.contains(event.target)) {
          renderResults(row, []);
        }
      });
    }
  };
})();
