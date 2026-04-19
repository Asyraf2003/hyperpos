(() => {
  const config = window.expenseCreateConfig;
  if (!config || !Array.isArray(config.categoryOptions)) return;

  const options = config.categoryOptions.map((item) => ({
    id: String(item.id ?? "").trim(),
    label: String(item.label ?? "").trim(),
  })).filter((item) => item.id !== "" && item.label !== "");

  const minChars = 2;
  const searchWrap = document.getElementById("expense-category-search-wrap");
  const searchInput = document.getElementById("expense-category-search-input");
  const searchResults = document.getElementById("expense-category-search-results");
  const helper = document.getElementById("expense-category-search-helper");
  const selectWrap = document.getElementById("expense-category-select-wrap");
  const select = document.getElementById("category_id");

  if (!searchWrap || !searchInput || !searchResults || !helper || !selectWrap || !select) {
    return;
  }

  const optionById = new Map(options.map((item) => [item.id, item]));

  const closeResults = () => {
    searchResults.innerHTML = "";
    searchResults.classList.add("d-none");
  };

  const setHelper = (message, danger = false) => {
    helper.textContent = message;
    helper.classList.toggle("text-danger", danger);
    helper.classList.toggle("text-muted", !danger);
  };

  const applySelection = (id) => {
    const selected = optionById.get(String(id));

    select.value = selected ? selected.id : "";
    searchInput.value = selected ? selected.label : "";

    if (selected) {
      setHelper(`Kategori dipilih: ${selected.label}`);
    } else {
      setHelper("Ketik minimal 2 karakter untuk cari kategori.");
    }

    closeResults();
  };

  const renderResults = (query) => {
    const normalized = query.trim().toLowerCase();

    if (normalized.length < minChars) {
      select.value = "";
      setHelper("Ketik minimal 2 karakter untuk cari kategori.");
      closeResults();
      return;
    }

    const matches = options
      .filter((item) => item.label.toLowerCase().includes(normalized))
      .slice(0, 8);

    if (matches.length === 0) {
      select.value = "";
      setHelper("Kategori tidak ditemukan.", true);
      closeResults();
      return;
    }

    setHelper("Pilih kategori dari daftar hasil.");
    searchResults.innerHTML = matches.map((item) => `
      <button
        type="button"
        class="list-group-item list-group-item-action"
        data-category-id="${item.id}"
      >
        ${item.label}
      </button>
    `).join("");
    searchResults.classList.remove("d-none");
  };

  searchWrap.classList.remove("d-none");
  selectWrap.classList.add("d-none");

  if (select.value) {
    applySelection(select.value);
  } else {
    setHelper("Ketik minimal 2 karakter untuk cari kategori.");
  }

  searchInput.addEventListener("input", () => {
    renderResults(searchInput.value);
  });

  searchResults.addEventListener("click", (event) => {
    const button = event.target.closest("[data-category-id]");
    if (!button) return;

    applySelection(button.getAttribute("data-category-id"));
  });

  document.addEventListener("click", (event) => {
    const target = event.target;

    if (
      target instanceof Node &&
      !searchWrap.contains(target) &&
      !searchResults.contains(target)
    ) {
      closeResults();
    }
  });
})();
