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

  const hasServerOldState = (config) => {
    const items = Array.isArray(config.oldItems) ? config.oldItems : [];
    const payment = typeof config.oldInlinePayment === "object" && config.oldInlinePayment !== null
      ? config.oldInlinePayment
      : {};

    return items.length > 0 || (payment.decision && payment.decision !== "skip");
  };

  const draftKey = (config) => {
    const mode = String(config.workspaceMode || "create");
    const noteId = String(config.noteId || window.location.pathname);
    return `cashier-note-workspace-draft:${mode}:${noteId}`;
  };

  const readDraft = (key) => {
    try {
      const raw = window.localStorage.getItem(key);
      if (!raw) return null;

      const parsed = JSON.parse(raw);
      return typeof parsed === "object" && parsed !== null ? parsed : null;
    } catch (_error) {
      return null;
    }
  };

  const writeConfig = (config) => {
    configEl.textContent = JSON.stringify(config);
    NS.config = config;
  };

  const maybeRestoreDraftToConfig = () => {
    const config = parseConfig();
    const key = draftKey(config);
    const draft = readDraft(key);

    if (!draft || hasServerOldState(config)) {
      writeConfig(config);
      return;
    }

    const shouldRestore = window.confirm("Ditemukan draft workspace terakhir. Pulihkan draft ini?");
    if (!shouldRestore) {
      writeConfig(config);
      return;
    }

    const merged = {
      ...config,
      oldNote: draft.note || config.oldNote || {},
      oldItems: Array.isArray(draft.items) ? draft.items : (config.oldItems || []),
      oldInlinePayment: draft.inline_payment || config.oldInlinePayment || {},
      draftMeta: {
        restored_at: new Date().toISOString(),
        saved_at: draft.saved_at || null,
      },
    };

    writeConfig(merged);
  };

  const numberText = (value) => String(value ?? "").replace(/\D+/g, "");
  const valueOf = (selector, root = document) => root.querySelector(selector)?.value || "";

  const normalizeItem = (row) => {
    const itemType = row.dataset.itemType || "service";
    const base = {
      description: valueOf('textarea[name$="[description]"]', row),
      pay_now: valueOf('[data-pay-now]', row) || "0",
    };

    if (itemType === "product") {
      return {
        ...base,
        entry_mode: "product",
        part_source: "store_stock",
        selected_label: valueOf('[data-product-search]', row),
        product_lines: [
          {
            product_id: valueOf('[data-product-id]', row),
            qty: numberText(valueOf('input[name$="[product_lines][0][qty]"]', row)) || "1",
            unit_price_rupiah: numberText(valueOf('input[name$="[product_lines][0][unit_price_rupiah]"]', row)),
          },
        ],
      };
    }

    if (itemType === "service_store_stock") {
      return {
        ...base,
        entry_mode: "service",
        part_source: "store_stock",
        service: {
          name: valueOf('input[name$="[service][name]"]', row),
          notes: valueOf('textarea[name$="[service][notes]"]', row),
          price_rupiah: numberText(valueOf('input[name$="[service][price_rupiah]"]', row)),
        },
        selected_label: valueOf('[data-product-search]', row),
        product_lines: [
          {
            product_id: valueOf('[data-product-id]', row),
            qty: numberText(valueOf('input[name$="[product_lines][0][qty]"]', row)) || "1",
            unit_price_rupiah: numberText(valueOf('input[name$="[product_lines][0][unit_price_rupiah]"]', row)),
          },
        ],
      };
    }

    if (itemType === "service_external") {
      return {
        ...base,
        entry_mode: "service",
        part_source: "external_purchase",
        service: {
          name: valueOf('input[name$="[service][name]"]', row),
          notes: valueOf('textarea[name$="[service][notes]"]', row),
          price_rupiah: numberText(valueOf('input[name$="[service][price_rupiah]"]', row)),
        },
        external_purchase_lines: [
          {
            label: valueOf('input[name$="[external_purchase_lines][0][label]"]', row),
            qty: numberText(valueOf('input[name$="[external_purchase_lines][0][qty]"]', row)) || "1",
            unit_cost_rupiah: numberText(valueOf('input[name$="[external_purchase_lines][0][unit_cost_rupiah]"]', row)),
          },
        ],
      };
    }

    return {
      ...base,
      entry_mode: "service",
      part_source: "none",
      service: {
        name: valueOf('input[name$="[service][name]"]', row),
        notes: valueOf('textarea[name$="[service][notes]"]', row),
        price_rupiah: numberText(valueOf('input[name$="[service][price_rupiah]"]', row)),
      },
    };
  };

  const serializeDraft = () => {
    const config = parseConfig();

    return {
      workspace_mode: config.workspaceMode || "create",
      note_id: config.noteId || null,
      note: {
        customer_name: valueOf("#note_customer_name"),
        customer_phone: valueOf("#note_customer_phone"),
        transaction_date: valueOf("#note_transaction_date"),
      },
      items: Array.from(document.querySelectorAll("[data-line-item]")).map((row) => normalizeItem(row)),
      inline_payment: {
        decision: valueOf("#inline_payment_decision_hidden") || "skip",
        payment_method: valueOf("#inline_payment_method_hidden"),
        paid_at: valueOf("#inline_payment_paid_at_hidden"),
        amount_paid_rupiah: numberText(valueOf("#inline_payment_amount_paid_rupiah")),
        amount_received_rupiah: numberText(valueOf("#inline_payment_amount_received_rupiah")),
      },
      saved_at: new Date().toISOString(),
    };
  };

  const saveDraft = () => {
    const config = parseConfig();
    const key = draftKey(config);

    try {
      window.localStorage.setItem(key, JSON.stringify(serializeDraft()));
    } catch (_error) {
      // noop
    }
  };

  const initAutosave = () => {
    const form = document.getElementById("cashier-note-workspace-form");
    if (!form) return;

    let timer = null;
    const queueSave = () => {
      window.clearTimeout(timer);
      timer = window.setTimeout(saveDraft, 300);
    };

    form.addEventListener("input", queueSave);
    form.addEventListener("change", queueSave);

    document.addEventListener("click", (event) => {
      if (
        event.target.closest("[data-add-item-type]")
        || event.target.closest("[data-remove-line]")
        || event.target.closest("[data-open-payment]")
        || event.target.closest("#workspace-payment-open-cash")
        || event.target.closest("#workspace-payment-back-cash")
      ) {
        window.setTimeout(queueSave, 0);
      }
    });

    window.addEventListener("beforeunload", saveDraft);
    window.setTimeout(saveDraft, 0);
  };

  maybeRestoreDraftToConfig();

  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", initAutosave);
  } else {
    initAutosave();
  }
})();
