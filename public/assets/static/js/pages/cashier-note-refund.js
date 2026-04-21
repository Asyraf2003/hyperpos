(() => {
  const modal = document.getElementById('note-refund-modal');
  const form = document.getElementById('note-refund-form');
  if (!modal || !form) return;

  const q = (selector) => Array.from(modal.querySelectorAll(selector));
  const byId = (id) => document.getElementById(id);
  const parseNumber = (value) => Number.parseInt(String(value || '').replace(/[^0-9]/g, '') || '0', 10);
  const format = (value) => new Intl.NumberFormat('id-ID').format(Number.isFinite(value) ? value : 0);
  const boxes = () => q('[data-refund-row-checkbox]');
  const selected = () => boxes().filter((el) => el.checked);
  const refundable = () => selected().reduce((sum, el) => sum + parseNumber(el.dataset.refundableRupiah), 0);
  const stockReturns = () => selected().reduce((sum, el) => sum + parseNumber(el.dataset.storeReturnCount), 0);
  const externalCount = () => selected().reduce((sum, el) => sum + parseNumber(el.dataset.externalCount), 0);
  const refundInput = () => byId('refund_amount_rupiah');
  const refundNow = () => {
    const typed = parseNumber(refundInput()?.value || '');
    const total = refundable();
    return typed > 0 ? Math.min(typed, total) : total;
  };

  const update = () => {
    const count = selected().length;
    const total = refundable();
    const amount = refundNow();

    const countNode = byId('refund-modal-selected-count');
    if (countNode) countNode.textContent = format(count);
    const totalNode = byId('refund-modal-selected-total');
    if (totalNode) totalNode.textContent = format(total);
    const stockNode = byId('refund-modal-stock-return-count');
    if (stockNode) stockNode.textContent = format(stockReturns());
    const externalNode = byId('refund-modal-external-count');
    if (externalNode) externalNode.textContent = format(externalCount());
    const refundNowNode = byId('refund-modal-refund-now');
    if (refundNowNode) refundNowNode.textContent = format(amount);

    const submit = byId('note-refund-submit');
    if (submit) submit.disabled = count <= 0 || amount <= 0;
  };

  boxes().forEach((el) => el.addEventListener('change', update));
  form.addEventListener('input', update);
  form.addEventListener('change', update);
  update();
})();
