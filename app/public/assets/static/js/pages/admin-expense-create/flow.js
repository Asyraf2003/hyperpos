(() => {
  const focusField = (field) => {
    if (!field) return;

    window.requestAnimationFrame(() => {
      field.focus();

      if (typeof field.select === "function" && field.tagName !== "SELECT" && field.type !== "date") {
        field.select();
      }
    });
  };

  const bindEnter = (field, next) => {
    if (!field || field.dataset.enterBound === "1") {
      return;
    }

    field.dataset.enterBound = "1";

    field.addEventListener("keydown", (event) => {
      if (event.key !== "Enter") return;
      if (event.altKey || event.ctrlKey || event.metaKey || event.shiftKey) return;

      event.preventDefault();
      next();
    });
  };

  const init = (ctx) => {
    const { elements } = ctx;

    const form = elements.form;
    const dateInput = elements.expenseDate;
    const amountDisplay = elements.amountDisplay;
    const paymentMethod = elements.paymentMethod;
    const description = elements.description;

    if (!form || !dateInput || !amountDisplay || !paymentMethod || !description) {
      return;
    }

    const dateFocusTarget = () => dateInput._flatpickr?.altInput || dateInput;

    const bindDateNavigation = () => {
      bindEnter(dateFocusTarget(), () => focusField(amountDisplay));
    };

    bindDateNavigation();
    window.setTimeout(bindDateNavigation, 200);

    bindEnter(amountDisplay, () => focusField(paymentMethod));
    bindEnter(paymentMethod, () => focusField(description));

    bindEnter(description, () => {
      if (typeof form.requestSubmit === "function") {
        form.requestSubmit();
        return;
      }

      form.submit();
    });

    document.addEventListener("expense-category:selected", () => {
      focusField(dateFocusTarget());
    });

    if (ctx.api?.hasSelectedCategory?.()) {
      focusField(dateFocusTarget());
      return;
    }

    ctx.api?.focusCategorySearch?.();
  };

  window.AdminExpenseCreateFlow = { init };
})();
