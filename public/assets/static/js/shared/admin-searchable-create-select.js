(() => {
  const normalize = (value) => String(value || "")
    .toLowerCase()
    .normalize("NFD")
    .replace(/[\u0300-\u036f]/g, "")
    .trim();

  const bind = (root) => {
    const select = root.querySelector("[data-searchable-create-select-native]");
    const ui = root.querySelector("[data-searchable-create-select-ui]");
    const search = root.querySelector("[data-searchable-create-select-search]");
    const results = root.querySelector("[data-searchable-create-select-results]");
    const empty = root.querySelector("[data-searchable-create-select-empty]");

    if (!select || !ui || !search || !results || !empty) return;
    if (root.dataset.searchableCreateSelectReady === "1") return;

    root.dataset.searchableCreateSelectReady = "1";

    const options = Array.from(select.options)
      .filter((option) => option.value !== "")
      .map((option) => ({
        value: option.value,
        label: option.textContent.trim(),
      }));

    const selected = options.find((option) => option.value === select.value);
    if (selected) search.value = selected.label;

    select.required = false;
    select.tabIndex = -1;
    select.classList.add("d-none");
    ui.hidden = false;

    const hideResults = () => {
      results.classList.add("d-none");
    };

    const showResults = () => {
      results.classList.remove("d-none");
    };

    const choose = (option) => {
      select.value = option.value;
      search.value = option.label;
      select.dispatchEvent(new Event("change", { bubbles: true }));
      empty.classList.add("d-none");
      hideResults();
    };

    const render = () => {
      const query = normalize(search.value);
      const filtered = options
        .filter((option) => normalize(option.label).includes(query))
        .slice(0, 50);

      results.innerHTML = "";

      if (query !== "" && filtered.length === 0) {
        hideResults();
        empty.classList.remove("d-none");
        return;
      }

      empty.classList.add("d-none");

      if (query === "" && document.activeElement !== search) {
        hideResults();
        return;
      }

      filtered.forEach((option) => {
        const button = document.createElement("button");
        button.type = "button";
        button.className = "list-group-item list-group-item-action";
        button.textContent = option.label;

        if (option.value === select.value) {
          button.classList.add("active");
        }

        button.addEventListener("click", () => choose(option));
        results.appendChild(button);
      });

      if (filtered.length > 0) {
        showResults();
      } else {
        hideResults();
      }
    };

    search.addEventListener("input", () => {
      if (search.value.trim() === "") {
        select.value = "";
        select.dispatchEvent(new Event("change", { bubbles: true }));
      }

      render();
    });

    search.addEventListener("focus", render);

    search.addEventListener("keydown", (event) => {
      if (event.key === "Escape") {
        hideResults();
      }
    });

    select.addEventListener("change", () => {
      const current = options.find((option) => option.value === select.value);
      search.value = current ? current.label : "";
    });

    document.addEventListener("click", (event) => {
      if (!root.contains(event.target)) {
        hideResults();
      }
    });
  };

  const bindBySelector = (context = document) => {
    context
      .querySelectorAll("[data-searchable-create-select]")
      .forEach(bind);
  };

  document.addEventListener("DOMContentLoaded", () => bindBySelector(document));

  window.AdminSearchableCreateSelect = { bindBySelector };
})();
