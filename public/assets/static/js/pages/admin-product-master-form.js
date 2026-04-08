(() => {
  const form = document.querySelector("[data-product-master-form='1']");
  if (!form) return;

  const ids = [
    "kode_barang",
    "nama_barang",
    "merek",
    "ukuran",
    "harga_jual_display",
  ];

  const fields = ids
    .map((id) => form.querySelector(`#${id}`))
    .filter((field) => field instanceof HTMLElement);

  if (!fields.length) return;

  const focusFirstField = () => {
    const first = fields[0];
    if (!first) return;

    window.requestAnimationFrame(() => {
      first.focus();

      if (typeof first.select === "function") {
        first.select();
      }
    });
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

    const next = fields[index + 1];
    next.focus();

    if (typeof next.select === "function") {
      next.select();
    }
  };

  fields.forEach((field) => {
    field.addEventListener("keydown", (event) => {
      if (event.key !== "Enter") return;
      if (event.shiftKey || event.ctrlKey || event.altKey || event.metaKey) return;

      event.preventDefault();
      moveToNextField(field);
    });
  });

  focusFirstField();
})();
