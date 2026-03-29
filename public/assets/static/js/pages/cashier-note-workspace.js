(() => {
    const configEl = document.getElementById('transaction-workspace-config');
    const itemsRoot = document.getElementById('workspace-items');
    const emptyState = document.getElementById('workspace-empty-state');
    const grandTotalText = document.getElementById('workspace-grand-total-text');
    const paidNowText = document.getElementById('workspace-paid-now-text');
    const outstandingText = document.getElementById('workspace-outstanding-text');
    const paidInput = document.getElementById('inline_payment_amount_paid_rupiah');
    const decisionInputs = document.querySelectorAll('input[name="inline_payment[decision]"]');

    if (!configEl || !itemsRoot || !emptyState) {
        return;
    }

    const config = JSON.parse(configEl.textContent || '{}');
    const partSourceOptions = Array.isArray(config.partSourceOptions) ? config.partSourceOptions : [];
    const items = Array.isArray(config.oldItems) ? config.oldItems.map(normalizeItem) : [];

    function normalizeItem(item) {
        const service = item && typeof item.service === 'object' && item.service !== null ? item.service : {};
        const productLines = Array.isArray(item?.product_lines) ? item.product_lines : [{}];
        const externalLines = Array.isArray(item?.external_purchase_lines) ? item.external_purchase_lines : [{}];

        return {
            entry_mode: item?.entry_mode === 'product' ? 'product' : 'service',
            description: stringValue(item?.description),
            part_source: stringValue(item?.part_source || (item?.entry_mode === 'product' ? 'none' : 'none')),
            service: {
                name: stringValue(service.name),
                price_rupiah: stringValue(service.price_rupiah),
                notes: stringValue(service.notes),
            },
            product_lines: [
                {
                    product_id: stringValue(productLines[0]?.product_id),
                    qty: stringValue(productLines[0]?.qty || '1'),
                    unit_price_rupiah: stringValue(productLines[0]?.unit_price_rupiah),
                },
            ],
            external_purchase_lines: [
                {
                    label: stringValue(externalLines[0]?.label),
                    qty: stringValue(externalLines[0]?.qty || '1'),
                    unit_cost_rupiah: stringValue(externalLines[0]?.unit_cost_rupiah),
                },
            ],
        };
    }

    function stringValue(value) {
        return typeof value === 'string' || typeof value === 'number' ? String(value) : '';
    }

    function makeDefaultItem(entryMode, partSource) {
        return normalizeItem({
            entry_mode: entryMode,
            part_source: partSource,
            product_lines: [{}],
            external_purchase_lines: [{}],
            service: {},
        });
    }

    function formatRupiah(value) {
        const number = Number(value || 0);

        return new Intl.NumberFormat('id-ID').format(Number.isFinite(number) ? number : 0);
    }

    function parseNumber(value) {
        if (typeof value !== 'string') {
            return 0;
        }

        const cleaned = value.replace(/[^0-9]/g, '');

        return cleaned === '' ? 0 : Number.parseInt(cleaned, 10);
    }

    function itemTotal(item) {
        const servicePrice = parseNumber(item.service.price_rupiah);
        const productLine = item.product_lines[0] || {};
        const externalLine = item.external_purchase_lines[0] || {};

        const productQty = parseNumber(productLine.qty || '0');
        const productUnit = parseNumber(productLine.unit_price_rupiah || '0');

        const externalQty = parseNumber(externalLine.qty || '0');
        const externalUnit = parseNumber(externalLine.unit_cost_rupiah || '0');

        return servicePrice + (productQty * productUnit) + (externalQty * externalUnit);
    }

    function selectedDecision() {
        const checked = Array.from(decisionInputs).find((input) => input.checked);

        return checked ? checked.value : 'skip';
    }

    function updateSummary() {
        const grandTotal = items.reduce((total, item) => total + itemTotal(item), 0);
        const paidNow = selectedDecision() === 'skip' ? 0 : parseNumber(paidInput?.value || '0');
        const outstanding = Math.max(grandTotal - paidNow, 0);

        grandTotalText.textContent = formatRupiah(grandTotal);
        paidNowText.textContent = formatRupiah(paidNow);
        outstandingText.textContent = formatRupiah(outstanding);
    }

    function partSourceOptionsHtml(selected) {
        return partSourceOptions.map((option) => {
            const isSelected = option.value === selected ? 'selected' : '';

            return '<option value="' + option.value + '" ' + isSelected + '>' + option.label + '</option>';
        }).join('');
    }

    function render() {
        itemsRoot.innerHTML = '';

        if (items.length === 0) {
            emptyState.classList.remove('d-none');
        } else {
            emptyState.classList.add('d-none');
        }

        items.forEach((item, index) => {
            const wrapper = document.createElement('div');
            wrapper.className = 'border rounded p-3';
            wrapper.dataset.workspaceItemIndex = String(index);

            const isProduct = item.entry_mode === 'product';
            const usesStoreStock = item.part_source === 'store_stock';
            const usesExternalPurchase = item.part_source === 'external_purchase';
            const serviceSectionClass = isProduct ? 'd-none' : '';
            const partSourceSectionClass = isProduct ? 'd-none' : '';
            const productSectionClass = (isProduct || usesStoreStock) ? '' : 'd-none';
            const externalSectionClass = usesExternalPurchase ? '' : 'd-none';

            wrapper.innerHTML = [
                '<div class="d-flex justify-content-between align-items-center gap-2 mb-3">',
                '  <div>',
                '    <h6 class="mb-0">Item ' + (index + 1) + '</h6>',
                '    <small class="text-muted">' + (isProduct ? 'Penjualan produk.' : 'Servis dan sumber part.') + '</small>',
                '  </div>',
                '  <button type="button" class="btn btn-sm btn-light-danger" data-remove-workspace-item="' + index + '">Hapus</button>',
                '</div>',
                '<input type="hidden" name="items[' + index + '][entry_mode]" value="' + item.entry_mode + '">',
                '<div class="row g-3">',
                '  <div class="col-12">',
                '    <label class="form-label">Deskripsi</label>',
                '    <input type="text" class="form-control" name="items[' + index + '][description]" value="' + escapeHtml(item.description) + '" data-bind="' + index + '.description" placeholder="Catatan tambahan item">',
                '  </div>',
                '  <div class="col-12 col-lg-6 ' + partSourceSectionClass + '">',
                '    <label class="form-label">Sumber Part</label>',
                '    <select class="form-select" name="items[' + index + '][part_source]" data-bind="' + index + '.part_source">',
                partSourceOptionsHtml(item.part_source),
                '    </select>',
                '  </div>',
                '  <div class="col-12 ' + serviceSectionClass + '">',
                '    <div class="border rounded p-3">',
                '      <h6 class="mb-3">Servis</h6>',
                '      <div class="row g-3">',
                '        <div class="col-12 col-lg-6">',
                '          <label class="form-label">Nama Servis</label>',
                '          <input type="text" class="form-control" name="items[' + index + '][service][name]" value="' + escapeHtml(item.service.name) + '" data-bind="' + index + '.service.name" placeholder="Contoh: Ganti oli">',
                '        </div>',
                '        <div class="col-12 col-lg-6">',
                '          <label class="form-label">Harga Servis</label>',
                '          <input type="text" inputmode="numeric" class="form-control" name="items[' + index + '][service][price_rupiah]" value="' + escapeHtml(item.service.price_rupiah) + '" data-bind="' + index + '.service.price_rupiah" placeholder="Contoh: 50000">',
                '        </div>',
                '        <div class="col-12">',
                '          <label class="form-label">Catatan Servis</label>',
                '          <textarea class="form-control" rows="2" name="items[' + index + '][service][notes]" data-bind="' + index + '.service.notes" placeholder="Catatan servis">' + escapeHtml(item.service.notes) + '</textarea>',
                '        </div>',
                '      </div>',
                '    </div>',
                '  </div>',
                '  <div class="col-12 ' + productSectionClass + '">',
                '    <div class="border rounded p-3">',
                '      <h6 class="mb-3">Produk / Part Stok Toko</h6>',
                '      <div class="row g-3">',
                '        <div class="col-12 col-lg-4">',
                '          <label class="form-label">Product ID</label>',
                '          <input type="text" class="form-control" name="items[' + index + '][product_lines][0][product_id]" value="' + escapeHtml(item.product_lines[0].product_id) + '" data-bind="' + index + '.product_lines.0.product_id" placeholder="ID produk">',
                '        </div>',
                '        <div class="col-12 col-lg-4">',
                '          <label class="form-label">Qty</label>',
                '          <input type="text" inputmode="numeric" class="form-control" name="items[' + index + '][product_lines][0][qty]" value="' + escapeHtml(item.product_lines[0].qty) + '" data-bind="' + index + '.product_lines.0.qty" placeholder="1">',
                '        </div>',
                '        <div class="col-12 col-lg-4">',
                '          <label class="form-label">Harga Satuan</label>',
                '          <input type="text" inputmode="numeric" class="form-control" name="items[' + index + '][product_lines][0][unit_price_rupiah]" value="' + escapeHtml(item.product_lines[0].unit_price_rupiah) + '" data-bind="' + index + '.product_lines.0.unit_price_rupiah" placeholder="Contoh: 25000">',
                '        </div>',
                '      </div>',
                '    </div>',
                '  </div>',
                '  <div class="col-12 ' + externalSectionClass + '">',
                '    <div class="border rounded p-3">',
                '      <h6 class="mb-3">Pembelian Luar</h6>',
                '      <div class="row g-3">',
                '        <div class="col-12 col-lg-4">',
                '          <label class="form-label">Label</label>',
                '          <input type="text" class="form-control" name="items[' + index + '][external_purchase_lines][0][label]" value="' + escapeHtml(item.external_purchase_lines[0].label) + '" data-bind="' + index + '.external_purchase_lines.0.label" placeholder="Nama part luar">',
                '        </div>',
                '        <div class="col-12 col-lg-4">',
                '          <label class="form-label">Qty</label>',
                '          <input type="text" inputmode="numeric" class="form-control" name="items[' + index + '][external_purchase_lines][0][qty]" value="' + escapeHtml(item.external_purchase_lines[0].qty) + '" data-bind="' + index + '.external_purchase_lines.0.qty" placeholder="1">',
                '        </div>',
                '        <div class="col-12 col-lg-4">',
                '          <label class="form-label">Biaya Satuan</label>',
                '          <input type="text" inputmode="numeric" class="form-control" name="items[' + index + '][external_purchase_lines][0][unit_cost_rupiah]" value="' + escapeHtml(item.external_purchase_lines[0].unit_cost_rupiah) + '" data-bind="' + index + '.external_purchase_lines.0.unit_cost_rupiah" placeholder="Contoh: 30000">',
                '        </div>',
                '      </div>',
                '    </div>',
                '  </div>',
                '</div>',
            ].join('');

            itemsRoot.appendChild(wrapper);
        });

        updateSummary();
    }

    function escapeHtml(value) {
        return String(value)
            .replaceAll('&', '&amp;')
            .replaceAll('<', '&lt;')
            .replaceAll('>', '&gt;')
            .replaceAll('"', '&quot;')
            .replaceAll("'", '&#039;');
    }

    function setValue(item, path, value) {
        if (path === 'description') {
            item.description = value;
            return;
        }

        if (path === 'part_source') {
            item.part_source = value;
            return;
        }

        if (path === 'service.name') {
            item.service.name = value;
            return;
        }

        if (path === 'service.price_rupiah') {
            item.service.price_rupiah = value;
            return;
        }

        if (path === 'service.notes') {
            item.service.notes = value;
            return;
        }

        if (path === 'product_lines.0.product_id') {
            item.product_lines[0].product_id = value;
            return;
        }

        if (path === 'product_lines.0.qty') {
            item.product_lines[0].qty = value;
            return;
        }

        if (path === 'product_lines.0.unit_price_rupiah') {
            item.product_lines[0].unit_price_rupiah = value;
            return;
        }

        if (path === 'external_purchase_lines.0.label') {
            item.external_purchase_lines[0].label = value;
            return;
        }

        if (path === 'external_purchase_lines.0.qty') {
            item.external_purchase_lines[0].qty = value;
            return;
        }

        if (path === 'external_purchase_lines.0.unit_cost_rupiah') {
            item.external_purchase_lines[0].unit_cost_rupiah = value;
        }
    }

    document.addEventListener('click', (event) => {
        const addButton = event.target.closest('[data-add-workspace-item]');

        if (addButton) {
            items.push(
                makeDefaultItem(
                    addButton.dataset.entryMode || 'service',
                    addButton.dataset.partSource || 'none'
                )
            );
            render();
            return;
        }

        const removeButton = event.target.closest('[data-remove-workspace-item]');

        if (removeButton) {
            const index = Number(removeButton.dataset.removeWorkspaceItem || '-1');

            if (index >= 0) {
                items.splice(index, 1);
                render();
            }
        }
    });

    document.addEventListener('input', (event) => {
        const input = event.target.closest('[data-bind]');

        if (!input) {
            return;
        }

        const raw = input.getAttribute('data-bind') || '';
        const dotIndex = raw.indexOf('.');

        if (dotIndex < 0) {
            return;
        }

        const itemIndex = Number(raw.slice(0, dotIndex));
        const path = raw.slice(dotIndex + 1);

        if (!Number.isInteger(itemIndex) || !items[itemIndex]) {
            return;
        }

        setValue(items[itemIndex], path, input.value);
        updateSummary();

        if (path === 'part_source') {
            render();
        }
    });

    document.addEventListener('change', (event) => {
        const input = event.target.closest('[data-bind]');

        if (input) {
            const raw = input.getAttribute('data-bind') || '';
            const dotIndex = raw.indexOf('.');

            if (dotIndex >= 0) {
                const itemIndex = Number(raw.slice(0, dotIndex));
                const path = raw.slice(dotIndex + 1);

                if (Number.isInteger(itemIndex) && items[itemIndex]) {
                    setValue(items[itemIndex], path, input.value);

                    if (path === 'part_source') {
                        render();
                        return;
                    }

                    updateSummary();
                }
            }
        }

        if (event.target.matches('input[name="inline_payment[decision]"]')) {
            updateSummary();
        }
    });

    if (paidInput) {
        paidInput.addEventListener('input', updateSummary);
    }

    render();
})();
