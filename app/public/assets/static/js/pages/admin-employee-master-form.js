(() => {
  const form = document.querySelector("[data-employee-master-form='1']");
  if (!form) return;

  const ids = [
    "employee_name",
    "phone",
    "salary_basis_type",
    "default_salary_amount_display",
    "started_at",
    "ended_at",
    "change_reason",
  ];

  const fields = ids
    .map((id) => form.querySelector(`#${id}`))
    .filter((field) => field instanceof HTMLElement);

  if (!fields.length) return;

  const isTextarea = (field) => field.tagName === "TEXTAREA";

  const focusField = (field) => {
    if (!field) return;

    window.requestAnimationFrame(() => {
      field.focus();

      if (typeof field.showPicker === "function" && field.type === "date") {
        try {
          field.showPicker();
        } catch (_) {
        }
      }

      if (!isTextarea(field) && typeof field.select === "function" && field.tagName !== "SELECT") {
        field.select();
      }
    });
  };

  const focusFirstField = () => {
    const first = fields[0];
    if (!first) return;
    focusField(first);
  };

  const moveToNextField = (currentField) => {
    const index = fields.indexOf(currentField);
    if (index === -1) return;

    if (index === fields.length - 1) {
      if (typeof form.requestSubmit === "function") {
        form.requestSubmit();
        return;
      }

      form.submit();
      return;
    }

    focusField(fields[index + 1]);
  };

  fields.forEach((field) => {
    field.addEventListener("keydown", (event) => {
      if (event.key !== "Enter") return;
      if (event.altKey || event.metaKey) return;

      if (isTextarea(field)) {
        if (event.ctrlKey) {
          event.preventDefault();

          if (typeof form.requestSubmit === "function") {
            form.requestSubmit();
            return;
          }

          form.submit();
        }

        return;
      }

      if (event.shiftKey || event.ctrlKey) return;

      event.preventDefault();
      moveToNextField(field);
    });
  });

  focusFirstField();
})();
