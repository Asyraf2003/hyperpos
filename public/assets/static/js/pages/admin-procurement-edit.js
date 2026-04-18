(() => {
  const config = window.procurementCreateConfig;
  const form = document.querySelector("[data-procurement-edit-form='1']");
  const container = document.getElementById("procurement-line-items");
  const addButton = document.getElementById("add-procurement-line");
  const template = document.getElementById("procurement-line-template");
  const tanggalTerimaInput = document.getElementById("tanggal_terima");
  const autoReceiveInputs = document.querySelectorAll('input[name="auto_receive"]');

  if (!config || !form || !container || !addButton || !template) return;

  const DRAFT_KEY = form?.dataset.procurementDraftKey || "admin.procurement.edit-supplier-invoice.draft.v1";

  let nextIndex = Number.parseInt(container.dataset.nextIndex || "0", 10);
  let activeLineItem = null;
  let saveTimer = null;

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

      if (lineNoInput) {
        lineNoInput.value = lineNo;
      }
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

  const formatMoneyDisplay = (rawValue, displayValue = "") => {
    const raw = String(rawValue ?? "").trim();
    if (raw !== "" && /^\d+$/.test(raw)) {
      return Number.parseInt(raw, 10).toLocaleString("id-ID");
    }

    return String(displayValue ?? "");
  };

  const isLineCompletelyEmpty = (item) => {
    const hiddenProductId = item.querySelector("[data-product-id]");
    const qtyInput = item.querySelector("[data-qty-input]");
    const moneyRaw = item.querySelector("[data-money-raw]");
    const moneyDisplay = item.querySelector("[data-money-display]");

    const productId = String(hiddenProductId?.value ?? "").trim();
    const qty = String(qtyInput?.value ?? "").replace(/\D+/g, "");
    const totalRaw = String(moneyRaw?.value ?? "").trim();
    const totalDisplay = String(moneyDisplay?.value ?? "").trim();

    const hasSelectedProduct = productId !== "";
    const hasMeaningfulQty = qty !== "" && qty !== "1";
    const hasMeaningfulTotal = totalRaw !== "" || totalDisplay !== "";

    return !hasSelectedProduct && !hasMeaningfulQty && !hasMeaningfulTotal;
  };

  const ensureProductDuplicateFeedback = (item) => {
    let feedback = item.querySelector("[data-product-duplicate-feedback]");

    if (feedback) {
      return feedback;
    }

    feedback = document.createElement("div");
    feedback.className = "invalid-feedback d-block";
    feedback.setAttribute("data-product-duplicate-feedback", "1");

    const resultsBox = item.querySelector("[data-product-results]");
    if (resultsBox) {
      resultsBox.insertAdjacentElement("afterend", feedback);
      return feedback;
    }

    const searchInput = item.querySelector("[data-product-search]");
    if (searchInput) {
      searchInput.insertAdjacentElement("afterend", feedback);
      return feedback;
    }

    item.appendChild(feedback);
    return feedback;
  };

  const clearProductDuplicateFeedback = (item, searchInput = null) => {
    const field = searchInput || item.querySelector("[data-product-search]");
    if (field) {
      field.classList.remove("is-invalid");
    }

    const feedback = item.querySelector("[data-product-duplicate-feedback]");
    if (feedback) {
      feedback.remove();
    }
  };

  const invalidateManualProductEntry = (item, searchInput, hiddenInput) => {
    clearProductDuplicateFeedback(item, searchInput);
    hiddenInput.value = "";
    searchInput.dataset.selectedProductId = "";
  };

  const lineNoOfItem = (item, fallbackIndex = 0) => {
    const value = String(item.querySelector("[data-line-no]")?.value ?? "").trim();
    const parsed = Number.parseInt(value, 10);

    return Number.isFinite(parsed) && parsed > 0 ? parsed : fallbackIndex + 1;
  };

  const duplicateProductMessage = (firstLineNo, currentLineNo) =>
    `Baris ${currentLineNo}: produk ini sudah dipakai di baris ${firstLineNo}. Satu produk hanya boleh satu kali per faktur.`;

  const findDuplicateProductLineNo = (currentItem, productId) => {
    const normalized = String(productId ?? "").trim();
    if (normalized === "") {
      return null;
    }

    const items = lineItems();

    for (let index = 0; index < items.length; index += 1) {
      const item = items[index];
      if (item === currentItem) {
        continue;
      }

      const otherProductId = String(item.querySelector("[data-product-id]")?.value ?? "").trim();
      if (otherProductId === normalized) {
        return lineNoOfItem(item, index);
      }
    }

    return null;
  };

  const validateDuplicateProductsBeforeSubmit = () => {
    let isValid = true;
    const seen = new Map();

    lineItems().forEach((item) => {
      clearProductDuplicateFeedback(item);
    });

    lineItems().forEach((item, index) => {
      const searchInput = item.querySelector("[data-product-search]");
      const productId = String(item.querySelector("[data-product-id]")?.value ?? "").trim();
      const typedLabel = String(searchInput?.value ?? "").trim();
      const currentLineNo = lineNoOfItem(item, index);

      if (typedLabel !== "" && productId === "") {
        isValid = false;

        if (searchInput) {
          searchInput.classList.add("is-invalid");
        }

        const feedback = ensureProductDuplicateFeedback(item);
        feedback.textContent = `Baris ${currentLineNo}: pilih produk dari daftar hasil pencarian, jangan hanya tempel atau ketik teks.`;
        return;
      }

      if (productId === "") {
        return;
      }

      if (seen.has(productId)) {
        isValid = false;

        if (searchInput) {
          searchInput.classList.add("is-invalid");
        }

        const feedback = ensureProductDuplicateFeedback(item);
        feedback.textContent = duplicateProductMessage(seen.get(productId), currentLineNo);
        return;
      }

      seen.set(productId, currentLineNo);
    });

    return isValid;
  };

  const pruneEmptyLinesBeforeSubmit = () => {
    const items = lineItems();
    const removable = items.filter((item) => isLineCompletelyEmpty(item));

    removable.forEach((item) => {
      item.querySelectorAll("[name]").forEach((field) => {
        field.disabled = true;
      });

      item.remove();
    });

    syncLineNumbers();
    updateRemoveButtons();
  };

  const buildLineHtml = (index, lineNo) =>
    template.innerHTML
      .replaceAll("__INDEX__", String(index))
      .replaceAll("__LINE_NO__", String(lineNo));

  const populateLineItem = (item, line) => {
    const previousLineIdInput = item.querySelector("[data-previous-line-id]");
    const lineNoInput = item.querySelector("[data-line-no]");
    const productIdInput = item.querySelector("[data-product-id]");
    const productSearchInput = item.querySelector("[data-product-search]");
    const qtyInput = item.querySelector("[data-qty-input]");
    const moneyRawInput = item.querySelector("[data-money-raw]");
    const moneyDisplayInput = item.querySelector("[data-money-display]");

    if (previousLineIdInput) {
      previousLineIdInput.value = String(line.previous_line_id ?? "");
    }

    if (lineNoInput) {
      lineNoInput.value = String(line.line_no ?? "");
    }

    if (productIdInput) {
      productIdInput.value = String(line.product_id ?? "");
    }

    if (productSearchInput) {
      productSearchInput.value = String(line.product_label ?? line.selected_label ?? "");
      productSearchInput.dataset.selectedProductId = String(line.product_id ?? "");
      productSearchInput.dataset.selectedLabel = productSearchInput.value;
    }

    if (qtyInput) {
      qtyInput.value = String(line.qty_pcs ?? "1");
    }

    if (moneyRawInput) {
      moneyRawInput.value = String(line.line_total_rupiah ?? "");
    }

    if (moneyDisplayInput) {
      moneyDisplayInput.value = formatMoneyDisplay(
        line.line_total_rupiah ?? "",
        line.line_total_display ?? line.line_total_display_formatted ?? ""
      );
    }
  };

  const mountLineItem = (item, lineData = null) => {
    initLineItem(item);

    if (lineData) {
      populateLineItem(item, lineData);
    }

    syncLineNumbers();
    updateRemoveButtons();
    setActiveLine(item);

    return item;
  };

  const insertLine = (lineData = null, position = "bottom") => {
    const html = buildLineHtml(nextIndex, lineItems().length + 1);

    if (position === "top" && container.firstElementChild) {
      container.insertAdjacentHTML("afterbegin", html);
    } else {
      container.insertAdjacentHTML("beforeend", html);
    }

    nextIndex += 1;

    const item = position === "top"
      ? container.querySelector("[data-line-item]")
      : lineItems()[lineItems().length - 1];

    if (!item) return null;

    return mountLineItem(item, lineData);
  };

  const getTopLine = () => lineItems()[0] || null;

  const ensureTopWorkingLine = () => {
    const topLine = getTopLine();

    if (topLine && isLineCompletelyEmpty(topLine)) {
      setActiveLine(topLine);
      return topLine;
    }

    return insertLine(null, "top");
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
    scheduleDraftSave();

    const remaining = lineItems();
    const fallback = remaining[itemIndex] || remaining[itemIndex - 1] || remaining[0];

    if (fallback) {
      setActiveLine(fallback);
      focusField(getLineFields(fallback).product);
    }
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
      const workingLine = ensureTopWorkingLine();
      if (!workingLine) return;

      setActiveLine(workingLine);
      focusField(getLineFields(workingLine).product);
      scheduleDraftSave();
    }
  };

  const moveLineFocus = (item, fieldName, direction) => {
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

    const workingLine = ensureTopWorkingLine();
    if (!workingLine) return;

    setActiveLine(workingLine);
    focusField(getLineFields(workingLine).product);
    scheduleDraftSave();
  };

  const readDraft = () => {
    try {
      const raw = window.localStorage.getItem(DRAFT_KEY);
      if (!raw) return null;

      const parsed = JSON.parse(raw);
      return parsed && typeof parsed === "object" ? parsed : null;
    } catch (_error) {
      return null;
    }
  };

  const writeDraft = (payload) => {
    try {
      window.localStorage.setItem(DRAFT_KEY, JSON.stringify(payload));
      return true;
    } catch (_error) {
      return false;
    }
  };

  const clearDraft = () => {
    try {
      window.localStorage.removeItem(DRAFT_KEY);
    } catch (_error) {
      // ignore localStorage failures
    }
  };

  const updateDraftPanelState = () => {
    // draft dipulihkan otomatis tanpa panel aksi
  };

  const collectDraftPayload = () => {
    const selectedAutoReceive = document.querySelector('input[name="auto_receive"]:checked');

    return {
      saved_at: new Date().toISOString(),
      header: {
        nomor_faktur: String(document.getElementById("nomor_faktur")?.value ?? ""),
        nama_pt_pengirim: String(document.getElementById("nama_pt_pengirim")?.value ?? ""),
        tanggal_pengiriman: String(document.getElementById("tanggal_pengiriman")?.value ?? ""),
        tanggal_terima: String(document.getElementById("tanggal_terima")?.value ?? ""),
        auto_receive: selectedAutoReceive ? String(selectedAutoReceive.value) : "1"
      },
      lines: lineItems()
        .filter((item) => !isLineCompletelyEmpty(item))
        .map((item) => ({
          previous_line_id: String(item.querySelector("[data-previous-line-id]")?.value ?? ""),
          line_no: String(item.querySelector("[data-line-no]")?.value ?? ""),
          product_id: String(item.querySelector("[data-product-id]")?.value ?? ""),
          product_label: String(item.querySelector("[data-product-search]")?.value ?? ""),
          qty_pcs: String(item.querySelector("[data-qty-input]")?.value ?? ""),
          line_total_rupiah: String(item.querySelector("[data-money-raw]")?.value ?? ""),
          line_total_display: String(item.querySelector("[data-money-display]")?.value ?? "")
        }))
    };
  };

  const persistDraftNow = () => {
    const payload = collectDraftPayload();
    const hasMeaningfulHeader =
      payload.header.nomor_faktur.trim() !== "" ||
      payload.header.nama_pt_pengirim.trim() !== "";

    if (!hasMeaningfulHeader && payload.lines.length === 0) {
      clearDraft();
      return;
    }

    writeDraft(payload);
    updateDraftPanelState();
  };

  const scheduleDraftSave = () => {
    window.clearTimeout(saveTimer);
    saveTimer = window.setTimeout(persistDraftNow, 300);
  };

  const restoreDraft = () => {
    const draft = readDraft();
    if (!draft) {
      updateDraftPanelState();
      return;
    }

    const header = draft.header || {};
    const lines = Array.isArray(draft.lines) ? draft.lines : [];

    const nomorFakturInput = document.getElementById("nomor_faktur");
    const namaPtInput = document.getElementById("nama_pt_pengirim");
    const tanggalPengirimanInput = document.getElementById("tanggal_pengiriman");
    const tanggalTerimaInputLocal = document.getElementById("tanggal_terima");
    const autoReceiveValue = String(header.auto_receive ?? "1");

    if (nomorFakturInput) nomorFakturInput.value = String(header.nomor_faktur ?? "");
    if (namaPtInput) namaPtInput.value = String(header.nama_pt_pengirim ?? "");
    if (tanggalPengirimanInput) tanggalPengirimanInput.value = String(header.tanggal_pengiriman ?? "");
    if (tanggalTerimaInputLocal) tanggalTerimaInputLocal.value = String(header.tanggal_terima ?? "");

    const autoReceiveTarget = document.querySelector(`input[name="auto_receive"][value="${autoReceiveValue}"]`);
    if (autoReceiveTarget instanceof HTMLInputElement) {
      autoReceiveTarget.checked = true;
    }

    container.innerHTML = "";
    nextIndex = 0;

    if (lines.length === 0) {
      insertLine(null, "bottom");
    } else {
      lines.forEach((line) => insertLine(line, "bottom"));
    }

    updateTanggalTerimaState();
    updateDraftPanelState();

    const topLine = getTopLine();
    if (topLine && !isLineCompletelyEmpty(topLine)) {
      const workingLine = insertLine(null, "top");
      if (workingLine) {
        focusField(getLineFields(workingLine).product);
        return;
      }
    }

    const firstHeader = headerFields()[0];
    if (firstHeader) {
      focusField(firstHeader);
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
        const workingLine = ensureTopWorkingLine();
        if (workingLine) {
          focusField(getLineFields(workingLine).product);
        }
        scheduleDraftSave();
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
    const createProductButton = () => resultsBox.querySelector("[data-create-product-action]");

    const openCreateProduct = () => {
      const href = String(config.createProductUrl ?? "").trim();
      if (href === "") return;

      window.location.assign(href);
    };

    const syncActiveChoice = () => {
      choiceButtons().forEach((button, index) => {
        const isActive = index === activeChoiceIndex;
        button.classList.toggle("active", isActive);

        const meta = button.querySelector("small");
        if (meta) {
          meta.classList.toggle("text-white", isActive);
          meta.classList.toggle("text-muted", !isActive);
        }
      });
    };

    const hideResults = () => {
      resultsBox.innerHTML = "";
      resultsBox.classList.add("d-none");
      activeChoiceIndex = -1;
    };

    const selectProduct = (row) => {
      const productId = String(row.id ?? "").trim();
      const currentLineNo = lineNoOfItem(item);
      const duplicateLineNo = findDuplicateProductLineNo(item, productId);
      const previousSelectedProductId = String(searchInput.dataset.selectedProductId ?? "").trim();
      const previousSelectedLabel = String(searchInput.dataset.selectedLabel ?? "").trim();

      if (duplicateLineNo !== null) {
        hideResults();
        searchInput.classList.add("is-invalid");

        const feedback = ensureProductDuplicateFeedback(item);
        feedback.textContent = duplicateProductMessage(duplicateLineNo, currentLineNo);

        hiddenInput.value = previousSelectedProductId;
        searchInput.value = previousSelectedLabel;

        scheduleDraftSave();
        focusField(searchInput, false);
        return;
      }

      clearProductDuplicateFeedback(item, searchInput);
      hiddenInput.value = row.id || "";
      searchInput.value = row.label || "";
      searchInput.dataset.selectedProductId = hiddenInput.value;
      searchInput.dataset.selectedLabel = searchInput.value;
      hideResults();
      scheduleDraftSave();
      focusField(qtyInput);
    };

    const renderResults = (rows) => {
      if (!rows.length) {
        const createHref = String(config.createProductUrl ?? "").trim();

        resultsBox.innerHTML = createHref !== ""
          ? '<button type="button" class="list-group-item list-group-item-action" data-create-product-action><div class="fw-semibold">Produk tidak ditemukan</div><small class="text-muted">Tekan Enter untuk buat product baru.</small></button>'
          : '<div class="list-group-item text-muted">Produk tidak ditemukan.</div>';

        resultsBox.classList.remove("d-none");
        activeChoiceIndex = -1;

        const button = createProductButton();
        if (button) {
          button.addEventListener("click", openCreateProduct);
        }

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
        scheduleDraftSave();
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
      const selectedLabel = String(searchInput.dataset.selectedLabel ?? "").trim();
      const currentValue = String(searchInput.value ?? "").trim();

      if (currentValue !== selectedLabel) {
        invalidateManualProductEntry(item, searchInput, hiddenInput);
      } else {
        clearProductDuplicateFeedback(item, searchInput);
      }

      clearTimeout(debounceTimer);
      debounceTimer = setTimeout(fetchResults, 250);
    });

    searchInput.addEventListener("paste", () => {
      window.requestAnimationFrame(() => {
        const selectedLabel = String(searchInput.dataset.selectedLabel ?? "").trim();
        const currentValue = String(searchInput.value ?? "").trim();

        if (currentValue !== selectedLabel) {
          invalidateManualProductEntry(item, searchInput, hiddenInput);
        }
      });
    });

    searchInput.addEventListener("blur", () => {
      const selectedLabel = String(searchInput.dataset.selectedLabel ?? "").trim();
      const currentValue = String(searchInput.value ?? "").trim();

      if (currentValue === "") {
        invalidateManualProductEntry(item, searchInput, hiddenInput);
        searchInput.dataset.selectedLabel = "";
        return;
      }

      if (currentValue !== selectedLabel) {
        invalidateManualProductEntry(item, searchInput, hiddenInput);
      }
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

      if (event.ctrlKey && event.key.toLowerCase() === "k") {
        event.preventDefault();
        openCreateProduct();
        return;
      }

      if (event.ctrlKey && event.key === "Enter") {
        event.preventDefault();
        const workingLine = ensureTopWorkingLine();
        if (workingLine) {
          focusField(getLineFields(workingLine).product);
        }
        scheduleDraftSave();
        return;
      }

      if (event.ctrlKey && event.key === "Backspace") {
        event.preventDefault();
        removeCurrentLineIfEmpty(item);
        return;
      }

      const buttons = choiceButtons();
      const createButton = createProductButton();

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

      if (createButton instanceof HTMLElement) {
        createButton.click();
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
    const workingLine = ensureTopWorkingLine();
    if (workingLine) {
      focusField(getLineFields(workingLine).product);
    }
    scheduleDraftSave();
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
    scheduleDraftSave();

    const topLine = getTopLine();
    if (topLine) {
      setActiveLine(topLine);
    }
  });

  document.addEventListener("click", (event) => {
    if (!event.target.closest("[data-line-item]")) {
      closeAllResults();
    }
  });

  autoReceiveInputs.forEach((input) => {
    input.addEventListener("change", () => {
      updateTanggalTerimaState();
      scheduleDraftSave();
    });
  });

  form.addEventListener("input", scheduleDraftSave);
  form.addEventListener("change", scheduleDraftSave);

  form.addEventListener("submit", (event) => {
    pruneEmptyLinesBeforeSubmit();

    if (!validateDuplicateProductsBeforeSubmit()) {
      event.preventDefault();
      persistDraftNow();

      const invalidItem = lineItems().find((item) =>
        item.querySelector("[data-product-search].is-invalid")
      );

      if (invalidItem) {
        setActiveLine(invalidItem);
        focusField(invalidItem.querySelector("[data-product-search]"), false);
      }

      return;
    }

    persistDraftNow();
  });

  lineItems().forEach(initLineItem);
  syncLineNumbers();
  updateRemoveButtons();
  updateTanggalTerimaState();
  updateDraftPanelState();

  const initialDraft = readDraft();
  if (initialDraft) {
    restoreDraft();
  } else {
    const workingLine = ensureTopWorkingLine();
    if (workingLine) {
      focusField(document.getElementById("nomor_faktur"));
    }
  }
})();
