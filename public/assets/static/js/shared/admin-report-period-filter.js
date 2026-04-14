(() => {
  const selector = '[data-report-period-filter="1"]';

  const getForms = () => Array.from(document.querySelectorAll(selector));

  const byId = (id) => {
    const value = String(id || '').trim();
    return value === '' ? null : document.getElementById(value);
  };

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

  const updateDateUiMode = (form) => {
    const enhancedWrap = form.querySelector('[data-report-range-enhanced-wrap]');
    const fallbackWrap = form.querySelector('[data-report-range-fallback-wrap]');
    const enhancedInput = form.querySelector('[data-ui-date="range-single"]');
    const enhancedReady = Boolean(enhancedInput && enhancedInput._flatpickr);

    enhancedWrap?.classList.toggle('d-none', !enhancedReady);
    fallbackWrap?.classList.toggle('d-none', enhancedReady);
  };

  const refreshDateUi = (form) => {
    window.AdminDateInput?.bindBySelector(form);
    window.AdminDateInput?.refreshWithin(form);
    updateDateUiMode(form);
  };

  const drawOpen = (drawer, backdrop, open) => {
    drawer?.classList.toggle('d-none', !open);
    backdrop?.classList.toggle('d-none', !open);
  };

  const bindForm = (form) => {
    if (!form || form.dataset.reportPeriodFilterBound === '1') {
      return;
    }

    const openButton = byId(form.dataset.filterOpenButtonId);
    const closeButton = byId(form.dataset.filterCloseButtonId);
    const drawer = byId(form.dataset.filterDrawerId);
    const backdrop = byId(form.dataset.filterBackdropId);
    const fallbackWrap = form.querySelector('[data-report-range-fallback-wrap]');
    const fallbackFrom = form.querySelector('[data-report-date-fallback-from]');
    const fallbackTo = form.querySelector('[data-report-date-fallback-to]');

    syncFallbackFromHidden(form);
    refreshDateUi(form);

    openButton?.addEventListener('click', () => {
      refreshDateUi(form);
      drawOpen(drawer, backdrop, true);
    });

    closeButton?.addEventListener('click', () => {
      drawOpen(drawer, backdrop, false);
    });

    backdrop?.addEventListener('click', () => {
      drawOpen(drawer, backdrop, false);
    });

    fallbackFrom?.addEventListener('input', () => syncHiddenFromFallback(form));
    fallbackTo?.addEventListener('input', () => syncHiddenFromFallback(form));
    fallbackFrom?.addEventListener('change', () => syncHiddenFromFallback(form));
    fallbackTo?.addEventListener('change', () => syncHiddenFromFallback(form));

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
