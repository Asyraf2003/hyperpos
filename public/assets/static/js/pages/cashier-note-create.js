document.addEventListener('DOMContentLoaded', () => {
    const cfgNode = document.getElementById('note-create-config');
    const rowsRoot = document.getElementById('note-rows');
    const totalText = document.getElementById('grand-total-text');

    if (!cfgNode || !rowsRoot || !totalText) {
        return;
    }

    let cfg = {};

    try {
        cfg = JSON.parse(cfgNode.textContent || '{}');
    } catch (_error) {
        cfg = {};
    }

    const products = Array.isArray(cfg.productOptions) ? cfg.productOptions : [];
    const initialRows = Array.isArray(cfg.oldRows) && cfg.oldRows.length > 0
        ? cfg.oldRows
        : [{ line_type: 'service' }];

    let rowIndex = 0;

    const money = (value) => new Intl.NumberFormat('id-ID').format(Number(value || 0));
    const rupiah = (value) => `Rp ${money(value)}`;
    const intVal = (value) => Number.parseInt(String(value || '0'), 10) || 0;

    const escapeHtml = (value) => String(value ?? '')
        .replaceAll('&', '&amp;')
        .replaceAll('<', '&lt;')
        .replaceAll('>', '&gt;')
        .replaceAll('"', '&quot;')
        .replaceAll("'", '&#039;');

    const findProduct = (id) => products.find((item) => item.id === id) || null;
    const productPrice = (id) => findProduct(id)?.price_rupiah || 0;

    const optionsHtml = (selectedId) => ['<option value="">Pilih produk</option>']
        .concat(
            products.map((item) => `
                <option value="${escapeHtml(item.id)}" ${item.id === selectedId ? 'selected' : ''}>
                    ${escapeHtml(item.label)}
                </option>
            `),
        )
        .join('');

    const rowHtml = (index, row) => {
        const type = row.line_type === 'product' ? 'product' : 'service';
        const serviceHidden = type === 'service' ? '' : 'd-none';
        const productHidden = type === 'product' ? '' : 'd-none';
        const selectedProduct = findProduct(row.product_id || '');
        const productPriceLabel = selectedProduct
            ? `Harga master: ${rupiah(selectedProduct.price_rupiah)}`
            : 'Harga akan mengikuti master produk';

        return `
            <div class="card shadow-sm border mb-3" data-note-row>
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start gap-3 mb-3">
                        <div>
                            <div class="small text-muted" data-row-seq>Baris</div>
                            <h6 class="mb-1" data-row-title>${type === 'product' ? 'Rincian Produk' : 'Rincian Servis'}</h6>
                            <span class="badge ${type === 'product' ? 'bg-secondary' : 'bg-primary'}" data-row-type-badge>
                                ${type === 'product' ? 'Produk' : 'Servis'}
                            </span>
                        </div>

                        <button type="button" class="btn btn-sm btn-light-danger" data-remove-row>
                            <i class="bi bi-trash me-1"></i> Hapus
                        </button>
                    </div>

                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label">Tipe Baris</label>
                            <select class="form-select" name="rows[${index}][line_type]" data-line-type>
                                <option value="service" ${type === 'service' ? 'selected' : ''}>Servis</option>
                                <option value="product" ${type === 'product' ? 'selected' : ''}>Produk</option>
                            </select>
                        </div>
                    </div>

                    <div class="row g-3 mt-1 ${serviceHidden}" data-service-fields>
                        <div class="col-md-8">
                            <label class="form-label">Nama Servis</label>
                            <input
                                type="text"
                                class="form-control"
                                name="rows[${index}][service_name]"
                                value="${escapeHtml(row.service_name || '')}"
                                placeholder="Contoh: Servis ringan"
                            >
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Harga Servis</label>
                            <input
                                type="number"
                                min="1"
                                class="form-control"
                                name="rows[${index}][service_price_rupiah]"
                                value="${escapeHtml(row.service_price_rupiah || '')}"
                                placeholder="Contoh: 150000"
                                data-service-price
                            >
                        </div>

                        <div class="col-12">
                            <label class="form-label">Keterangan Servis <span class="text-muted">(Opsional)</span></label>
                            <textarea
                                class="form-control"
                                name="rows[${index}][service_notes]"
                                rows="2"
                                placeholder="Contoh: Ganti oli, cek rem, bersihkan karburator"
                            >${escapeHtml(row.service_notes || '')}</textarea>
                        </div>
                    </div>

                    <div class="row g-3 mt-1 ${productHidden}" data-product-fields>
                        <div class="col-md-8">
                            <label class="form-label">Produk</label>
                            <select class="form-select" name="rows[${index}][product_id]" data-product-id>
                                ${optionsHtml(row.product_id || '')}
                            </select>
                            <div class="form-text" data-product-price-label>${escapeHtml(productPriceLabel)}</div>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Qty</label>
                            <input
                                type="number"
                                min="1"
                                class="form-control"
                                name="rows[${index}][qty]"
                                value="${escapeHtml(row.qty || '1')}"
                                data-product-qty
                            >
                        </div>
                    </div>

                    <div class="border-top pt-3 mt-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="small text-muted">Subtotal Baris</div>
                            <div class="fw-bold fs-5 text-primary" data-row-subtotal>${rupiah(0)}</div>
                        </div>
                    </div>
                </div>
            </div>
        `;
    };

    const renumberRows = () => {
        rowsRoot.querySelectorAll('[data-note-row]').forEach((row, idx) => {
            const seq = row.querySelector('[data-row-seq]');
            if (seq) {
                seq.textContent = `Baris ${idx + 1}`;
            }
        });
    };

    const syncRowPresentation = (row) => {
        const type = row.querySelector('[data-line-type]')?.value === 'product' ? 'product' : 'service';
        const serviceFields = row.querySelector('[data-service-fields]');
        const productFields = row.querySelector('[data-product-fields]');
        const title = row.querySelector('[data-row-title]');
        const badge = row.querySelector('[data-row-type-badge]');
        const productPriceLabel = row.querySelector('[data-product-price-label]');
        const productId = row.querySelector('[data-product-id]')?.value || '';
        const product = findProduct(productId);

        serviceFields?.classList.toggle('d-none', type !== 'service');
        productFields?.classList.toggle('d-none', type !== 'product');

        if (title) {
            title.textContent = type === 'product' ? 'Rincian Produk' : 'Rincian Servis';
        }

        if (badge) {
            badge.textContent = type === 'product' ? 'Produk' : 'Servis';
            badge.className = `badge ${type === 'product' ? 'bg-secondary' : 'bg-primary'}`;
        }

        if (productPriceLabel) {
            productPriceLabel.textContent = product
                ? `Harga master: ${rupiah(product.price_rupiah)}`
                : 'Harga akan mengikuti master produk';
        }
    };

    const recalc = () => {
        let total = 0;

        rowsRoot.querySelectorAll('[data-note-row]').forEach((row) => {
            syncRowPresentation(row);

            const type = row.querySelector('[data-line-type]')?.value === 'product' ? 'product' : 'service';
            let subtotal = 0;

            if (type === 'service') {
                subtotal = intVal(row.querySelector('[data-service-price]')?.value);
            } else {
                subtotal = productPrice(row.querySelector('[data-product-id]')?.value || '')
                    * intVal(row.querySelector('[data-product-qty]')?.value);
            }

            const subtotalNode = row.querySelector('[data-row-subtotal]');
            if (subtotalNode) {
                subtotalNode.textContent = rupiah(subtotal);
            }

            total += subtotal;
        });

        totalText.textContent = rupiah(total);
    };

    const insertRow = (row, isPrepend = false, focusTarget = null) => {
        const html = rowHtml(rowIndex, row);

        if (isPrepend) {
            rowsRoot.insertAdjacentHTML('afterbegin', html);
        } else {
            rowsRoot.insertAdjacentHTML('beforeend', html);
        }

        rowIndex += 1;

        const insertedRow = isPrepend
            ? rowsRoot.querySelector('[data-note-row]')
            : rowsRoot.querySelector('[data-note-row]:last-child');

        renumberRows();
        recalc();

        if (!insertedRow) {
            return;
        }

        if (focusTarget === 'service') {
            insertedRow.querySelector('input[name*="[service_name]"]')?.focus();
        }

        if (focusTarget === 'product') {
            insertedRow.querySelector('[data-product-id]')?.focus();
        }
    };

    rowsRoot.addEventListener('click', (event) => {
        const target = event.target.closest('[data-remove-row]');

        if (!target) {
            return;
        }

        target.closest('[data-note-row]')?.remove();
        renumberRows();
        recalc();
    });

    rowsRoot.addEventListener('change', (event) => {
        const row = event.target.closest('[data-note-row]');

        if (!row) {
            return;
        }

        recalc();
    });

    rowsRoot.addEventListener('input', () => {
        recalc();
    });

    document.getElementById('add-service-row')?.addEventListener('click', () => {
        insertRow({ line_type: 'service' }, true, 'service');
    });

    document.getElementById('add-product-row')?.addEventListener('click', () => {
        insertRow({ line_type: 'product', qty: '1' }, true, 'product');
    });

    initialRows.forEach((row) => insertRow(row, false));
});
