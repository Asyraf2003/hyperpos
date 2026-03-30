(() => {
  const format = (value) => Number(value || 0).toLocaleString("id-ID");

  document.addEventListener("click", (event) => {
    const trigger = event.target.closest("[data-mode]");
    if (!trigger) return;

    const modal = trigger.closest("[data-prototype='a']");
    if (!modal) return;

    modal.querySelectorAll("[data-mode]").forEach((button) => button.classList.remove("active"));
    trigger.classList.add("active");

    const mode = trigger.dataset.mode || "skip";
    modal.querySelectorAll("[data-panel]").forEach((panel) => panel.classList.add("d-none"));
    modal.querySelector(`[data-panel='${mode}']`)?.classList.remove("d-none");

    const footerLabel = modal.querySelector("[data-action-label]");
    if (footerLabel) {
      footerLabel.textContent =
        mode === "skip" ? "Simpan Nota" :
        mode === "full" ? "Pilih TF / Cash" :
        "Isi nominal lalu pilih TF / Cash";
    }

    const paidNow = modal.querySelector("[data-paid-now]");
    const outstanding = modal.querySelector("[data-outstanding]");

    if (paidNow && outstanding) {
      if (mode === "skip") {
        paidNow.textContent = format(0);
        outstanding.textContent = format(250000);
      } else if (mode === "full") {
        paidNow.textContent = format(250000);
        outstanding.textContent = format(0);
      } else {
        paidNow.textContent = format(100000);
        outstanding.textContent = format(150000);
      }
    }
  });

  document.addEventListener("click", (event) => {
    const openCash = event.target.closest("[data-open-cash]");
    if (openCash) {
      const modal = openCash.closest("[data-prototype='a']");
      modal.querySelectorAll("[data-panel]").forEach((panel) => panel.classList.add("d-none"));
      modal.querySelector("[data-panel='cash']")?.classList.remove("d-none");
    }

    const backCash = event.target.closest("[data-back-cash]");
    if (backCash) {
      const modal = backCash.closest("[data-prototype='a']");
      const active = modal.querySelector("[data-mode].active")?.dataset.mode || "skip";
      modal.querySelectorAll("[data-panel]").forEach((panel) => panel.classList.add("d-none"));
      modal.querySelector(`[data-panel='${active}']`)?.classList.remove("d-none");
    }
  });
})();
