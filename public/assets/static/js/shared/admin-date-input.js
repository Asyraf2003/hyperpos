(() => {
  const canonicalDateFormat = 'Y-m-d';
  const singleAltFormat = 'd F Y';
  const rangeDisplayFormat = 'd F Y';

  const singleSelector = 'input[data-ui-date="single"]';
  const rangeSelector = 'input[data-ui-date="range"]';
  const rangeSingleSelector = 'input[data-ui-date="range-single"]';

  const defaultSinglePlaceholder = 'Pilih tanggal';
  const defaultRangeStartPlaceholder = 'Tanggal mulai';
  const defaultRangeEndPlaceholder = 'Tanggal akhir';
  const defaultRangeSinglePlaceholder = 'Pilih rentang tanggal';

  const getLocale = () => {
    if (!window.flatpickr) {
      return 'id';
    }

    return window.flatpickr.l10ns?.id ?? 'id';
  };

  const queryByName = (root, name) => {
    if (!root || !name) {
      return null;
    }

    return root.querySelector(`[name="${name}"]`);
  };

  const getFormsInScope = (root) => {
    if (!root || root === document) {
      return Array.from(document.querySelectorAll('form'));
    }

    const forms = [];

    if (root.matches?.('form')) {
      forms.push(root);
    }

    forms.push(...root.querySelectorAll?.('form') ?? []);

    return forms;
  };

  const resolvePlaceholder = (input, fallback) => {
    const fromData = String(input?.dataset?.uiDatePlaceholder || '').trim();
    if (fromData !== '') {
      return fromData;
    }

    const fromAttr = String(input?.getAttribute?.('placeholder') || '').trim();
    if (fromAttr !== '') {
      return fromAttr;
    }

    return fallback;
  };

  const applyPlaceholder = (fp, placeholder) => {
    if (!fp || !placeholder) {
      return;
    }

    if (fp.altInput) {
      fp.altInput.placeholder = placeholder;
    }

    if (fp.mobileInput) {
      fp.mobileInput.placeholder = placeholder;
    }

    if (fp.input) {
      fp.input.placeholder = placeholder;
    }
  };

  const syncRangeConstraints = (startInput, endInput) => {
    if (!startInput?._flatpickr || !endInput?._flatpickr) {
      return;
    }

    const startValue = String(startInput.value || '').trim();
    const endValue = String(endInput.value || '').trim();

    endInput._flatpickr.set('minDate', startValue === '' ? null : startValue);
    startInput._flatpickr.set('maxDate', endValue === '' ? null : endValue);
  };

  const bindSingle = (input) => {
    if (!input || input.dataset.uiDateBound === '1' || input._flatpickr) {
      return;
    }

    const placeholder = resolvePlaceholder(input, defaultSinglePlaceholder);

    const instance = window.flatpickr(input, {
      locale: getLocale(),
      dateFormat: canonicalDateFormat,
      altInput: true,
      altFormat: singleAltFormat,
      disableMobile: true,
      allowInput: false,
      monthSelectorType: 'static',
      static: window.matchMedia?.('(pointer: coarse)').matches ?? false,
      onReady: (_selectedDates, _dateStr, fp) => {
        applyPlaceholder(fp, placeholder);
      },
      onValueUpdate: (_selectedDates, _dateStr, fp) => {
        applyPlaceholder(fp, placeholder);
      },
    });

    applyPlaceholder(instance, placeholder);
    input.dataset.uiDateBound = '1';
  };

  const bindRangePair = (startInput, endInput) => {
    if (!startInput || !endInput) {
      return;
    }

    const startPlaceholder = resolvePlaceholder(startInput, defaultRangeStartPlaceholder);
    const endPlaceholder = resolvePlaceholder(endInput, defaultRangeEndPlaceholder);

    if (!startInput._flatpickr) {
      const startInstance = window.flatpickr(startInput, {
        locale: getLocale(),
        dateFormat: canonicalDateFormat,
        altInput: true,
        altFormat: singleAltFormat,
        disableMobile: true,
        allowInput: false,
        monthSelectorType: 'static',
        onReady: (_selectedDates, _dateStr, fp) => {
          applyPlaceholder(fp, startPlaceholder);
          syncRangeConstraints(startInput, endInput);
        },
        onChange: () => syncRangeConstraints(startInput, endInput),
        onValueUpdate: (_selectedDates, _dateStr, fp) => {
          applyPlaceholder(fp, startPlaceholder);
        },
      });

      applyPlaceholder(startInstance, startPlaceholder);
    }

    if (!endInput._flatpickr) {
      const endInstance = window.flatpickr(endInput, {
        locale: getLocale(),
        dateFormat: canonicalDateFormat,
        altInput: true,
        altFormat: singleAltFormat,
        disableMobile: true,
        allowInput: false,
        monthSelectorType: 'static',
        onReady: (_selectedDates, _dateStr, fp) => {
          applyPlaceholder(fp, endPlaceholder);
          syncRangeConstraints(startInput, endInput);
        },
        onChange: () => syncRangeConstraints(startInput, endInput),
        onValueUpdate: (_selectedDates, _dateStr, fp) => {
          applyPlaceholder(fp, endPlaceholder);
        },
      });

      applyPlaceholder(endInstance, endPlaceholder);
    }

    startInput.dataset.uiDateBound = '1';
    endInput.dataset.uiDateBound = '1';

    syncRangeConstraints(startInput, endInput);
  };

  const syncRangeSingleHiddenFields = (visibleInput, selectedDates) => {
    const form = visibleInput.closest('form');
    if (!form) {
      return;
    }

    const startName = visibleInput.dataset.rangeStartName;
    const endName = visibleInput.dataset.rangeEndName;

    const startInput = queryByName(form, startName);
    const endInput = queryByName(form, endName);

    if (!startInput || !endInput) {
      return;
    }

    if (!Array.isArray(selectedDates) || selectedDates.length === 0) {
      startInput.value = '';
      endInput.value = '';
      return;
    }

    const fp = visibleInput._flatpickr;
    const format = (date) => fp.formatDate(date, canonicalDateFormat);

    startInput.value = format(selectedDates[0]);
    endInput.value = selectedDates[1] ? format(selectedDates[1]) : '';
  };

  const syncRangeSingleVisibleFromHidden = (visibleInput) => {
    if (!visibleInput?._flatpickr) {
      return;
    }

    const form = visibleInput.closest('form');
    if (!form) {
      return;
    }

    const startName = visibleInput.dataset.rangeStartName;
    const endName = visibleInput.dataset.rangeEndName;

    const startInput = queryByName(form, startName);
    const endInput = queryByName(form, endName);

    if (!startInput || !endInput) {
      return;
    }

    const startValue = String(startInput.value || '').trim();
    const endValue = String(endInput.value || '').trim();

    if (startValue === '' && endValue === '') {
      visibleInput._flatpickr.clear(false);
      return;
    }

    if (startValue !== '' && endValue !== '') {
      visibleInput._flatpickr.setDate([startValue, endValue], false, canonicalDateFormat);
      return;
    }

    if (startValue !== '') {
      visibleInput._flatpickr.setDate([startValue], false, canonicalDateFormat);
      return;
    }

    visibleInput._flatpickr.setDate([endValue], false, canonicalDateFormat);
  };

  const bindRangeSingle = (input) => {
    if (!input || input.dataset.uiDateBound === '1' || input._flatpickr) {
      return;
    }

    const form = input.closest('form');
    if (!form) {
      return;
    }

    const startName = input.dataset.rangeStartName;
    const endName = input.dataset.rangeEndName;
    const startInput = queryByName(form, startName);
    const endInput = queryByName(form, endName);

    if (!startInput || !endInput) {
      return;
    }

    const placeholder = resolvePlaceholder(input, defaultRangeSinglePlaceholder);

    const instance = window.flatpickr(input, {
      locale: getLocale(),
      mode: 'range',
      dateFormat: rangeDisplayFormat,
      disableMobile: true,
      allowInput: false,
      monthSelectorType: 'static',
      onReady: (_selectedDates, _dateStr, fp) => {
        applyPlaceholder(fp, placeholder);
        syncRangeSingleVisibleFromHidden(fp.input);
      },
      onChange: (selectedDates, _dateStr, fp) => {
        syncRangeSingleHiddenFields(fp.input, selectedDates);
      },
      onValueUpdate: (_selectedDates, _dateStr, fp) => {
        applyPlaceholder(fp, placeholder);
      },
    });

    applyPlaceholder(instance, placeholder);
    input.dataset.uiDateBound = '1';

    syncRangeSingleVisibleFromHidden(input);
  };

  const bindRangeByForm = (root = document) => {
    const forms = getFormsInScope(root);

    forms.forEach((form) => {
      const inputs = Array.from(form.querySelectorAll(rangeSelector));

      for (let i = 0; i + 1 < inputs.length; i += 2) {
        bindRangePair(inputs[i], inputs[i + 1]);
      }
    });
  };

  const refreshWithin = (root = document) => {
    const scope = root === document ? document : root;

    scope.querySelectorAll?.(`${singleSelector}, ${rangeSelector}`)?.forEach((input) => {
      if (!input?._flatpickr) {
        return;
      }

      const value = String(input.value || '').trim();
      input._flatpickr.setDate(value === '' ? null : value, false, canonicalDateFormat);

      const fallback = input.dataset.uiDate === 'range'
        ? defaultRangeStartPlaceholder
        : defaultSinglePlaceholder;

      applyPlaceholder(input._flatpickr, resolvePlaceholder(input, fallback));
    });

    scope.querySelectorAll?.(rangeSingleSelector)?.forEach((input) => {
      if (!input?._flatpickr) {
        return;
      }

      syncRangeSingleVisibleFromHidden(input);
      applyPlaceholder(input._flatpickr, resolvePlaceholder(input, defaultRangeSinglePlaceholder));
    });

    getFormsInScope(root).forEach((form) => {
      const inputs = Array.from(form.querySelectorAll(rangeSelector));

      for (let i = 0; i + 1 < inputs.length; i += 2) {
        syncRangeConstraints(inputs[i], inputs[i + 1]);
      }
    });
  };

  const bindBySelector = (root = document) => {
    if (!window.flatpickr) {
      return;
    }

    const scope = root === document ? document : root;

    scope.querySelectorAll?.(singleSelector)?.forEach(bindSingle);
    bindRangeByForm(root);
    scope.querySelectorAll?.(rangeSingleSelector)?.forEach(bindRangeSingle);
    refreshWithin(root);
  };

  window.AdminDateInput = {
    bindBySelector,
    refreshWithin,
  };

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => bindBySelector(document));
  } else {
    bindBySelector(document);
  }
})();
