(() => {
  const modal = document.getElementById('note-payment-modal');
  const form = document.getElementById('note-payment-form');
  if (!modal || !form) return;
  const q = (s) => Array.from(modal.querySelectorAll(s));
  const byId = (id) => document.getElementById(id);
  let pendingRowId = null;
  const parseNumber = (v) => Number.parseInt(String(v || '').replace(/[^0-9]/g, '') || '0', 10);
  const format = (v) => new Intl.NumberFormat('id-ID').format(Number.isFinite(v) ? v : 0);
  const boxes = () => q('[data-payment-row-checkbox]');
  const selected = () => boxes().filter((el) => el.checked);
  const outstanding = () => selected().reduce((s, el) => s + parseNumber(el.dataset.outstandingRupiah), 0);
  const amountInput = () => byId('amount-paid');
  const receivedInput = () => byId('amount-received');
  const methodInput = () => byId('payment-method');
  const payNow = () => {
    const typed = parseNumber(amountInput()?.value || '');
    const total = outstanding();
    return typed > 0 ? Math.min(typed, total) : total;
  };
  const setDefaultSelection = () => {
    if (!pendingRowId) return;
    boxes().forEach((el) => { el.checked = el.dataset.rowId === pendingRowId; });
    pendingRowId = null;
  };
  const update = () => {
    const count = selected().length;
    const total = outstanding();
    const paid = payNow();
    const received = parseNumber(receivedInput()?.value || '');
    const isCash = methodInput()?.value === 'cash';
    byId('payment-modal-selected-count').textContent = format(count);
    byId('payment-modal-selected-total').textContent = format(total);
    byId('payment-modal-pay-now').textContent = format(paid);
    byId('payment-remaining-text').textContent = format(Math.max(total - paid, 0));
    byId('payment-change-text').textContent = format(isCash ? Math.max(received - paid, 0) : 0);
    if (receivedInput()) receivedInput().disabled = !isCash;
    const submit = byId('note-payment-submit');
    if (submit) submit.disabled = count <= 0 || paid <= 0 || (isCash && received > 0 && received < paid);
  };
  document.addEventListener('click', (e) => {
    const button = e.target.closest('.js-open-payment-modal');
    if (button) pendingRowId = button.dataset.defaultRowId || null;
  });
  modal.addEventListener('shown.bs.modal', () => { setDefaultSelection(); update(); });
  boxes().forEach((el) => el.addEventListener('change', update));
  form.addEventListener('input', update);
  form.addEventListener('change', update);
  update();
})();
