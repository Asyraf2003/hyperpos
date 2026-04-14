(() => {
  const selector = '[data-report-period-filter="1"]';

  const getForms = () => Array.from(document.querySelectorAll(selector));

  const syncFallbackFromHidden = (form) => {
    const hiddenFrom = form.querySelector('input[name="date_from"]');
    const hiddenTo = form.querySelector('input[name="date_to"]');
    const fallbackFrom = form.querySelector('[data-report-date-fallback-from]');
    const fallbackTo = form.querySelector('[data-report-date-fallback-to]');

    if (!hiddenFrom || !hiddenTo || !fallbackFrom || !fallbackTo) {
      return;
    }

    fallbackFrom.value = String(hiddenFrom.value || '').trim();
    fallbackTo.value = String(hiddenTo.value || '').trim();
  };

  const syncHiddenFromFallback = (form) => {
    const hiddenFrom = form.querySelector('input[name="date_from"]');
    const hiddenTo = form.querySelector('input[name="date_to"]');
    const fallbackFrom = form.querySelector('[data-report-date-fallback-from]');
    const fallbackTo = form.querySelector('[data-report-date-fallback-to]');

    if (!hiddenFrom || !hiddenTo || !fallbackFrom || !fallbackTo) {
      return;
    }

    hiddenFrom.value = String(fallbackFrom.value || '').trim();
    hiddenTo.value = String(fallbackTo.value || '').trim();
  };

  const togglePickerMode = (form) => {
    const enhancedWrap = form.querySelector('[data-report-range-enhanced-wrap]');
    const fallbackWrap = form.querySelector('[data-report-range-fallback-wrap]');
    const enhancedAvailable = typeof window.flatpickr === 'function' && !!window.AdminDateInput;

    if (enhancedAvailable) {
      enhancedWrap?.classList.remove('d-none');
      fallbackWrap?.classList.add('d-none');
      window.AdminDateInput.bindBySelector(form);
      window.AdminDateInput.refreshWithin(form);
      return;
    }

    enhancedWrap?.classList.add('d-none');
    fallbackWrap?.classList.remove('d-none');
  };

  const bindForm = (form) => {
    if (!form || form.dataset.reportPeriodFilterBound === '1') {
      return;
    }

    syncFallbackFromHidden(form);
    togglePickerMode(form);

    const fallbackFrom = form.querySelector('[data-report-date-fallback-from]');
    const fallbackTo = form.querySelector('[data-report-date-fallback-to]');
    const fallbackWrap = form.querySelector('[data-report-range-fallback-wrap]');

    fallbackFrom?.addEventListener('input', () => syncHiddenFromFallback(form));
    fallbackTo?.addEventListener('input', () => syncHiddenFromFallback(form));

    form.addEventListener('submit', () => {
      if (!fallbackWrap || fallbackWrap.classList.contains('d-none')) {
        return;
      }

      syncHiddenFromFallback(form);
    });

    form.dataset.reportPeriodFilterBound = '1';
  };

  const boot = () => {
    getForms().forEach(bindForm);
  };

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', boot);
  } else {
    boot();
  }
})();
