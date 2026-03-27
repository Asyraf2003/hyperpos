document.addEventListener('DOMContentLoaded', () => {
  const form = document.getElementById('note-payment-form');
  if (!form) return;

  const money = (v) => new Intl.NumberFormat('id-ID').format(Number(v || 0));
  const method = document.getElementById('payment-method');
  const amountReceived = document.getElementById('amount-received');
  const selectedTotal = document.getElementById('selected-payment-total');
  const changeText = document.getElementById('payment-change-text');

  const recalc = () => {
    let total = 0;
    form.querySelectorAll('[data-payment-row]:checked').forEach((el) => { total += Number(el.dataset.subtotal || 0); });
    selectedTotal.value = money(total);

    if (method.value !== 'cash') {
      changeText.textContent = '0';
      return;
    }

    const change = Math.max((parseInt(amountReceived.value || '0', 10) || 0) - total, 0);
    changeText.textContent = money(change);
  };

  form.addEventListener('change', recalc);
  form.addEventListener('input', recalc);
  recalc();
});
