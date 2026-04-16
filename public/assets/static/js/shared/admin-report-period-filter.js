(() => {
  const selector = '[data-report-period-filter="1"]';

  const getForms = () => Array.from(document.querySelectorAll(selector));

  const byId = (id) => {
    const value = String(id || '').trim();
    return value === '' ? null : document.getElementById(value);
  };

  const getMode = (form) => {
    const select = form.querySelector('[data-report-period-mode-select]');
    return String(select?.value || 'daily').trim() || 'daily';
  };

  const getModeHelpText = (form, mode) => {
    if (mode === 'weekly') {
      return String(form.dataset.modeHelpWeekly || '').trim();
    }

    if (mode === 'monthly') {
      return String(form.dataset.modeHelpMonthly || '').trim();
    }

    if (mode === 'custom') {
      return String(form.dataset.modeHelpCustom || '').trim();
    }

    return String(form.dataset.modeHelpDaily || '').trim();
  };

  const syncFallbackFromHidden = (form) => {
    const hiddenFrom = form.querySelector('[data-report-hidden-date-from]');
    const hiddenTo = form.querySelector('[data-report-hidden-date-to]');
    const fallbackFrom = form.querySelector('[data-report-date-fallback-from]');
    const fallbackTo = form.querySelector('[data-report-date-fallback-to]');

    if (!hiddenFrom || !hiddenTo || !fallbackFrom || !fallbackTo) {
      return;
    }

    fallbackFrom.value = String(hiddenFrom.value || '').trim();
    fallbackTo.value = String(hiddenTo.value || '').trim();
  };

  const syncHiddenFromFallback = (form) => {
    const hiddenFrom = form.querySelector('[data-report-hidden-date-from]');
    const hiddenTo = form.querySelector('[data-report-hidden-date-to]');
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

  const updateModeState = (form) => {
    const mode = getMode(form);
    const isCustom = mode === 'custom';

    const referenceGroup = form.querySelector('[data-report-reference-group]');
    const rangeGroup = form.querySelector('[data-report-range-group]');
    const referenceInput = form.querySelector('[data-report-reference-input]');
    const hiddenFrom = form.querySelector('[data-report-hidden-date-from]');
    const hiddenTo = form.querySelector('[data-report-hidden-date-to]');
    const fallbackFrom = form.querySelector('[data-report-date-fallback-from]');
    const fallbackTo = form.querySelector('[data-report-date-fallback-to]');
    const rangeInput = form.querySelector('[data-report-range-input]');
    const helpBox = form.querySelector('[data-report-mode-help]');

    referenceGroup?.classList.toggle('d-none', isCustom);
    rangeGroup?.classList.toggle('d-none', !isCustom);

    if (referenceInput) {
      referenceInput.disabled = isCustom;
    }

    if (hiddenFrom) {
      hiddenFrom.disabled = !isCustom;
    }

    if (hiddenTo) {
      hiddenTo.disabled = !isCustom;
    }

    if (fallbackFrom) {
      fallbackFrom.disabled = !isCustom;
    }

    if (fallbackTo) {
      fallbackTo.disabled = !isCustom;
    }

    if (rangeInput) {
      rangeInput.disabled = !isCustom;
    }

    if (helpBox) {
      helpBox.textContent = getModeHelpText(form, mode);
    }

    if (isCustom) {
      syncFallbackFromHidden(form);
    }
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
    const modeSelect = form.querySelector('[data-report-period-mode-select]');

    syncFallbackFromHidden(form);
    refreshDateUi(form);
    updateModeState(form);

    openButton?.addEventListener('click', () => {
      refreshDateUi(form);
      updateModeState(form);
      drawOpen(drawer, backdrop, true);
    });

    closeButton?.addEventListener('click', () => {
      drawOpen(drawer, backdrop, false);
    });

    backdrop?.addEventListener('click', () => {
      drawOpen(drawer, backdrop, false);
    });

    modeSelect?.addEventListener('change', () => {
      updateModeState(form);
      refreshDateUi(form);
    });

    fallbackFrom?.addEventListener('input', () => syncHiddenFromFallback(form));
    fallbackTo?.addEventListener('input', () => syncHiddenFromFallback(form));
    fallbackFrom?.addEventListener('change', () => syncHiddenFromFallback(form));
    fallbackTo?.addEventListener('change', () => syncHiddenFromFallback(form));

    form.addEventListener('submit', () => {
      if (getMode(form) !== 'custom') {
        return;
      }

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
