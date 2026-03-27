<script>
document.addEventListener('DOMContentLoaded', function () {
    const products = @json($productOptions);
    const rowsRoot = document.getElementById('note-rows');
    const totalText = document.getElementById('grand-total-text');
    const initialRows = @json(array_values(old('rows', [['line_type' => 'service']])));

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
            <div class="card mt-3" data-note-row>
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start gap-2">
                        <div class="w-100">
                            <label class="form-label">Tipe Baris</label>
                            <select class="form-select" name="rows[${index}][line_type]" data-line-type>
                                <option value="service" ${type === 'service' ? 'selected' : ''}>Servis</option>
                                <option value="product" ${type === 'product' ? 'selected' : ''}>Produk</option>
                            </select>
                        </div>
                        <button type="button" class="btn btn-outline-danger mt-4" data-remove-row>Hapus</button>
                    </div>

                    <div class="row g-3 mt-1 ${serviceHidden}" data-service-fields>
                        <div class="col-md-6">
                            <label class="form-label">Nama Servis</label>
                            <input type="text" class="form-control" name="rows[${index}][service_name]" value="${row.service_name || ''}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Harga Servis</label>
                            <input type="number" min="1" class="form-control" name="rows[${index}][service_price_rupiah]" value="${row.service_price_rupiah || ''}" data-service-price>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Subtotal</label>
                            <input type="text" class="form-control" value="0" data-row-subtotal readonly>
                        </div>
                    </div>

                    <div class="row g-3 mt-1 ${productHidden}" data-product-fields>
                        <div class="col-md-6">
                            <label class="form-label">Produk</label>
                            <select class="form-select" name="rows[${index}][product_id]" data-product-id>${optionsHtml(row.product_id || '')}</select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Qty</label>
                            <input type="number" min="1" class="form-control" name="rows[${index}][qty]" value="${row.qty || ''}" data-product-qty>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Subtotal</label>
                            <input type="text" class="form-control" value="0" data-row-subtotal readonly>
                        </div>
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

            row.querySelectorAll('[data-row-subtotal]').forEach((input) => input.value = money(subtotal));
            total += subtotal;
        });

        totalText.textContent = money(total);
    };

    const appendRow = (row) => {
        rowsRoot.insertAdjacentHTML('beforeend', rowHtml(rowIndex, row));
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
    document.getElementById('add-service-row').addEventListener('click', () => appendRow({ line_type: 'service' }));
    document.getElementById('add-product-row').addEventListener('click', () => appendRow({ line_type: 'product' }));
    initialRows.forEach((row) => appendRow(row));
});
</script>
