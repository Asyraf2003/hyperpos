(() => {
  const NS = (window.CashierNoteWorkspace = window.CashierNoteWorkspace || {});
  const configEl = document.getElementById("cashier-note-workspace-config");
  if (!configEl) return;

  NS.config = JSON.parse(configEl.textContent || "{}");
  const addButton = document.getElementById("workspace-add-button");
  const addMenu = document.getElementById("workspace-item-type-menu");
  const form = document.getElementById("cashier-note-workspace-form");
  const customerName = document.getElementById("note_customer_name");
  const paymentModal = document.getElementById("workspace-payment-modal");

  if (customerName && !customerName.value.trim()) {
    customerName.value = NS.config.defaultCustomerName || "Pelanggan no 1";
  }

  addButton?.addEventListener("click", () => addMenu.classList.toggle("d-none"));

  document.addEventListener("click", (event) => {
    if (!addMenu.contains(event.target) && event.target !== addButton) {
      addMenu.classList.add("d-none");
    }

    const removeButton = event.target.closest("[data-remove-line]");
    if (removeButton) {
      NS.removeRow(removeButton.closest("[data-line-item]"));
    }

    const typeButton = event.target.closest("[data-add-item-type]");
    if (typeButton) {
      addMenu.classList.add("d-none");
      NS.addRow(typeButton.dataset.addItemType || "service");
    }
  });

  form?.addEventListener("input", (event) => {
    if (event.target.closest("[data-line-item]") || event.target.id === "note_transaction_date") {
      NS.updateSummary();
    }
  });

  form?.addEventListener("change", (event) => {
    if (event.target.closest("[data-line-item]") || event.target.id === "note_transaction_date") {
      NS.updateSummary();
    }
  });

  (NS.config.oldItems || []).forEach((item) => NS.addRow(NS.detectType(item), item));

  window.AdminMoneyInput?.bindBySelector?.(document);

  if (paymentModal) {
    paymentModal.addEventListener("shown.bs.modal", () => {
      window.AdminMoneyInput?.bindBySelector?.(paymentModal);
      NS.updateSummary?.();
    });
  }

  NS.updateSummary();
})();
