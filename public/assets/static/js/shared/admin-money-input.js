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

    if (display.dataset.moneyBound === "1") {
      return;
    }

    const sync = () => {
      const sourceValue = display.value !== "" ? display.value : raw.value;
      const digits = digitsOnly(sourceValue);

      raw.value = digits;
      display.value = formatThousands(digits);
    };

    display.addEventListener("input", sync);
    display.addEventListener("blur", sync);
    display.dataset.moneyBound = "1";
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
