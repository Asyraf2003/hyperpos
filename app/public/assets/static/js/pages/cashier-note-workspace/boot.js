(() => {
  const NS = (window.CashierNoteWorkspace = window.CashierNoteWorkspace || {});
  const configEl = document.getElementById("cashier-note-workspace-config");
  if (!configEl) return;

  const parseConfig = () => {
    try {
      return JSON.parse(configEl.textContent || "{}");
    } catch (_error) {
      return {};
    }
  };

  const start = async () => {
    if (NS.workspaceConfigReady instanceof Promise) {
      await NS.workspaceConfigReady;
    }

    NS.config = parseConfig();

    const addButton = document.getElementById("workspace-add-button");
    const addMenu = document.getElementById("workspace-item-type-menu");
    const form = document.getElementById("cashier-note-workspace-form");
    const customerName = document.getElementById("note_customer_name");
    const customerPhone = document.getElementById("note_customer_phone");
    const transactionDate = document.getElementById("note_transaction_date");
    const paymentModal = document.getElementById("workspace-payment-modal");

    let activeMenuIndex = 0;
    let menuReturnFocus = addButton;

    const menuButtons = () =>
      Array.from(addMenu?.querySelectorAll("[data-add-item-type]") || []);

    const syncMenuButtonState = () => {
      menuButtons().forEach((button, index) => {
        const active = index === activeMenuIndex;
        button.classList.toggle("btn-primary", active);
        button.classList.toggle("text-white", active);
        button.classList.toggle("btn-light", !active);
        button.classList.toggle("text-dark", !active);
        button.classList.toggle("shadow-sm", active);
      });
    };

    const focusMenuButton = (index) => {
      const buttons = menuButtons();
      if (!buttons.length) return;

      const nextIndex = Math.max(0, Math.min(index, buttons.length - 1));
      activeMenuIndex = nextIndex;
      syncMenuButtonState();
      NS.focusElement?.(buttons[nextIndex], false);
    };

    const closeItemTypeMenu = (restoreFocus = false) => {
      if (!addMenu) return;

      addMenu.classList.add("d-none");
      activeMenuIndex = 0;
      syncMenuButtonState();

      if (restoreFocus && menuReturnFocus instanceof HTMLElement) {
        NS.focusElement?.(menuReturnFocus, false);
      }
    };

    const openItemTypeMenu = (focusFirstButton = false, returnFocus = addButton) => {
      if (!addMenu) return;

      menuReturnFocus = returnFocus instanceof HTMLElement ? returnFocus : addButton;
      addMenu.classList.remove("d-none");
      activeMenuIndex = 0;
      syncMenuButtonState();

      if (focusFirstButton) {
        focusMenuButton(0);
      }
    };

    const hydrateNoteFields = () => {
      const note =
        typeof NS.config.oldNote === "object" && NS.config.oldNote !== null
          ? NS.config.oldNote
          : {};

      if (customerName && typeof note.customer_name === "string") {
        customerName.value = note.customer_name;
      }

      if (customerPhone && typeof note.customer_phone === "string") {
        customerPhone.value = note.customer_phone;
      }

      if (
        transactionDate &&
        typeof note.transaction_date === "string" &&
        note.transaction_date !== ""
      ) {
        transactionDate.value = note.transaction_date;
      }

      if (customerName && !customerName.value.trim()) {
        customerName.value = NS.config.defaultCustomerName || "Pelanggan no 1";
      }
    };

    const hydratePaymentFields = () => {
      const payment =
        typeof NS.config.oldInlinePayment === "object" &&
        NS.config.oldInlinePayment !== null
          ? NS.config.oldInlinePayment
          : {};

      const setValue = (id, value) => {
        const el = document.getElementById(id);
        if (!el || value === undefined || value === null) return;
        el.value = String(value);
      };

      setValue("inline_payment_decision_hidden", payment.decision || "skip");
      setValue("inline_payment_method_hidden", payment.payment_method || "");
      setValue(
        "inline_payment_paid_at_hidden",
        payment.paid_at || transactionDate?.value || ""
      );
      setValue(
        "inline_payment_amount_paid_rupiah",
        payment.amount_paid_rupiah || ""
      );
      setValue(
        "inline_payment_amount_received_rupiah",
        payment.amount_received_rupiah || ""
      );
    };

    const autofocusHeader = () => {
      if ((NS.config.workspaceMode || "create") !== "create") return;
      if (!customerName) return;
      if (document.querySelector(".modal.show")) return;

      NS.focusElement?.(customerName);
    };

    addButton?.addEventListener("click", () => {
      const isHidden = addMenu?.classList.contains("d-none");
      if (isHidden) {
        openItemTypeMenu(false, addButton);
        return;
      }

      closeItemTypeMenu(false);
    });

    addButton?.addEventListener("keydown", (event) => {
      if (event.key === "Enter" || event.key === " " || event.key === "ArrowDown") {
        event.preventDefault();
        openItemTypeMenu(true, addButton);
      }

      if (event.key === "Escape") {
        event.preventDefault();
        closeItemTypeMenu(false);
      }
    });

    addMenu?.addEventListener("keydown", (event) => {
      const buttons = menuButtons();
      if (!buttons.length) return;

      if (event.key === "ArrowDown") {
        event.preventDefault();
        focusMenuButton(activeMenuIndex + 1);
        return;
      }

      if (event.key === "ArrowUp") {
        event.preventDefault();
        focusMenuButton(activeMenuIndex - 1);
        return;
      }

      if (event.key === "Escape") {
        event.preventDefault();
        closeItemTypeMenu(true);
        return;
      }

      if (event.key !== "Enter" && event.key !== " ") {
        return;
      }

      event.preventDefault();

      const currentButton =
        event.target.closest("[data-add-item-type]") || buttons[activeMenuIndex];

      if (currentButton instanceof HTMLElement) {
        currentButton.click();
      }
    });

    customerName?.addEventListener("keydown", (event) => {
      if (event.key !== "Enter" || event.ctrlKey || event.altKey || event.metaKey) {
        return;
      }

      event.preventDefault();

      if (event.shiftKey) {
        return;
      }

      NS.focusElement?.(customerPhone);
    });

    customerPhone?.addEventListener("keydown", (event) => {
      if (event.key !== "Enter" || event.ctrlKey || event.altKey || event.metaKey) {
        return;
      }

      event.preventDefault();

      if (event.shiftKey) {
        NS.focusElement?.(customerName);
        return;
      }

      openItemTypeMenu(true, customerPhone);
    });

    document.addEventListener("click", (event) => {
      if (addMenu && addButton) {
        const clickedInsideMenu = addMenu.contains(event.target);
        const clickedAddButton =
          event.target === addButton || event.target.closest("#workspace-add-button");

        if (!clickedInsideMenu && !clickedAddButton) {
          closeItemTypeMenu(false);
        }
      }

      const removeButton = event.target.closest("[data-remove-line]");
      if (removeButton) {
        NS.removeRow?.(removeButton.closest("[data-line-item]"));
      }

      const typeButton = event.target.closest("[data-add-item-type]");
      if (typeButton) {
        closeItemTypeMenu(false);
        NS.addRow?.(typeButton.dataset.addItemType || "service");
      }
    });

    form?.addEventListener("input", (event) => {
      if (
        event.target.closest("[data-line-item]") ||
        event.target.id === "note_transaction_date"
      ) {
        NS.updateSummary?.();
      }
    });

    form?.addEventListener("change", (event) => {
      if (
        event.target.closest("[data-line-item]") ||
        event.target.id === "note_transaction_date"
      ) {
        NS.updateSummary?.();
      }
    });

    hydrateNoteFields();
    hydratePaymentFields();

    (NS.config.oldItems || []).forEach((item) => NS.addRow?.(NS.detectType(item), item));

    window.AdminMoneyInput?.bindBySelector?.(document);

    if (paymentModal) {
      paymentModal.addEventListener("shown.bs.modal", () => {
        window.AdminMoneyInput?.bindBySelector?.(paymentModal);
        NS.updateSummary?.();
        NS.handlePaymentModalShown?.();
      });
    }

    NS.updateSummary?.();
    autofocusHeader();
  };

  void start();
})();
