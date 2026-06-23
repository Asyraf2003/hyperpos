(() => {
  const NS = (window.CashierNoteWorkspace = window.CashierNoteWorkspace || {});
  const timers = new WeakMap();
  const requestTokens = new WeakMap();
  const activeChoiceIndexes = new WeakMap();

  const digits = (value) =>
    Number.parseInt(String(value || "").replace(/\D+/g, "") || "0", 10);
  const format = (value) => Number(value || 0).toLocaleString("id-ID");

  const productLineScopes = (row) =>
    Array.from(row.querySelectorAll("[data-product-line]"));

  const packageSearchInput = (row) => row.querySelector("[data-package-search]");
  const packageResults = (row) => row.querySelector("[data-package-results]");
  const packageSelectedSection = (row) => row.querySelector("[data-package-selected-section]");

  const setValue = (root, selector, value) => {
    const el = root.querySelector(selector);
    if (el && value !== undefined && value !== null) el.value = String(value);
  };

  const setText = (row, selector, value) => {
    const el = row.querySelector(selector);
    if (el) el.textContent = String(value || "-");
  };

  const clearResults = (row) => {
    const results = packageResults(row);
    if (!results) return;

    results.innerHTML = "";
    results.classList.add("d-none");
    activeChoiceIndexes.set(row, -1);
  };

  const resultButtons = (row) =>
    Array.from(packageResults(row)?.querySelectorAll("[data-package-choice]") || []);

  const setActiveChoice = (row, index) => {
    const buttons = resultButtons(row);

    if (!buttons.length) {
      activeChoiceIndexes.set(row, -1);
      return;
    }

    const nextIndex = Math.max(0, Math.min(index, buttons.length - 1));
    activeChoiceIndexes.set(row, nextIndex);

    buttons.forEach((button, buttonIndex) => {
      button.classList.toggle("active", buttonIndex === nextIndex);
    });
  };

  const ensureProductLineCount = (row, count) => {
    const targetCount = Math.max(1, Math.min(Number(count || 1), 3));

    while (productLineScopes(row).length < targetCount) {
      NS.addProductLine?.(row, {}, false);
    }

    while (productLineScopes(row).length > targetCount) {
      const scopes = productLineScopes(row);
      scopes[scopes.length - 1]?.remove();
    }

    NS.reindexProductLines?.(row);
  };

  const applyProductLine = (row, scope, line) => {
    if (!(scope instanceof HTMLElement)) return;

    const label = String(line?.label || line?.product_name || "").trim();

    setValue(scope, "[data-product-search]", label);
    setValue(scope, "[data-product-id]", line?.product_id || "");
    setValue(scope, "[data-price-basis]", "current_catalog");
    setValue(scope, "[data-qty-input]", line?.qty || "1");
    setValue(scope, 'input[name$="[unit_price_rupiah]"]', line?.unit_price_rupiah || "");

    scope.dataset.minimumUnitPriceRupiah = String(
      line?.minimum_unit_price_rupiah || line?.unit_price_rupiah || 0
    );

    NS.updateStockText?.(row, line?.available_stock || 0, scope);
  };

  NS.applyPackageTemplate = (row, item) => {
    if (!(row instanceof HTMLElement)) return;
    if ((row.dataset.itemType || "") !== "service_store_stock") return;

    const productLines = Array.isArray(item?.product_lines)
      ? item.product_lines.slice(0, 3)
      : [];

    row.dataset.serviceProductTemplateApplied = productLines.length > 0 ? "1" : "0";
    row.dataset.serviceTemplateAutofilled = productLines.length > 0 ? "1" : "0";
    row.dataset.selectedPackageId = String(item?.id || "");

    const input = packageSearchInput(row);
    if (input) input.value = String(item?.label || "");

    const service = item?.service || {};
    const serviceTemplate = item?.service_product_template || {};
    const serviceName = String(service?.name || serviceTemplate?.service_name || "").trim();
    const servicePrice = digits(
      service?.price_rupiah || serviceTemplate?.default_service_price_rupiah || 0
    );

    setValue(row, "[data-service-name]", serviceName);
    setValue(row, "[data-service-catalog-id]", service?.catalog_item_id || serviceTemplate?.service_catalog_item_id || "");
    setValue(row, "[data-service-default-fee-rupiah]", servicePrice > 0 ? servicePrice : "");
    setValue(row, "[data-service-price-raw]", servicePrice > 0 ? servicePrice : "0");
    setValue(row, "[data-service-price-display]", servicePrice > 0 ? format(servicePrice) : "");

    ensureProductLineCount(row, Math.max(productLines.length, 1));

    productLineScopes(row).forEach((scope, index) => {
      applyProductLine(row, scope, productLines[index] || {});
    });

    packageSelectedSection(row)?.classList.toggle("d-none", productLines.length === 0);
    setText(row, "[data-package-title]", serviceName !== "" ? `Paket ${serviceName}` : item?.label || "");
    setText(row, "[data-package-description]", item?.description || item?.label || "");
    setText(row, "[data-package-stock-text]", item?.stock_label || "");

    row.querySelector("[data-package-error]")?.classList.add("d-none");

    window.AdminMoneyInput?.bindBySelector?.(row);
    NS.syncFloorPriceGuard?.(row);
    NS.syncQtyGuard?.(row);
    NS.syncServiceDefaults?.(row);
    NS.updateSummary?.();
  };

  const renderResults = (row, rows) => {
    const results = packageResults(row);
    if (!results) return;

    results.innerHTML = "";

    if (!rows.length) {
      const empty = document.createElement("div");
      empty.className = "list-group-item small text-muted";
      empty.textContent = "Paket tidak ditemukan. Buat template paket dulu di admin.";
      results.appendChild(empty);
      results.classList.remove("d-none");
      activeChoiceIndexes.set(row, -1);
      return;
    }

    rows.forEach((item) => {
      const button = document.createElement("button");
      button.type = "button";
      button.className = "list-group-item list-group-item-action";
      button.dataset.packageChoice = "1";
      button.textContent = item.label || item.description || "Paket";
      button.addEventListener("click", () => {
        NS.applyPackageTemplate(row, item);
        clearResults(row);
        NS.focusElement?.(packageSearchInput(row), false);
      });
      results.appendChild(button);
    });

    results.classList.remove("d-none");
    setActiveChoice(row, 0);
  };

  const fetchPackages = async (row, input) => {
    const query = input.value.trim();
    const endpoint = NS.config?.packageLookupEndpoint;

    if (query.length < 2 || !endpoint) {
      clearResults(row);
      return;
    }

    const token = Symbol("package-search");
    requestTokens.set(input, token);

    try {
      const params = new URLSearchParams({ q: query });
      const separator = endpoint.includes("?") ? "&" : "?";
      const response = await fetch(`${endpoint}${separator}${params.toString()}`, {
        headers: { Accept: "application/json" },
      });

      if (!response.ok) {
        throw new Error(`Package lookup failed with status ${response.status}`);
      }

      const payload = await response.json();

      if (requestTokens.get(input) !== token) return;

      renderResults(row, payload?.data?.rows || []);
    } catch (_error) {
      if (requestTokens.get(input) === token) clearResults(row);
    }
  };

  const clearPackageState = (row) => {
    row.dataset.serviceProductTemplateApplied = "0";
    row.dataset.serviceTemplateAutofilled = "0";
    row.dataset.selectedPackageId = "";

    setValue(row, "[data-service-name]", "");
    setValue(row, "[data-service-catalog-id]", "");
    setValue(row, "[data-service-default-fee-rupiah]", "");
    setValue(row, "[data-service-price-raw]", "0");
    setValue(row, "[data-service-price-display]", "");

    productLineScopes(row).forEach((scope) => {
      setValue(scope, "[data-product-search]", "");
      setValue(scope, "[data-product-id]", "");
      setValue(scope, "[data-price-basis]", "current_catalog");
      setValue(scope, "[data-qty-input]", "1");
      setValue(scope, 'input[name$="[unit_price_rupiah]"]', "");
      scope.dataset.minimumUnitPriceRupiah = "0";
    });

    packageSelectedSection(row)?.classList.add("d-none");
    NS.updateSummary?.();
  };

  NS.bindPackageSearch = (row) => {
    if (!(row instanceof HTMLElement)) return;
    if ((row.dataset.itemType || "") !== "service_store_stock") return;
    if (row.dataset.packageSearchBound === "1") return;

    const input = packageSearchInput(row);
    if (!(input instanceof HTMLInputElement)) return;

    row.dataset.packageSearchBound = "1";

    input.addEventListener("input", () => {
      clearPackageState(row);
      clearTimeout(timers.get(input));
      timers.set(input, setTimeout(() => void fetchPackages(row, input), 250));
    });

    input.addEventListener("focus", () => {
      if (input.value.trim().length >= 2) {
        void fetchPackages(row, input);
      }
    });

    input.addEventListener("keydown", (event) => {
      const buttons = resultButtons(row);
      if (!buttons.length) return;

      const current = activeChoiceIndexes.get(row) ?? 0;

      if (event.key === "ArrowDown") {
        event.preventDefault();
        setActiveChoice(row, current + 1);
      } else if (event.key === "ArrowUp") {
        event.preventDefault();
        setActiveChoice(row, current - 1);
      } else if (event.key === "Enter") {
        event.preventDefault();
        buttons[activeChoiceIndexes.get(row) ?? 0]?.click();
      } else if (event.key === "Escape") {
        clearResults(row);
      }
    });

    document.addEventListener("click", (event) => {
      if (event.target instanceof Node && !row.contains(event.target)) clearResults(row);
    });
  };
})();
