(() => {
  const init = () => {
    const modalEl = document.getElementById('note-refund-modal');
    const form = document.getElementById('note-refund-form');
    const openButton = document.getElementById('note-refund-open-button');
    const selectedContainer = document.getElementById('note-refund-selected-lines');
    const hiddenInputsContainer = document.getElementById('note-refund-hidden-selected-rows');
    const refundInput = document.getElementById('refund_amount_rupiah');
    const submitButton = document.getElementById('note-refund-submit');

    if (!modalEl || !form || !openButton || !selectedContainer || !hiddenInputsContainer) {
      return;
    }

    const modal = window.bootstrap?.Modal
      ? window.bootstrap.Modal.getOrCreateInstance(modalEl)
      : null;

    const parseNumber = (value) =>
      Number.parseInt(String(value || '').replace(/[^0-9]/g, '') || '0', 10);

    const format = (value) =>
      new Intl.NumberFormat('id-ID').format(Number.isFinite(value) ? value : 0);

    const rows = () => Array.from(document.querySelectorAll('[data-refund-row="1"]'));
    const selectedIds = new Set();

    const hydrateInitialSelection = () => {
      rows().forEach((row) => {
        if (String(row.dataset.initialSelected || '0') === '1') {
          selectedIds.add(String(row.dataset.rowId || ''));
        }
      });
    };

    const isSelected = (row) => selectedIds.has(String(row.dataset.rowId || ''));

    const syncRowVisual = (row) => {
      const selected = isSelected(row);
      row.classList.toggle('refund-row-selected', selected);
      row.setAttribute('aria-pressed', selected ? 'true' : 'false');
    };

    const selectedRows = () => rows().filter((row) => isSelected(row));

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

    const updateUi = () => {
      rows().forEach(syncRowVisual);

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
      const rowId = String(row.dataset.rowId || '');
      if (rowId === '') return;

      if (selectedIds.has(rowId)) {
        selectedIds.delete(rowId);
      } else {
        selectedIds.add(rowId);
      }

      updateUi();
    };

    hydrateInitialSelection();
    updateUi();

    document.addEventListener('click', (event) => {
      const target = event.target;
      if (!(target instanceof HTMLElement)) return;

      const row = target.closest('[data-refund-row="1"]');
      if (row && !target.closest('a, button, input, textarea, select, label')) {
        toggleRow(row);
        return;
      }

      if (target === openButton) {
        event.preventDefault();
        if (selectedRows().length <= 0) {
          return;
        }

        updateUi();
        modal?.show();
      }
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

    form.addEventListener('input', updateUi);
    form.addEventListener('change', updateUi);
  };

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})();
