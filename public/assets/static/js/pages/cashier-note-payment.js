(() => {
  const modal = document.getElementById('note-payment-modal');
  const form = document.getElementById('note-payment-form');
  if (!modal || !form) return;

  const byId = (id) => document.getElementById(id);
  const q = (selector) => Array.from(modal.querySelectorAll(selector));
  const parseNumber = (value) => Number.parseInt(String(value || '').replace(/[^0-9]/g, '') || '0', 10);
  const format = (value) => new Intl.NumberFormat('id-ID').format(Number.isFinite(value) ? value : 0);

  const boxes = () => q('[data-billing-row-checkbox]');
  const selectedBoxes = () => boxes().filter((el) => el.checked && !el.disabled);
  const amountInput = () => byId('amount-paid');
  const receivedInput = () => byId('amount-received');
  const methodInput = () => byId('payment-method');
  const presetInput = () => byId('payment-preset-mode');
  const intentInput = () => byId('payment-intent');
  const hiddenRowsContainer = () => byId('payment-selected-row-ids');
  const payNow = () => {
    const typed = parseNumber(amountInput()?.value || '');
    const total = selectedOutstanding();
    return typed > 0 ? Math.min(typed, total) : total;
  };

  let openIntent = 'pay';
  let openPreset = 'manual';

  const selectedOutstanding = () => selectedBoxes().reduce((sum, el) => sum + parseNumber(el.dataset.outstandingRupiah), 0);
  const selectedWorkItemIds = () => Array.from(new Set(selectedBoxes().map((el) => el.dataset.workItemId).filter(Boolean)));

  const syncHiddenSelectedRows = () => {
    const container = hiddenRowsContainer();
    if (!container) return;
    container.innerHTML = selectedWorkItemIds()
      .map((id) => `<input type="hidden" name="selected_row_ids[]" value="${id}">`)
      .join('');
  };

  const applyManualSelection = () => {
    boxes().forEach((el) => {
      if (!el.disabled) {
        el.checked = false;
      }
    });
  };

  const applyDpPreset = () => {
    boxes().forEach((el) => {
      if (el.disabled) {
        el.checked = false;
        return;
      }
      el.checked = el.dataset.eligibleDp === '1';
    });
  };

  const applySettlePreset = () => {
    boxes().forEach((el) => {
      if (el.disabled) {
        el.checked = false;
        return;
      }
      el.checked = true;
    });
  };

  const applyIntent = () => {
    if (intentInput()) {
      intentInput().value = openIntent;
    }

    if (presetInput()) {
      presetInput().value = openIntent === 'settle' ? 'manual' : openPreset;
      presetInput().disabled = openIntent === 'settle';
    }

    if (openIntent === 'settle') {
      applySettlePreset();
      const badge = byId('payment-intent-badge');
      if (badge) badge.textContent = 'Lunasi';
      return;
    }

    const badge = byId('payment-intent-badge');
    if (badge) badge.textContent = openPreset === 'dp' ? 'Bayar · DP' : 'Bayar';

    if (openPreset === 'dp') {
      applyDpPreset();
      return;
    }

    applyManualSelection();
  };

  const update = () => {
    const selected = selectedBoxes();
    const count = selected.length;
    const lineCount = selectedWorkItemIds().length;
    const total = selectedOutstanding();
    const paid = payNow();
    const received = parseNumber(receivedInput()?.value || '');
    const isCash = methodInput()?.value === 'cash';

    syncHiddenSelectedRows();

    const selectedCountNode = byId('payment-modal-selected-count');
    if (selectedCountNode) selectedCountNode.textContent = format(count);
    const selectedLineCountNode = byId('payment-modal-selected-line-count');
    if (selectedLineCountNode) selectedLineCountNode.textContent = format(lineCount);
    const selectedTotalNode = byId('payment-modal-selected-total');
    if (selectedTotalNode) selectedTotalNode.textContent = format(total);
    const payNowNode = byId('payment-modal-pay-now');
    if (payNowNode) payNowNode.textContent = format(paid);
    const remainingNode = byId('payment-remaining-text');
    if (remainingNode) remainingNode.textContent = format(Math.max(total - paid, 0));
    const changeNode = byId('payment-change-text');
    if (changeNode) changeNode.textContent = format(isCash ? Math.max(received - paid, 0) : 0);

    if (receivedInput()) receivedInput().disabled = !isCash;

    const submit = byId('note-payment-submit');
    if (submit) {
      submit.disabled = lineCount <= 0 || paid <= 0 || (isCash && received > 0 && received < paid);
    }
  };

  document.addEventListener('click', (event) => {
    const button = event.target.closest('.js-open-payment-intent');
    if (!button) return;
    openIntent = button.dataset.paymentIntent || 'pay';
    openPreset = button.dataset.paymentPreset || 'manual';
  });

  if (presetInput()) {
    presetInput().addEventListener('change', () => {
      openPreset = presetInput().value || 'manual';
      if (openIntent === 'pay') {
        applyIntent();
        update();
      }
    });
  }

  modal.addEventListener('shown.bs.modal', () => {
    applyIntent();
    update();
  });

  boxes().forEach((el) => el.addEventListener('change', update));
  form.addEventListener('input', update);
  form.addEventListener('change', update);
  update();
})();
