(() => {
  const modal = document.getElementById('note-refund-modal');
  const form = document.getElementById('note-refund-form');
  if (!modal || !form) return;
  const q = (s) => Array.from(modal.querySelectorAll(s));
  const byId = (id) => document.getElementById(id);
  let pendingRowId = null;
  const parseNumber = (v) => Number.parseInt(String(v || '').replace(/[^0-9]/g, '') || '0', 10);
  const format = (v) => new Intl.NumberFormat('id-ID').format(Number.isFinite(v) ? v : 0);
  const boxes = () => q('[data-refund-row-checkbox]');
  const selected = () => boxes().filter((el) => el.checked);
  const refundable = () => selected().reduce((s, el) => s + parseNumber(el.dataset.refundableRupiah), 0);
  const refundInput = () => byId('refund_amount_rupiah');
  const refundNow = () => {
    const typed = parseNumber(refundInput()?.value || '');
    const total = refundable();
    return typed > 0 ? Math.min(typed, total) : total;
  };
  const setDefaultSelection = () => {
    if (!pendingRowId) return;
    boxes().forEach((el) => { el.checked = el.dataset.rowId === pendingRowId; });
    pendingRowId = null;
  };
  const update = () => {
    const count = selected().length;
    const total = refundable();
    const amount = refundNow();
    byId('refund-modal-selected-count').textContent = format(count);
    byId('refund-modal-selected-total').textContent = format(total);
    byId('refund-modal-refund-now').textContent = format(amount);
    const submit = byId('note-refund-submit');
    if (submit) submit.disabled = count <= 0 || amount <= 0;
  };
  document.addEventListener('click', (e) => {
    const button = e.target.closest('.js-open-refund-modal');
    if (button) pendingRowId = button.dataset.defaultRowId || null;
  });
  modal.addEventListener('shown.bs.modal', () => { setDefaultSelection(); update(); });
  boxes().forEach((el) => el.addEventListener('change', update));
  form.addEventListener('input', update);
  form.addEventListener('change', update);
  update();
})();
