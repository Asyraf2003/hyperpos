(() => {
  const config = window.procurementCreateConfig;
  if (!config) return;

  const container = document.getElementById("procurement-line-items");
  const addButton = document.getElementById("add-procurement-line");
  const template = document.getElementById("procurement-line-template");
  const tanggalTerimaInput = document.getElementById("tanggal_terima");
  const autoReceiveInputs = document.querySelectorAll('input[name="auto_receive"]');

  if (!container || !addButton || !template) {
    return;
  }

  let nextIndex = Number.parseInt(container.dataset.nextIndex || "0", 10);

  const esc = (v) => String(v ?? "").replace(/[&<>"']/g, (m) => ({
    "&": "&amp;",
    "<": "&lt;",
    ">": "&gt;",
    '"': "&quot;",
    "'": "&#39;"
  }[m]));


  const updateRemoveButtons = () => {
    const items = container.querySelectorAll("[data-line-item]");
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

  const renderResults = (box, rows, onSelect) => {
    if (!rows.length) {
      box.innerHTML = '<div class="list-group-item text-muted">Product tidak ditemukan.</div>';
      box.classList.remove("d-none");
      return;
    }

    box.innerHTML = rows.map((row) => `
      <button type="button" class="list-group-item list-group-item-action" data-product-choice='${JSON.stringify(row).replace(/'/g, "&apos;")}'>
        <div class="fw-semibold">${esc(row.nama_barang)}</div>
        <small class="text-muted">${esc(row.merek)}${row.ukuran !== null ? " - " + esc(row.ukuran) : ""}${row.kode_barang ? " (" + esc(row.kode_barang) + ")" : ""}</small>
      </button>
    `).join("");

    box.classList.remove("d-none");

    box.querySelectorAll("[data-product-choice]").forEach((button) => {
      button.addEventListener("click", () => {
        const raw = button.getAttribute("data-product-choice");
        if (!raw) return;

        const data = JSON.parse(raw.replace(/&apos;/g, "'"));
        onSelect(data);
      });
    });
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

  const initProductLookup = (item) => {
    const searchInput = item.querySelector("[data-product-search]");
    const hiddenInput = item.querySelector("[data-product-id]");
    const resultsBox = item.querySelector("[data-product-results]");

    if (!searchInput || !hiddenInput || !resultsBox) return;

    let debounceTimer = null;
    let requestCounter = 0;

    const selectProduct = (row) => {
      hiddenInput.value = row.id || "";
      searchInput.value = row.label || "";
      resultsBox.innerHTML = "";
      resultsBox.classList.add("d-none");
    };

    const fetchResults = async () => {
      const query = searchInput.value.trim();

      hiddenInput.value = "";

      if (query.length < 2) {
        resultsBox.innerHTML = "";
        resultsBox.classList.add("d-none");
        return;
      }

      const currentRequest = ++requestCounter;
      const res = await fetch(`${config.lookupEndpoint}?q=${encodeURIComponent(query)}`, {
        headers: { Accept: "application/json" }
      });

      const json = await res.json();

      if (currentRequest !== requestCounter) {
        return;
      }

      if (!res.ok || !json.success) {
        resultsBox.innerHTML = '<div class="list-group-item text-danger">Gagal memuat product.</div>';
        resultsBox.classList.remove("d-none");
        return;
      }

      renderResults(resultsBox, json.data?.rows || [], selectProduct);
    };

    searchInput.addEventListener("input", () => {
      clearTimeout(debounceTimer);
      debounceTimer = setTimeout(fetchResults, 250);
    });

    searchInput.addEventListener("focus", () => {
      if (searchInput.value.trim().length >= 2) {
        fetchResults();
      }
    });
  };

  const initLineItem = (item) => {
    initProductLookup(item);
    initQtyInput(item);
    initMoneyInput(item);
  };

  addButton.addEventListener("click", () => {
    const html = template.innerHTML.replaceAll("__INDEX__", String(nextIndex));
    container.insertAdjacentHTML("beforeend", html);
    nextIndex += 1;

    const items = container.querySelectorAll("[data-line-item]");
    const lastItem = items[items.length - 1];
    if (lastItem) {
      initLineItem(lastItem);
    }

    updateRemoveButtons();
  });

  container.addEventListener("click", (event) => {
    const button = event.target.closest("[data-remove-line]");
    if (!button) return;

    const item = button.closest("[data-line-item]");
    if (!item) return;

    const items = container.querySelectorAll("[data-line-item]");
    if (items.length <= 1) return;

    item.remove();
    updateRemoveButtons();
  });

  document.addEventListener("click", (event) => {
    if (!event.target.closest("[data-line-item]")) {
      closeAllResults();
    }
  });

  autoReceiveInputs.forEach((input) => {
    input.addEventListener("change", updateTanggalTerimaState);
  });

  container.querySelectorAll("[data-line-item]").forEach(initLineItem);
  updateRemoveButtons();
  updateTanggalTerimaState();
})();
