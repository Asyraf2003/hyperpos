(() => {
  const modalElement = document.getElementById("package-service-action-modal");
  if (!modalElement) return;

  const subtitle = document.getElementById("package-service-action-modal-subtitle");
  const detailLink = document.getElementById("package-service-action-detail-link");
  const editLink = document.getElementById("package-service-action-edit-link");
  const productLink = document.getElementById("package-service-action-product-link");
  const serviceLink = document.getElementById("package-service-action-service-link");
  const statusForm = document.getElementById("package-service-action-status-form");
  const statusButton = document.getElementById("package-service-action-status-button");
  const statusTitle = document.getElementById("package-service-action-status-title");

  const modal = window.bootstrap && window.bootstrap.Modal
    ? new window.bootstrap.Modal(modalElement)
    : null;

  document.addEventListener("click", (event) => {
    const button = event.target.closest("[data-package-action='open']");
    if (!button) return;

    const status = button.dataset.packageStatus || "inactive";
    const isActive = status === "active";

    if (subtitle) {
      subtitle.textContent = `${button.dataset.packageName || "Paket Service"} • ${button.dataset.packageProduct || "-"}`;
    }

    if (detailLink) detailLink.href = button.dataset.detailUrl || "#";
    if (editLink) editLink.href = button.dataset.editUrl || "#";
    if (productLink) productLink.href = button.dataset.productUrl || "#";
    if (serviceLink) serviceLink.href = button.dataset.serviceUrl || "#";

    if (statusForm) {
      statusForm.action = isActive
        ? (button.dataset.deactivateUrl || "#")
        : (button.dataset.reactivateUrl || "#");
    }

    if (statusTitle) {
      statusTitle.textContent = isActive ? "Nonaktifkan Paket" : "Aktifkan Paket";
    }

    if (statusButton) {
      statusButton.classList.toggle("btn-outline-warning", isActive);
      statusButton.classList.toggle("btn-outline-success", !isActive);
    }

    if (modal) {
      modal.show();
      return;
    }

    window.location.href = button.dataset.detailUrl || button.dataset.editUrl || "#";
  });
})();
