(() => {
  const modal = document.getElementById('note-refund-modal');
  const form = document.getElementById('note-refund-form');
  const openButton = document.getElementById('note-refund-open-button');
  if (!modal || !form || !openButton) return;

  const byId = (id) => document.getElementById(id);
  const parseNumber = (value) => Number.parseInt(String(value || '').replace(/[^0-9]/g, '') || '0', 10);
  const format = (value) => new Intl.NumberFormat('id-ID').format(Number.isFinite(value) ? value : 0);
  const rows = () => Array.from(document.querySelectorAll('[data-refund-row]'));
  const selectedRows = () => rows().filter((row) => row.dataset.selected === '1');
  const refundInput = () => byId('refund_amount_rupiah');
  const selectedContainer = byId('note-refund-selected-lines');
  const hiddenInputsContainer = byId('note-refund-hidden-selected-rows');

  const refundableTotal = () =>
    selectedRows().reduce((sum, row) => sum + parseNumber(row.dataset.refundableRupiah), 0);

  const stockReturnCount = () =>
    selectedRows().reduce((sum, row) => sum + parseNumber(row.dataset.storeReturnCount), 0);

  const externalCount = () =>
    selectedRows().reduce((sum, row) => sum + parseNumber(row.dataset.externalCount), 0);

  const refundNow = () => {
    const typed = parseNumber(refundInput()?.value || '');
    const total = refundableTotal();
    return typed > 0 ? Math.min(typed, total) : total;
  };

  const syncRowVisual = (row) => {
    const selected = row.dataset.selected === '1';
    row.classList.toggle('table-active', selected);
    row.classList.toggle('border-dark', selected);
    row.classList.toggle('shadow-sm', selected);
    row.setAttribute('aria-pressed', selected ? 'true' : 'false');
  };

  const buildHiddenInputs = () => {
    if (!hiddenInputsContainer) return;
    hiddenInputsContainer.innerHTML = selectedRows()
      .map((row) => `<input type="hidden" name="selected_row_ids[]" value="${row.dataset.rowId}">`)
      .join('');
  };

  const buildSelectedLinesSummary = () => {
    if (!selectedContainer) return;

    const items = selectedRows();
    if (items.length === 0) {
      selectedContainer.innerHTML = '<div class="small text-muted">Belum ada line dipilih.</div>';
      return;
    }

    selectedContainer.innerHTML = items
      .map((row) => {
        const lineNo = row.dataset.lineNo || '-';
        const label = row.dataset.lineLabel || '-';
        const typeLabel = row.dataset.typeLabel || '-';
        const preview = row.dataset.previewLabel || '-';
        const refundable = format(parseNumber(row.dataset.refundableRupiah));

        return `
          <div class="border rounded px-3 py-2">
            <div class="d-flex justify-content-between align-items-start gap-3">
              <div>
                <div class="fw-semibold">Line ${lineNo} · ${label}</div>
                <div class="small text-muted">${typeLabel}</div>
                <div class="small text-muted">${preview}</div>
              </div>
              <strong>${refundable}</strong>
            </div>
          </div>
        `;
      })
      .join('');
  };

  const updateSummary = () => {
    const count = selectedRows().length;
    const total = refundableTotal();
    const amount = refundNow();

    const countNode = byId('refund-modal-selected-count');
    if (countNode) countNode.textContent = format(count);

    const totalNode = byId('refund-modal-selected-total');
    if (totalNode) totalNode.textContent = format(total);

    const stockNode = byId('refund-modal-stock-return-count');
    if (stockNode) stockNode.textContent = format(stockReturnCount());

    const externalNode = byId('refund-modal-external-count');
    if (externalNode) externalNode.textContent = format(externalCount());

    const refundNowNode = byId('refund-modal-refund-now');
    if (refundNowNode) refundNowNode.textContent = format(amount);

    openButton.disabled = count <= 0;

    const submit = byId('note-refund-submit');
    if (submit) submit.disabled = count <= 0 || amount <= 0;

    buildHiddenInputs();
    buildSelectedLinesSummary();
  };

  const toggleRow = (row) => {
    row.dataset.selected = row.dataset.selected === '1' ? '0' : '1';
    syncRowVisual(row);
    updateSummary();
  };

  rows().forEach((row) => {
    syncRowVisual(row);

    row.addEventListener('click', (event) => {
      const target = event.target;
      if (target instanceof HTMLElement && target.closest('a, button, input, textarea, select, label')) {
        return;
      }

      toggleRow(row);
    });

    row.addEventListener('keydown', (event) => {
      if (event.key !== 'Enter' && event.key !== ' ') return;
      event.preventDefault();
      toggleRow(row);
    });
  });

  form.addEventListener('input', updateSummary);
  form.addEventListener('change', updateSummary);
  updateSummary();
})();
