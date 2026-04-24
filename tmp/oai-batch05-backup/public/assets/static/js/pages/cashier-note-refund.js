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

    const escapeHtml = (value) =>
      String(value ?? '')
        .replaceAll('&', '&amp;')
        .replaceAll('<', '&lt;')
        .replaceAll('>', '&gt;')
        .replaceAll('"', '&quot;')
        .replaceAll("'", '&#039;');

    const rows = () => Array.from(document.querySelectorAll('[data-refund-row="1"]'));
    const selectedIds = new Set();

    const isSelected = (row) => selectedIds.has(String(row.dataset.rowId || ''));
    const selectedRows = () => rows().filter((row) => isSelected(row));

    const parseRefundImpact = (row) => {
      try {
        const parsed = JSON.parse(row.dataset.refundImpact || '{}');
        return parsed && typeof parsed === 'object' ? parsed : {};
      } catch {
        return {};
      }
    };

    const refundableTotal = () =>
      selectedRows().reduce((sum, row) => sum + parseNumber(row.dataset.refundableRupiah), 0);

    const stockReturnCount = () =>
      selectedRows().reduce((sum, row) => {
        const impact = parseRefundImpact(row);
        return sum + parseNumber(impact.effect_summary?.stock_store_return_count);
      }, 0);

    const externalCount = () =>
      selectedRows().reduce((sum, row) => {
        const impact = parseRefundImpact(row);
        return sum + parseNumber(impact.effect_summary?.external_item_count);
      }, 0);

    const hasReason = () => String(reasonInput.value || '').trim() !== '';

    const selectedStoreReturns = () =>
      selectedRows().flatMap((row) => {
        const impact = parseRefundImpact(row);
        const items = Array.isArray(impact.store_returns) ? impact.store_returns : [];
        return items.map((item) => ({
          lineNo: row.dataset.lineNo || '-',
          lineLabel: row.dataset.lineLabel || '-',
          sourceLineId: String(item.source_line_id || ''),
          productId: String(item.product_id || ''),
          productLabel: String(item.product_label || item.product_id || '-'),
          qty: parseNumber(item.qty),
        }));
      });

    const selectedExternalReturns = () =>
      selectedRows().flatMap((row) => {
        const impact = parseRefundImpact(row);
        const items = Array.isArray(impact.external_returns) ? impact.external_returns : [];
        return items.map((item) => ({
          lineNo: row.dataset.lineNo || '-',
          lineLabel: row.dataset.lineLabel || '-',
          sourceLineId: String(item.source_line_id || ''),
          description: String(item.description || '-'),
          qty: parseNumber(item.qty),
          amountRupiah: parseNumber(item.amount_rupiah),
        }));
      });

    const buildHiddenInputs = () => {
      hiddenInputsContainer.innerHTML = selectedRows()
        .map((row) => `<input type="hidden" name="selected_row_ids[]" value="${escapeHtml(row.dataset.rowId)}">`)
        .join('');
    };

    const buildSelectedLinesSummary = () => {
      const items = selectedRows();

      if (items.length === 0) {
        selectedContainer.innerHTML = '<div class="small text-muted">Belum ada line dipilih.</div>';
        return;
      }

      selectedContainer.innerHTML = items.map((row) => {
        const impact = parseRefundImpact(row);
        const lineNo = row.dataset.lineNo || '-';
        const label = row.dataset.lineLabel || '-';
        const typeLabel = row.dataset.typeLabel || '-';
        const preview = row.dataset.previewLabel || '-';
        const refundable = format(parseNumber(impact.refund_amount_rupiah ?? row.dataset.refundableRupiah));
        const stockCount = parseNumber(impact.effect_summary?.stock_store_return_count);
        const externalCount = parseNumber(impact.effect_summary?.external_item_count);

        return `
          <div class="border rounded px-3 py-2">
            <div class="d-flex justify-content-between align-items-start gap-3">
              <div>
                <div class="fw-semibold">Line ${escapeHtml(lineNo)} · ${escapeHtml(label)}</div>
                <div class="small text-muted">${escapeHtml(typeLabel)}</div>
                <div class="small text-muted">${escapeHtml(preview)}</div>
                <div class="small text-muted">Stok toko kembali: ${format(stockCount)} · External dinetralkan: ${format(externalCount)}</div>
              </div>
              <strong>${refundable}</strong>
            </div>
          </div>
        `;
      }).join('');
    };

    const renderStoreReturns = () => {
      const container = document.getElementById('refund-modal-store-returns');
      if (!container) return;

      const items = selectedStoreReturns();

      if (items.length === 0) {
        container.innerHTML = '<div class="small text-muted">Tidak ada stok toko yang kembali.</div>';
        return;
      }

      container.innerHTML = items.map((item) => `
        <div class="border rounded px-3 py-2">
          <div class="d-flex justify-content-between align-items-start gap-3">
            <div>
              <div class="fw-semibold">${escapeHtml(item.productLabel)}</div>
              <div class="small text-muted">Line ${escapeHtml(item.lineNo)} · ${escapeHtml(item.lineLabel)}</div>
            </div>
            <strong>+${format(item.qty)}</strong>
          </div>
        </div>
      `).join('');
    };

    const renderExternalReturns = () => {
      const container = document.getElementById('refund-modal-external-returns');
      if (!container) return;

      const items = selectedExternalReturns();

      if (items.length === 0) {
        container.innerHTML = '<div class="small text-muted">Tidak ada komponen external yang dinetralkan.</div>';
        return;
      }

      container.innerHTML = items.map((item) => `
        <div class="border rounded px-3 py-2">
          <div class="d-flex justify-content-between align-items-start gap-3">
            <div>
              <div class="fw-semibold">${escapeHtml(item.description)}</div>
              <div class="small text-muted">Line ${escapeHtml(item.lineNo)} · ${escapeHtml(item.lineLabel)}</div>
            </div>
            <div class="text-end">
              <strong>${format(item.amountRupiah)}</strong>
              <div class="small text-muted">Qty ${format(item.qty)}</div>
            </div>
          </div>
        </div>
      `).join('');
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
          ? `Refund akan dicatat otomatis sebesar ${format(refundableTotal())}. Line terpilih menjadi netral setelah refund.`
          : 'Refund akan dicatat untuk line yang dipilih sesuai total refundable saat ini.';
      }

      buildHiddenInputs();
      buildSelectedLinesSummary();
      renderStoreReturns();
      renderExternalReturns();
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
