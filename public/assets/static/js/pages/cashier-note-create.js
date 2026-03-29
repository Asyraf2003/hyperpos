document.addEventListener('DOMContentLoaded', () => {
    const cfgNode = document.getElementById('note-create-config');
    if (!cfgNode) return;

    const cfg = JSON.parse(cfgNode.textContent);
    const rowsRoot = document.getElementById('note-rows');
    const totalText = document.getElementById('grand-total-text');
    const products = cfg.productOptions || [];
    const initialRows = (cfg.oldRows && cfg.oldRows.length > 0) ? cfg.oldRows : [{ line_type: 'service' }];

    let rowIndex = 0;

    const money = (value) => new Intl.NumberFormat('id-ID').format(Number(value || 0));
    const intVal = (value) => Number.parseInt(value || '0', 10) || 0;
    const productPrice = (id) => (products.find((item) => item.id === id) || {}).price_rupiah || 0;
    const optionsHtml = (selectedId) => ['<option value="">Pilih produk</option>']
        .concat(products.map((item) => `<option value="${item.id}" ${item.id === selectedId ? 'selected' : ''}>${item.label}</option>`))
        .join('');

    const rowHtml = (index, row) => {
        const type = row.line_type === 'product' ? 'product' : 'service';
        const serviceHidden = type === 'service' ? '' : 'd-none';
        const productHidden = type === 'product' ? '' : 'd-none';

        return `
            <div class="border rounded p-3 mb-3 bg-white" data-note-row>
                <div class="d-flex justify-content-between align-items-center gap-2 mb-3">
                    <div>
                        <h6 class="mb-0">Baris Rincian</h6>
                    </div>
                    <button type="button" class="btn btn-sm btn-light-danger" data-remove-row>Hapus</button>
                </div>

                <div class="row">
                    <div class="col-12 mb-3">
                        <label class="form-label">Tipe Baris</label>
                        <select class="form-select" name="rows[${index}][line_type]" data-line-type>
                            <option value="service" ${type === 'service' ? 'selected' : ''}>Servis</option>
                            <option value="product" ${type === 'product' ? 'selected' : ''}>Produk</option>
                        </select>
                    </div>
                </div>

                <div class="row g-3 ${serviceHidden}" data-service-fields>
                    <div class="col-md-8">
                        <label class="form-label">Nama Servis</label>
                        <input type="text" class="form-control" name="rows[${index}][service_name]" value="${row.service_name || ''}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Harga Servis</label>
                        <input type="number" min="1" class="form-control" name="rows[${index}][service_price_rupiah]" value="${row.service_price_rupiah || ''}" data-service-price>
                    </div>
                </div>

                <div class="row g-3 ${productHidden}" data-product-fields>
                    <div class="col-md-8">
                        <label class="form-label">Produk</label>
                        <select class="form-select" name="rows[${index}][product_id]" data-product-id>${optionsHtml(row.product_id || '')}</select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Qty</label>
                        <input type="number" min="1" class="form-control" name="rows[${index}][qty]" value="${row.qty || '1'}" data-product-qty>
                    </div>
                </div>

                <div class="row mt-3">
                    <div class="col-12 text-end">
                        <div class="text-muted small">Subtotal</div>
                        <div class="fw-bold fs-5 text-primary"><span data-row-subtotal>0</span></div>
                    </div>
                </div>
            </div>`;
    };

    const recalc = () => {
        let total = 0;

        rowsRoot.querySelectorAll('[data-note-row]').forEach((row) => {
            const type = row.querySelector('[data-line-type]').value;
            let subtotal = 0;

            if (type === 'service') {
                subtotal = intVal(row.querySelector('[data-service-price]').value);
            } else {
                subtotal = productPrice(row.querySelector('[data-product-id]').value) * intVal(row.querySelector('[data-product-qty]').value);
            }

            row.querySelector('[data-row-subtotal]').textContent = money(subtotal);
            total += subtotal;
        });

        totalText.textContent = money(total);
    };

    const insertRow = (row, isPrepend = false) => {
        const html = rowHtml(rowIndex, row);
        if (isPrepend) {
            rowsRoot.insertAdjacentHTML('afterbegin', html);
        } else {
            rowsRoot.insertAdjacentHTML('beforeend', html);
        }
        rowIndex++;
        recalc();
    };

    rowsRoot.addEventListener('click', (event) => {
        if (event.target.matches('[data-remove-row]')) {
            event.target.closest('[data-note-row]').remove();
            recalc();
        }
    });

    rowsRoot.addEventListener('change', (event) => {
        if (event.target.matches('[data-line-type]')) {
            const row = event.target.closest('[data-note-row]');
            row.querySelector('[data-service-fields]').classList.toggle('d-none', event.target.value !== 'service');
            row.querySelector('[data-product-fields]').classList.toggle('d-none', event.target.value !== 'product');
        }
        recalc();
    });

    rowsRoot.addEventListener('input', recalc);
    
    document.getElementById('add-service-row')?.addEventListener('click', () => insertRow({ line_type: 'service' }, true));
    document.getElementById('add-product-row')?.addEventListener('click', () => insertRow({ line_type: 'product' }, true));
    
    initialRows.forEach((row) => insertRow(row, false));
});
