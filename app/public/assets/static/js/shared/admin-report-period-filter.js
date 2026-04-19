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

    return String(form.dataset.modeHelpDaily || '').trim();
  };

  const refreshDateUi = (form) => {
    window.AdminDateInput?.bindBySelector(form);
    window.AdminDateInput?.refreshWithin(form);
  };

  const drawOpen = (drawer, backdrop, open) => {
    drawer?.classList.toggle('d-none', !open);
    backdrop?.classList.toggle('d-none', !open);
  };

  const updateModeState = (form) => {
    const mode = getMode(form);
    const helpBox = form.querySelector('[data-report-mode-help]');

    if (helpBox) {
      helpBox.textContent = getModeHelpText(form, mode);
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
    const modeSelect = form.querySelector('[data-report-period-mode-select]');

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
