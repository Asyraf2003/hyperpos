(() => {
  const shouldReload = (event) => {
    if (event && event.persisted === true) {
      return true;
    }

    const entries = typeof performance !== "undefined" && typeof performance.getEntriesByType === "function"
      ? performance.getEntriesByType("navigation")
      : [];

    const navigationEntry = Array.isArray(entries) && entries.length > 0 ? entries[0] : null;
    return navigationEntry?.type === "back_forward";
  };

  window.addEventListener("pageshow", (event) => {
    if (!shouldReload(event)) {
      return;
    }

    window.location.reload();
  });
})();
