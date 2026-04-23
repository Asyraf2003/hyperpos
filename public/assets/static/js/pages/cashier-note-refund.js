(() => {
  const init = () => {
    const modalEl = document.getElementById('note-refund-modal');
    const form = document.getElementById('note-refund-form');
    const openButton = document.getElementById('note-refund-open-button');
    const selectedContainer = document.getElementById('note-refund-selected-lines');
    const hiddenInputsContainer = document.getElementById('note-refund-hidden-selected-rows');
    const amountInput = document.getElementById('refund_amount_rupiah');
    const reasonInput = document.getElementById('note-refund-reason');
    const submitButton = document.getElementById('note-refund-submit');

    if (!modalEl || !form || !openButton || !selectedContainer || !hiddenInputsContainer || !amountInput || !reasonInput) {
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

    const isSelected = (row) => selectedIds.has(String(row.dataset.rowId || ''));
    const selectedRows = () => rows().filter((row) => isSelected(row));

    const refundableTotal = () =>
      selectedRows().reduce((sum, row) => sum + parseNumber(row.dataset.refundableRupiah), 0);

    const stockReturnCount = () =>
      selectedRows().reduce((sum, row) => sum + parseNumber(row.dataset.storeReturnCount), 0);

    const externalCount = () =>
      selectedRows().reduce((sum, row) => sum + parseNumber(row.dataset.externalCount), 0);

    const hasReason = () => String(reasonInput.value || '').trim() !== '';

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
        const stockCount = parseNumber(row.dataset.storeReturnCount);
        const externalCount = parseNumber(row.dataset.externalCount);

        return `
          <div class="border rounded px-3 py-2">
            <div class="d-flex justify-content-between align-items-start gap-3">
              <div>
                <div class="fw-semibold">Line ${lineNo} · ${label}</div>
                <div class="small text-muted">${typeLabel}</div>
                <div class="small text-muted">${preview}</div>
                <div class="small text-muted">Stok toko kembali: ${format(stockCount)} · External disederhanakan: ${format(externalCount)}</div>
              </div>
              <strong>${refundable}</strong>
            </div>
          </div>
        `;
      }).join('');
    };

    const syncVisual = () => {
      rows().forEach((row) => {
        const selected = isSelected(row);
        row.classList.toggle('refund-row-selected', selected);
        row.setAttribute('aria-pressed', selected ? 'true' : 'false');
      });
    };

    const syncAmount = () => {
      amountInput.value = String(refundableTotal());
    };

    const syncButton = () => {
      const hasSelection = selectedRows().length > 0;
      openButton.disabled = !hasSelection;
      openButton.classList.toggle('opacity-50', !hasSelection);
      openButton.classList.toggle('disabled', !hasSelection);
      openButton.style.pointerEvents = hasSelection ? 'auto' : 'none';
      openButton.setAttribute('aria-disabled', hasSelection ? 'false' : 'true');

      if (submitButton) {
        submitButton.disabled = !hasSelection || refundableTotal() <= 0 || !hasReason();
      }
    };

    const syncSummary = () => {
      const countNode = document.getElementById('refund-modal-selected-count');
      const totalNode = document.getElementById('refund-modal-selected-total');
      const stockNode = document.getElementById('refund-modal-stock-return-count');
      const externalNode = document.getElementById('refund-modal-external-count');
      const impactNode = document.getElementById('refund-modal-impact-note');

      if (countNode) countNode.textContent = format(selectedRows().length);
      if (totalNode) totalNode.textContent = format(refundableTotal());
      if (stockNode) stockNode.textContent = format(stockReturnCount());
      if (externalNode) externalNode.textContent = format(externalCount());

      if (impactNode) {
        impactNode.textContent = selectedRows().length > 0
          ? `Refund akan dicatat otomatis sebesar ${format(refundableTotal())} untuk line yang dipilih.`
          : 'Refund akan dicatat untuk line yang dipilih sesuai total refundable saat ini.';
      }

      buildHiddenInputs();
      buildSelectedLinesSummary();
    };

    const syncAll = () => {
      syncVisual();
      syncAmount();
      syncButton();
      syncSummary();
    };

    const toggleRow = (row) => {
      const rowId = String(row.dataset.rowId || '');
      if (rowId === '') return;

      if (selectedIds.has(rowId)) {
        selectedIds.delete(rowId);
      } else {
        selectedIds.add(rowId);
      }

      syncAll();
    };

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

        syncAll();
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

    modalEl.addEventListener('shown.bs.modal', () => {
      reasonInput.focus();
      reasonInput.select();
    });

    reasonInput.addEventListener('keydown', (event) => {
      if (event.key !== 'Enter') {
        return;
      }

      event.preventDefault();

      if (submitButton.disabled) {
        return;
      }

      form.requestSubmit();
    });

    form.addEventListener('input', syncAll);
    form.addEventListener('change', syncAll);

    selectedIds.clear();
    syncAll();
  };

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})();
