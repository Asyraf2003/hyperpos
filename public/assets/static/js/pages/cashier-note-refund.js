(() => {
  const init = () => {
    const modal = document.getElementById('note-refund-modal');
    const form = document.getElementById('note-refund-form');
    const openButton = document.getElementById('note-refund-open-button');
    const selectedContainer = document.getElementById('note-refund-selected-lines');
    const hiddenInputsContainer = document.getElementById('note-refund-hidden-selected-rows');
    const submitButton = document.getElementById('note-refund-submit');
    const refundInput = document.getElementById('refund_amount_rupiah');

    if (!modal || !form || !openButton || !selectedContainer || !hiddenInputsContainer) {
      return;
    }

    const parseNumber = (value) =>
      Number.parseInt(String(value || '').replace(/[^0-9]/g, '') || '0', 10);

    const format = (value) =>
      new Intl.NumberFormat('id-ID').format(Number.isFinite(value) ? value : 0);

    const rows = () => Array.from(document.querySelectorAll('[data-refund-row="1"]'));
    const selectedRows = () => rows().filter((row) => String(row.dataset.selected || '0') === '1');

    const paintRow = (row) => {
      const selected = String(row.dataset.selected || '0') === '1';
      row.setAttribute('aria-pressed', selected ? 'true' : 'false');

      Array.from(row.children).forEach((cell) => {
        if (!(cell instanceof HTMLElement)) return;
        cell.style.backgroundColor = selected ? '#dbeafe' : '';
        cell.style.boxShadow = selected ? 'inset 0 0 0 9999px rgba(30,64,175,0.10)' : '';
      });
    };

    const refundableTotal = () =>
      selectedRows().reduce((sum, row) => sum + parseNumber(row.dataset.refundableRupiah), 0);

    const stockReturnCount = () =>
      selectedRows().reduce((sum, row) => sum + parseNumber(row.dataset.storeReturnCount), 0);

    const externalCount = () =>
      selectedRows().reduce((sum, row) => sum + parseNumber(row.dataset.externalCount), 0);

    const refundNow = () => {
      const typed = parseNumber(refundInput?.value || '');
      const total = refundableTotal();
      return typed > 0 ? Math.min(typed, total) : total;
    };

    const buildHiddenInputs = () => {
      hiddenInputsContainer.innerHTML = selectedRows()
        .map((row) => `<input type="hidden" name="selected_row_ids[]" value="${row.dataset.rowId}">`)
        .join('');
    };

    const buildSelectedLinesSummary = () => {
      const items = selectedRows();

      if (items.length === 0) {
        selectedContainer.innerHTML = '<div class="small text-muted">Belum ada line dipilih.</div>';
        return;
      }

      selectedContainer.innerHTML = items.map((row) => {
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
      }).join('');
    };

    const updateSummary = () => {
      const count = selectedRows().length;
      const total = refundableTotal();
      const amount = refundNow();

      const countNode = document.getElementById('refund-modal-selected-count');
      const totalNode = document.getElementById('refund-modal-selected-total');
      const stockNode = document.getElementById('refund-modal-stock-return-count');
      const externalNode = document.getElementById('refund-modal-external-count');
      const refundNowNode = document.getElementById('refund-modal-refund-now');

      if (countNode) countNode.textContent = format(count);
      if (totalNode) totalNode.textContent = format(total);
      if (stockNode) stockNode.textContent = format(stockReturnCount());
      if (externalNode) externalNode.textContent = format(externalCount());
      if (refundNowNode) refundNowNode.textContent = format(amount);

      openButton.disabled = count <= 0;
      openButton.classList.toggle('disabled', count <= 0);
      openButton.setAttribute('aria-disabled', count <= 0 ? 'true' : 'false');

      if (submitButton) {
        submitButton.disabled = count <= 0 || amount <= 0;
      }

      buildHiddenInputs();
      buildSelectedLinesSummary();
    };

    const toggleRow = (row) => {
      row.dataset.selected = String(row.dataset.selected || '0') === '1' ? '0' : '1';
      paintRow(row);
      updateSummary();
    };

    rows().forEach((row) => paintRow(row));

    document.addEventListener('click', (event) => {
      const target = event.target;
      if (!(target instanceof HTMLElement)) return;

      const row = target.closest('[data-refund-row="1"]');
      if (!row) return;

      if (target.closest('a, button, input, textarea, select, label')) {
        return;
      }

      toggleRow(row);
    });

    document.addEventListener('keydown', (event) => {
      const target = event.target;
      if (!(target instanceof HTMLElement)) return;

      const row = target.closest('[data-refund-row="1"]');
      if (!row) return;

      if (event.key !== 'Enter' && event.key !== ' ') return;
      event.preventDefault();
      toggleRow(row);
    });

    openButton.addEventListener('click', (event) => {
      if (selectedRows().length > 0) return;
      event.preventDefault();
      event.stopPropagation();
      return false;
    });

    form.addEventListener('input', updateSummary);
    form.addEventListener('change', updateSummary);

    if (submitButton) {
      submitButton.disabled = true;
    }

    updateSummary();
  };

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})();
