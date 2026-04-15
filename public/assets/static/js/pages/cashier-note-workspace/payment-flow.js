(() => {
  const NS = (window.CashierNoteWorkspace = window.CashierNoteWorkspace || {});
  const digits = (value) =>
    Number.parseInt(String(value || "").replace(/\D+/g, "") || "0", 10);
  const format = (value) => Number(value || 0).toLocaleString("id-ID");
  const byId = (id) => document.getElementById(id);

  const setText = (id, value) => {
    const el = byId(id);
    if (el) el.textContent = format(value);
  };

  const toggle = (id, show) => {
    const el = byId(id);
    if (el) el.classList.toggle("d-none", !show);
  };

  const toggleFlex = (id, show) => {
    const el = byId(id);
    if (!el) return;
    el.classList.toggle("d-none", !show);
    el.classList.toggle("d-flex", show);
  };

  const focusElement = (element, select = true) => {
    if (typeof NS.focusElement === "function") {
      NS.focusElement(element, select);
      return;
    }

    if (!(element instanceof HTMLElement)) return;

    window.requestAnimationFrame(() => {
      element.focus();

      if (select && typeof element.select === "function") {
        element.select();
      }
    });
  };

  const escapeHtml = (value) =>
    String(value || "")
      .replaceAll("&", "&amp;")
      .replaceAll("<", "&lt;")
      .replaceAll(">", "&gt;")
      .replaceAll('"', "&quot;")
      .replaceAll("'", "&#039;");

  NS.paymentState = NS.paymentState || { mode: "", cashStep: false };

  const updateHidden = (id, value) => {
    const el = byId(id);
    if (el) el.value = String(value ?? "");
  };

  const hiddenValue = (id) => byId(id)?.value || "";
  const partialAmountInput = () => byId("inline_payment_amount_paid_display");
  const receivedAmountInput = () => byId("inline_payment_amount_received_display");
  const modalEl = () => byId("workspace-payment-modal");
  const formEl = () => byId("cashier-note-workspace-form");
  const choiceButtons = () =>
    Array.from(document.querySelectorAll("[data-payment-choice]"));

  const hasBootstrapModal = () =>
    typeof bootstrap !== "undefined" && !!bootstrap.Modal;

  const visibleModalCount = () => document.querySelectorAll(".modal.show").length;

  const cleanupResidualModalArtifacts = () => {
    if (visibleModalCount() > 0) {
      return;
    }

    document.querySelectorAll(".modal-backdrop").forEach((el) => el.remove());
    document.body.classList.remove("modal-open");
    document.body.style.removeProperty("overflow");
    document.body.style.removeProperty("padding-right");
  };

  const deferCleanupResidualModalArtifacts = () => {
    window.setTimeout(cleanupResidualModalArtifacts, 0);
  };

  const getPaymentModalInstance = () => {
    const el = modalEl();
    if (!el || !hasBootstrapModal()) return null;

    return bootstrap.Modal.getOrCreateInstance(el);
  };

  const currentRows = () =>
    typeof NS.currentRows === "function" ? NS.currentRows() : [];

  const grandTotal = () =>
    currentRows().reduce((sum, item) => sum + Number(item?.total || 0), 0);

  const partialAmount = (total) => {
    const inputValue = digits(
      partialAmountInput()?.value || hiddenValue("inline_payment_amount_paid_rupiah")
    );

    return Math.min(inputValue, total);
  };

  const payableAmount = (total) => {
    if (NS.paymentState.mode === "full") {
      return total;
    }

    if (NS.paymentState.mode === "partial") {
      return partialAmount(total);
    }

    return 0;
  };

  const lineSummaryLabel = (item) => {
    const row = item?.row;
    if (!(row instanceof HTMLElement)) {
      return item?.title || "Rincian";
    }

    const type = row.dataset.itemType || "";
    const serviceName =
      row.querySelector('input[name$="[service][name]"]')?.value?.trim() || "";
    const productName =
      row.querySelector("[data-product-search]")?.value?.trim() || "";
    const externalLabel =
      row
        .querySelector('input[name$="[external_purchase_lines][0][label]"]')
        ?.value?.trim() || "";
    const description =
      row.querySelector('textarea[name$="[description]"]')?.value?.trim() || "";

    if (type === "product") {
      return productName || description || item.title || "Produk";
    }

    if (type === "service_store_stock") {
      return [serviceName, productName].filter(Boolean).join(" + ") || item.title || "Rincian";
    }

    if (type === "service_external") {
      return [serviceName, externalLabel].filter(Boolean).join(" + ") || item.title || "Rincian";
    }

    return serviceName || description || item.title || "Servis";
  };

  const renderLineSummary = () => {
    const summaryRoot = byId("workspace-payment-line-summary");
    if (!summaryRoot) return;

    const rows = currentRows();

    if (!rows.length) {
      summaryRoot.innerHTML =
        '<div class="p-3 text-muted small">Belum ada rincian nota.</div>';
      return;
    }

    summaryRoot.innerHTML = rows
      .map(
        (item) => `
          <div class="p-3 border-bottom">
              <div class="d-flex justify-content-between align-items-start gap-3">
                  <div class="pe-2">
                      <div class="fw-semibold">${escapeHtml(lineSummaryLabel(item))}</div>
                      <div class="small text-muted">${escapeHtml(item.title || "Rincian")}</div>
                  </div>
                  <strong>${format(item.total)}</strong>
              </div>
          </div>
        `
      )
      .join("");
  };

  const syncChoiceButtons = () => {
    choiceButtons().forEach((button) => {
      const active = button.dataset.paymentChoice === NS.paymentState.mode;

      button.classList.toggle("btn-primary", active);
      button.classList.toggle("text-white", active);
      button.classList.toggle("btn-light", !active);
      button.classList.toggle("text-dark", !active);
      button.classList.toggle("shadow-sm", active);
    });
  };

  const clearReceivedAmount = () => {
    updateHidden("inline_payment_amount_received_rupiah", "");
    const input = receivedAmountInput();
    if (input) {
      input.value = "";
    }
  };

  const clearPaymentMethod = () => {
    updateHidden("inline_payment_method_hidden", "");
    clearReceivedAmount();
  };

  const syncPartialAmount = (total) => {
    const amount = partialAmount(total);
    updateHidden("inline_payment_amount_paid_rupiah", amount > 0 ? amount : "");

    const input = partialAmountInput();
    if (input && document.activeElement !== input) {
      input.value = amount > 0 ? format(amount) : "";
    }
  };

  const syncReceivedAmount = () => {
    const hidden = digits(hiddenValue("inline_payment_amount_received_rupiah"));
    const input = receivedAmountInput();

    if (input && document.activeElement !== input) {
      input.value = hidden > 0 ? format(hidden) : "";
    }
  };

  const applyMode = (mode) => {
    NS.paymentState.mode = mode;
    NS.paymentState.cashStep = false;

    if (mode === "skip") {
      updateHidden("inline_payment_decision_hidden", "skip");
      updateHidden("inline_payment_amount_paid_rupiah", "");
      clearPaymentMethod();
    }

    if (mode === "full") {
      updateHidden("inline_payment_decision_hidden", "pay_full");
      updateHidden("inline_payment_amount_paid_rupiah", "");
      clearPaymentMethod();
    }

    if (mode === "partial") {
      updateHidden("inline_payment_decision_hidden", "pay_partial");
      clearPaymentMethod();
    }

    NS.refreshPaymentUi();
  };

  const hydrateStateFromHidden = () => {
    const decision = hiddenValue("inline_payment_decision_hidden");
    const method = hiddenValue("inline_payment_method_hidden");

    NS.paymentState.mode =
      decision === "pay_partial"
        ? "partial"
        : decision === "pay_full"
          ? "full"
          : "";

    NS.paymentState.cashStep = method === "cash";
  };

  const focusByState = () => {
    if (NS.paymentState.cashStep) {
      focusElement(receivedAmountInput());
      return;
    }

    if (NS.paymentState.mode === "partial") {
      focusElement(partialAmountInput());
      return;
    }

    if (NS.paymentState.mode === "full") {
      focusElement(byId("workspace-payment-submit-transfer"), false);
      return;
    }

    if (NS.paymentState.mode === "skip") {
      focusElement(byId("workspace-payment-submit-skip"), false);
      return;
    }

    focusElement(byId("workspace-payment-choice-full"), false);
  };

  NS.refreshPaymentUi = (total = grandTotal()) => {
    const noteDate = byId("note_transaction_date")?.value || "";
    updateHidden("inline_payment_paid_at_hidden", noteDate);

    renderLineSummary();
    syncChoiceButtons();

    if (NS.paymentState.mode !== "partial") {
      updateHidden("inline_payment_amount_paid_rupiah", "");
      const input = partialAmountInput();
      if (input && document.activeElement !== input) {
        input.value = "";
      }
    } else {
      syncPartialAmount(total);
    }

    if (NS.paymentState.cashStep) {
      syncReceivedAmount();
    } else {
      clearReceivedAmount();
    }

    const payable = payableAmount(total);
    const received = digits(hiddenValue("inline_payment_amount_received_rupiah"));
    const badge = byId("workspace-payment-mode-badge");

    if (badge) {
      badge.textContent =
        NS.paymentState.mode === "full"
          ? "Bayar Penuh"
          : NS.paymentState.mode === "partial"
            ? "Bayar Sebagian"
            : NS.paymentState.mode === "skip"
              ? "Tanpa Pembayaran"
              : "Pilih Aksi";
    }

    setText("workspace-modal-total-text", total);
    setText("workspace-cash-payable-text", payable);
    setText("workspace-cash-received-text", received);
    setText("workspace-cash-change-text", Math.max(received - payable, 0));

    toggle("workspace-payment-panel-partial", NS.paymentState.mode === "partial");
    toggle("workspace-cash-shell-hint", !NS.paymentState.cashStep);
    toggle("workspace-payment-panel-cash", NS.paymentState.cashStep);

    toggleFlex("workspace-payment-footer-main", !NS.paymentState.cashStep);
    toggleFlex("workspace-payment-footer-cash", NS.paymentState.cashStep);

    const skipButton = byId("workspace-payment-submit-skip");
    const transferButton = byId("workspace-payment-submit-transfer");
    const cashButton = byId("workspace-payment-open-cash");
    const cashSubmitButton = byId("workspace-payment-submit-cash");

    const partialInvalid = NS.paymentState.mode === "partial" && payable <= 0;
    const baseInvalid = total <= 0;
    const transferInvalid =
      baseInvalid ||
      !["full", "partial"].includes(NS.paymentState.mode) ||
      partialInvalid;
    const skipInvalid = baseInvalid || NS.paymentState.mode !== "skip";
    const cashInvalid =
      baseInvalid ||
      !["full", "partial"].includes(NS.paymentState.mode) ||
      partialInvalid;
    const receivedInvalid =
      payable <= 0 || received < payable || hiddenValue("inline_payment_method_hidden") !== "cash";

    if (skipButton) {
      skipButton.classList.toggle("d-none", NS.paymentState.mode !== "skip");
      skipButton.disabled = skipInvalid;
    }

    if (transferButton) {
      transferButton.classList.toggle(
        "d-none",
        !["full", "partial"].includes(NS.paymentState.mode)
      );
      transferButton.disabled = transferInvalid;
    }

    if (cashButton) {
      cashButton.classList.toggle(
        "d-none",
        !["full", "partial"].includes(NS.paymentState.mode)
      );
      cashButton.disabled = cashInvalid;
    }

    if (cashSubmitButton) {
      cashSubmitButton.disabled = receivedInvalid;
    }
  };

  const showPaymentModal = () => {
    cleanupResidualModalArtifacts();

    const instance = getPaymentModalInstance();
    if (!instance) return;

    instance.show();
  };

  NS.handlePaymentModalShown = () => {
    NS.refreshPaymentUi();
    focusByState();
  };

  const bindPaymentModalLifecycle = () => {
    const el = modalEl();
    if (!el || el.dataset.paymentLifecycleBound === "1") return;

    el.dataset.paymentLifecycleBound = "1";

    el.addEventListener("shown.bs.modal", () => {
      NS.handlePaymentModalShown?.();
    });

    el.addEventListener("hidden.bs.modal", () => {
      deferCleanupResidualModalArtifacts();
    });
  };

  const reopenModalIfNeeded = () => {
    const decision = hiddenValue("inline_payment_decision_hidden");

    if (!["pay_full", "pay_partial"].includes(decision)) {
      return;
    }

    hydrateStateFromHidden();
    NS.refreshPaymentUi();
    showPaymentModal();
  };

  const openPaymentModal = () => {
    bindPaymentModalLifecycle();
    hydrateStateFromHidden();

    if (!["full", "partial"].includes(NS.paymentState.mode)) {
      NS.paymentState.mode = "";
      NS.paymentState.cashStep = false;
      clearPaymentMethod();
    }

    NS.refreshPaymentUi();
    showPaymentModal();
  };

  document.addEventListener("click", (event) => {
    if (event.target.closest("#workspace-open-payment-dialog")) {
      openPaymentModal();
      return;
    }

    const choiceButton = event.target.closest("[data-payment-choice]");
    if (choiceButton) {
      applyMode(choiceButton.dataset.paymentChoice || "");
      focusByState();
      return;
    }

    if (event.target.closest("#workspace-payment-submit-skip")) {
      applyMode("skip");
      return;
    }

    if (event.target.closest("#workspace-payment-submit-transfer")) {
      updateHidden("inline_payment_method_hidden", "transfer");
      return;
    }

    if (event.target.closest("#workspace-payment-open-cash")) {
      updateHidden("inline_payment_method_hidden", "cash");
      NS.paymentState.cashStep = true;
      NS.refreshPaymentUi();
      focusElement(receivedAmountInput());
      return;
    }

    if (event.target.closest("#workspace-payment-back-cash")) {
      clearPaymentMethod();
      NS.paymentState.cashStep = false;
      NS.refreshPaymentUi();
      focusElement(byId("workspace-payment-submit-transfer"), false);
      return;
    }

    if (event.target.closest("#workspace-payment-submit-cash")) {
      updateHidden("inline_payment_method_hidden", "cash");
    }
  });

  document.addEventListener("input", (event) => {
    if (event.target.id === "inline_payment_amount_paid_display") {
      updateHidden(
        "inline_payment_amount_paid_rupiah",
        digits(partialAmountInput()?.value || "")
      );
      NS.refreshPaymentUi();
      return;
    }

    if (event.target.id === "inline_payment_amount_received_display") {
      updateHidden(
        "inline_payment_amount_received_rupiah",
        digits(receivedAmountInput()?.value || "")
      );
      NS.refreshPaymentUi();
      return;
    }

    if (event.target.id === "note_transaction_date") {
      NS.refreshPaymentUi();
    }
  });

  document.addEventListener("keydown", (event) => {
    const choiceButton = event.target.closest("[data-payment-choice]");
    const buttons = choiceButtons();

    if (choiceButton && buttons.length) {
      const currentIndex = buttons.indexOf(choiceButton);

      if (event.key === "ArrowDown" || event.key === "ArrowRight") {
        event.preventDefault();
        const nextButton =
          buttons[Math.min(currentIndex + 1, buttons.length - 1)] || buttons[0];
        focusElement(nextButton, false);
        return;
      }

      if (event.key === "ArrowUp" || event.key === "ArrowLeft") {
        event.preventDefault();
        const nextButton =
          buttons[Math.max(currentIndex - 1, 0)] || buttons[0];
        focusElement(nextButton, false);
        return;
      }
    }

    if (event.target.id === "inline_payment_amount_paid_display" && event.key === "Enter") {
      if (event.ctrlKey || event.altKey || event.metaKey) {
        return;
      }

      event.preventDefault();
      NS.refreshPaymentUi();

      const total = grandTotal();
      if (payableAmount(total) <= 0) {
        return;
      }

      focusElement(byId("workspace-payment-submit-transfer"), false);
      return;
    }

    if (event.target.id === "inline_payment_amount_received_display" && event.key === "Enter") {
      if (event.ctrlKey || event.altKey || event.metaKey) {
        return;
      }

      const total = grandTotal();
      const payable = payableAmount(total);
      const received = digits(receivedAmountInput()?.value || "");

      if (payable <= 0 || received < payable) {
        event.preventDefault();
        NS.refreshPaymentUi();
        return;
      }

      event.preventDefault();
      updateHidden("inline_payment_method_hidden", "cash");
      updateHidden("inline_payment_amount_received_rupiah", received);
      formEl()?.requestSubmit();
    }
  });

  bindPaymentModalLifecycle();
  hydrateStateFromHidden();
  NS.refreshPaymentUi();

  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", reopenModalIfNeeded, { once: true });
  } else {
    reopenModalIfNeeded();
  }
})();
