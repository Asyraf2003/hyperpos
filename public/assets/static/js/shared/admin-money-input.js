(() => {
  const digitsOnly = (value) => String(value ?? "").replace(/\D+/g, "");

  const formatThousands = (value) => {
    const digits = digitsOnly(value);

    if (digits === "") {
      return "";
    }

    return Number.parseInt(digits, 10).toLocaleString("id-ID");
  };

  const bindMoneyPair = (display, raw) => {
    if (!display || !raw) {
      return;
    }

    const sync = () => {
      raw.value = digitsOnly(display.value);
      display.value = formatThousands(display.value);
    };

    display.addEventListener("input", sync);
    display.addEventListener("blur", sync);
    sync();
  };

  const bindBySelector = (root = document) => {
    root.querySelectorAll("[data-money-input-group]").forEach((group) => {
      const display = group.querySelector("[data-money-display]");
      const raw = group.querySelector("[data-money-raw]");
      bindMoneyPair(display, raw);
    });
  };

  window.AdminMoneyInput = {
    digitsOnly,
    formatThousands,
    bindMoneyPair,
    bindBySelector,
  };
})();
