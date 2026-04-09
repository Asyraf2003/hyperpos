(() => {
  const config = window.procurementCreateConfig;
  const form = document.querySelector("[data-procurement-create-form='1']");
  const container = document.getElementById("procurement-line-items");
  const addButton = document.getElementById("add-procurement-line");
  const template = document.getElementById("procurement-line-template");
  const tanggalTerimaInput = document.getElementById("tanggal_terima");
  const autoReceiveInputs = document.querySelectorAll('input[name="auto_receive"]');

  if (!config || !form || !container || !addButton || !template) return;

  let nextIndex = Number.parseInt(container.dataset.nextIndex || "0", 10);
  let activeLineItem = null;

  const esc = (value) => String(value ?? "").replace(/[&<>"']/g, (char) => ({
    "&": "&amp;",
    "<": "&lt;",
    ">": "&gt;",
    '"': "&quot;",
    "'": "&#39;"
  }[char]));

  const lineItems = () => Array.from(container.querySelectorAll("[data-line-item]"));

  const headerFields = () =>
    Array.from(form.querySelectorAll("[data-procurement-header-field]"))
      .filter((field) => field instanceof HTMLElement);

  const getLineFields = (item) => ({
    product: item.querySelector("[data-product-search]"),
    qty: item.querySelector("[data-qty-input]"),
    total: item.querySelector("[data-money-display]")
  });

  const focusField = (field, select = true) => {
    if (!(field instanceof HTMLElement)) return;

    window.requestAnimationFrame(() => {
      field.focus();

      if (select && typeof field.select === "function") {
        field.select();
      }
    });
  };

  const setActiveLine = (item) => {
    lineItems().forEach((line) => {
      line.classList.remove("border-primary", "shadow-sm");
    });

    if (!item) {
      activeLineItem = null;
      return;
    }

    item.classList.add("border-primary", "shadow-sm");
    activeLineItem = item;
  };

  const syncLineNumbers = () => {
    lineItems().forEach((item, index) => {
      const lineNo = String(index + 1);
      const lineNoInput = item.querySelector("[data-line-no]");
      const lineLabels = item.querySelectorAll("[data-line-label]");

      if (lineNoInput) {
        lineNoInput.value = lineNo;
      }

      lineLabels.forEach((label) => {
        label.textContent = lineNo;
      });
    });
  };

  const updateRemoveButtons = () => {
    const items = lineItems();

    items.forEach((item) => {
      const button = item.querySelector("[data-remove-line]");
      if (!button) return;

      button.disabled = items.length === 1;
    });
  };

  const closeAllResults = () => {
    container.querySelectorAll("[data-product-results]").forEach((box) => {
      box.classList.add("d-none");
      box.innerHTML = "";
    });
  };

  const updateTanggalTerimaState = () => {
    const selected = document.querySelector('input[name="auto_receive"]:checked');
    const enabled = selected && selected.value === "1";

    if (!tanggalTerimaInput) return;
    tanggalTerimaInput.disabled = !enabled;
  };

  const initMoneyInput = (item) => {
    if (!window.AdminMoneyInput) return;

    const display = item.querySelector("[data-money-display]");
    const raw = item.querySelector("[data-money-raw]");

    window.AdminMoneyInput.bindMoneyPair(display, raw);
  };

  const initQtyInput = (item) => {
    const qtyInput = item.querySelector("[data-qty-input]");
    if (!qtyInput) return;

    const syncQty = () => {
      qtyInput.value = window.AdminMoneyInput
        ? window.AdminMoneyInput.digitsOnly(qtyInput.value)
        : String(qtyInput.value ?? "").replace(/\D+/g, "");
    };

    qtyInput.addEventListener("input", syncQty);
    qtyInput.addEventListener("blur", syncQty);
  };

  const isLineCompletelyEmpty = (item) => {
    const hiddenProductId = item.querySelector("[data-product-id]");
    const productSearch = item.querySelector("[data-product-search]");
    const qtyInput = item.querySelector("[data-qty-input]");
    const moneyRaw = item.querySelector("[data-money-raw]");
    const moneyDisplay = item.querySelector("[data-money-display]");

    const productId = String(hiddenProductId?.value ?? "").trim();
    const productText = String(productSearch?.value ?? "").trim();
    const qty = String(qtyInput?.value ?? "").replace(/\D+/g, "");
    const totalRaw = String(moneyRaw?.value ?? "").trim();
    const totalDisplay = String(moneyDisplay?.value ?? "").trim();

    return (
      productId === "" &&
      productText === "" &&
      (qty === "" || qty === "1") &&
      totalRaw === "" &&
      totalDisplay === ""
    );
  };

  const pruneEmptyLinesBeforeSubmit = () => {
    const items = lineItems();
    const removable = items.filter((item) => isLineCompletelyEmpty(item));

    removable.forEach((item) => item.remove());

    syncLineNumbers();
    updateRemoveButtons();
  };

  const buildLineHtml = (index, lineNo) =>
    template.innerHTML
      .replaceAll("__INDEX__", String(index))
      .replaceAll("__LINE_NO__", String(lineNo));

  const appendLine = () => {
    const html = buildLineHtml(nextIndex, lineItems().length + 1);
    container.insertAdjacentHTML("beforeend", html);
    nextIndex += 1;

    const items = lineItems();
    const lastItem = items[items.length - 1];

    if (lastItem) {
      initLineItem(lastItem);
      syncLineNumbers();
      updateRemoveButtons();
      setActiveLine(lastItem);
    }

    return lastItem;
  };

  const moveHeaderFocus = (currentField, direction) => {
    const fields = headerFields();
    const index = fields.indexOf(currentField);
    if (index === -1) return;

    const target = fields[index + direction];
    if (target) {
      focusField(target);
      return;
    }

    if (direction > 0) {
      const firstLine = activeLineItem || lineItems()[0];
      if (!firstLine) return;

      setActiveLine(firstLine);
      focusField(getLineFields(firstLine).product);
    }
  };

  const moveLineFocus = (item, fieldName, direction) => {
    const items = lineItems();
    const itemIndex = items.indexOf(item);
    if (itemIndex === -1) return;

    const fields = getLineFields(item);
    const order = ["product", "qty", "total"];
    const currentIndex = order.indexOf(fieldName);
    if (currentIndex === -1) return;

    const nextFieldName = order[currentIndex + direction];

    if (nextFieldName) {
      focusField(fields[nextFieldName]);
      return;
    }

    if (direction < 0) {
      if (fieldName === "product") {
        const headers = headerFields();
        const lastHeader = headers[headers.length - 1];
        if (lastHeader) {
          focusField(lastHeader);
        }
      }
      return;
    }

    const nextItem = items[itemIndex + 1] || appendLine();
    if (!nextItem) return;

    setActiveLine(nextItem);
    focusField(getLineFields(nextItem).product);
  };

  const removeCurrentLineIfEmpty = (item) => {
    const items = lineItems();
    if (items.length <= 1 || !isLineCompletelyEmpty(item)) {
      return;
    }

    const itemIndex = items.indexOf(item);
    item.remove();

    syncLineNumbers();
    updateRemoveButtons();

    const remaining = lineItems();
    const fallback = remaining[itemIndex] || remaining[itemIndex - 1] || remaining[0];

    if (fallback) {
      setActiveLine(fallback);
      focusField(getLineFields(fallback).product);
    }
  };

  const attachSharedShortcuts = (field, item, fieldName) => {
    if (!field) return;

    field.addEventListener("focus", () => setActiveLine(item));

    field.addEventListener("keydown", (event) => {
      if ((event.ctrlKey || event.metaKey) && event.key.toLowerCase() === "s") {
        event.preventDefault();
        form.requestSubmit();
        return;
      }

      if (event.ctrlKey && event.key === "Enter") {
        event.preventDefault();
        const newItem = appendLine();
        if (newItem) {
          focusField(getLineFields(newItem).product);
        }
        return;
      }

      if (event.ctrlKey && event.key === "Backspace") {
        event.preventDefault();
        removeCurrentLineIfEmpty(item);
        return;
      }

      if (event.key !== "Enter") return;
      if (event.ctrlKey || event.altKey || event.metaKey) return;

      event.preventDefault();
      moveLineFocus(item, fieldName, event.shiftKey ? -1 : 1);
    });
  };

  const initProductLookup = (item) => {
    const searchInput = item.querySelector("[data-product-search]");
    const hiddenInput = item.querySelector("[data-product-id]");
    const resultsBox = item.querySelector("[data-product-results]");
    const qtyInput = item.querySelector("[data-qty-input]");

    if (!searchInput || !hiddenInput || !resultsBox) return;

    let debounceTimer = null;
    let requestCounter = 0;
    let activeChoiceIndex = -1;

    const choiceButtons = () => Array.from(resultsBox.querySelectorAll("[data-product-choice]"));

    const syncActiveChoice = () => {
      choiceButtons().forEach((button, index) => {
        button.classList.toggle("active", index === activeChoiceIndex);
      });
    };

    const hideResults = () => {
      resultsBox.innerHTML = "";
      resultsBox.classList.add("d-none");
      activeChoiceIndex = -1;
    };

    const selectProduct = (row) => {
      hiddenInput.value = row.id || "";
      searchInput.value = row.label || "";
      hideResults();
      focusField(qtyInput);
    };

    const renderResults = (rows) => {
      if (!rows.length) {
        resultsBox.innerHTML = '<div class="list-group-item text-muted">Produk tidak ditemukan.</div>';
        resultsBox.classList.remove("d-none");
        activeChoiceIndex = -1;
        return;
      }

      resultsBox.innerHTML = rows.map((row) => `
        <button type="button" class="list-group-item list-group-item-action" data-product-choice='${JSON.stringify(row).replace(/'/g, "&apos;")}'>
          <div class="fw-semibold">${esc(row.nama_barang)}</div>
          <small class="text-muted">${esc(row.merek)}${row.ukuran !== null ? " - " + esc(row.ukuran) : ""}${row.kode_barang ? " (" + esc(row.kode_barang) + ")" : ""}</small>
        </button>
      `).join("");

      resultsBox.classList.remove("d-none");
      activeChoiceIndex = 0;
      syncActiveChoice();

      choiceButtons().forEach((button) => {
        button.addEventListener("click", () => {
          const raw = button.getAttribute("data-product-choice");
          if (!raw) return;

          selectProduct(JSON.parse(raw.replace(/&apos;/g, "'")));
        });
      });
    };

    const fetchResults = async () => {
      const query = searchInput.value.trim();
      hiddenInput.value = "";

      if (query.length < 2) {
        hideResults();
        return;
      }

      const currentRequest = ++requestCounter;
      const response = await fetch(`${config.lookupEndpoint}?q=${encodeURIComponent(query)}`, {
        headers: { Accept: "application/json" }
      });

      const json = await response.json();

      if (currentRequest !== requestCounter) {
        return;
      }

      if (!response.ok || !json.success) {
        resultsBox.innerHTML = '<div class="list-group-item text-danger">Gagal memuat produk.</div>';
        resultsBox.classList.remove("d-none");
        activeChoiceIndex = -1;
        return;
      }

      renderResults(json.data?.rows || []);
    };

    searchInput.addEventListener("input", () => {
      clearTimeout(debounceTimer);
      debounceTimer = setTimeout(fetchResults, 250);
    });

    searchInput.addEventListener("focus", () => {
      setActiveLine(item);

      if (searchInput.value.trim().length >= 2) {
        fetchResults();
      }
    });

    searchInput.addEventListener("keydown", (event) => {
      if ((event.ctrlKey || event.metaKey) && event.key.toLowerCase() === "s") {
        event.preventDefault();
        form.requestSubmit();
        return;
      }

      if (event.ctrlKey && event.key === "Enter") {
        event.preventDefault();
        const newItem = appendLine();
        if (newItem) {
          focusField(getLineFields(newItem).product);
        }
        return;
      }

      if (event.ctrlKey && event.key === "Backspace") {
        event.preventDefault();
        removeCurrentLineIfEmpty(item);
        return;
      }

      const buttons = choiceButtons();

      if (event.key === "ArrowDown" && buttons.length) {
        event.preventDefault();
        activeChoiceIndex = Math.min(activeChoiceIndex + 1, buttons.length - 1);
        syncActiveChoice();
        return;
      }

      if (event.key === "ArrowUp" && buttons.length) {
        event.preventDefault();
        activeChoiceIndex = Math.max(activeChoiceIndex - 1, 0);
        syncActiveChoice();
        return;
      }

      if (event.key === "Escape") {
        event.preventDefault();
        hideResults();
        return;
      }

      if (event.key !== "Enter") return;
      if (event.ctrlKey || event.altKey || event.metaKey) return;

      event.preventDefault();

      if (buttons.length && activeChoiceIndex >= 0 && buttons[activeChoiceIndex]) {
        buttons[activeChoiceIndex].click();
        return;
      }

      if (event.shiftKey) {
        moveLineFocus(item, "product", -1);
        return;
      }

      if (hiddenInput.value.trim() !== "") {
        moveLineFocus(item, "product", 1);
        return;
      }

      if (searchInput.value.trim().length >= 2) {
        fetchResults();
      }
    });
  };

  const initLineItem = (item) => {
    initProductLookup(item);
    initQtyInput(item);
    initMoneyInput(item);

    const { qty, total } = getLineFields(item);
    attachSharedShortcuts(qty, item, "qty");
    attachSharedShortcuts(total, item, "total");
  };

  headerFields().forEach((field) => {
    field.addEventListener("keydown", (event) => {
      if ((event.ctrlKey || event.metaKey) && event.key.toLowerCase() === "s") {
        event.preventDefault();
        form.requestSubmit();
        return;
      }

      if (event.key !== "Enter") return;
      if (event.ctrlKey || event.altKey || event.metaKey) return;

      event.preventDefault();
      moveHeaderFocus(field, event.shiftKey ? -1 : 1);
    });
  });

  addButton.addEventListener("click", () => {
    const newItem = appendLine();
    if (newItem) {
      focusField(getLineFields(newItem).product);
    }
  });

  container.addEventListener("click", (event) => {
    const button = event.target.closest("[data-remove-line]");
    if (!button) return;

    const item = button.closest("[data-line-item]");
    if (!item) return;

    if (lineItems().length <= 1) return;

    item.remove();
    syncLineNumbers();
    updateRemoveButtons();

    const fallback = lineItems()[0];
    if (fallback) {
      setActiveLine(fallback);
    }
  });

  document.addEventListener("click", (event) => {
    if (!event.target.closest("[data-line-item]")) {
      closeAllResults();
    }
  });

  autoReceiveInputs.forEach((input) => {
    input.addEventListener("change", updateTanggalTerimaState);
  });

  form.addEventListener("submit", () => {
    pruneEmptyLinesBeforeSubmit();
  });

  lineItems().forEach(initLineItem);
  syncLineNumbers();
  updateRemoveButtons();
  updateTanggalTerimaState();

  const firstLine = lineItems()[0];
  if (firstLine) {
    setActiveLine(firstLine);
  }

  const firstHeader = headerFields()[0];
  if (firstHeader) {
    focusField(firstHeader);
  }
})();
