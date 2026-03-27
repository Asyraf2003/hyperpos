document.addEventListener('DOMContentLoaded', () => {
  const cfg = JSON.parse(document.getElementById('note-create-config').textContent);
  const rowsRoot = document.getElementById('note-rows');
  const totalText = document.getElementById('grand-total-text');
  const products = cfg.productOptions || [];
  const oldRows = (cfg.oldRows && cfg.oldRows.length > 0) ? cfg.oldRows : [{ line_type: 'service' }];
  let rowIndex = 0;

  const money = (v) => new Intl.NumberFormat('id-ID').format(Number(v || 0));
  const intVal = (v) => Number.parseInt(v || '0', 10) || 0;
  const productPrice = (id) => (products.find((p) => p.id === id) || {}).price_rupiah || 0;
  const optionsHtml = (selected) => ['<option value="">Pilih produk</option>']
    .concat(products.map((p) => `<option value="${p.id}" ${p.id === selected ? 'selected' : ''}>${p.label}</option>`))
    .join('');

  const rowHtml = (index, row) => {
    const type = row.line_type === 'product' ? 'product' : 'service';
    return `<div class="card mt-3" data-row>
      <div class="card-body">
        <div class="d-flex justify-content-between gap-2">
          <div class="w-100">
            <label class="form-label">Tipe Baris</label>
            <select class="form-select" name="rows[${index}][line_type]" data-line-type>
              <option value="service" ${type === 'service' ? 'selected' : ''}>Servis</option>
              <option value="product" ${type === 'product' ? 'selected' : ''}>Produk</option>
            </select>
          </div>
          <button type="button" class="btn btn-outline-danger mt-4" data-remove-row>Hapus</button>
        </div>
        <div class="row g-3 mt-1 ${type === 'service' ? '' : 'd-none'}" data-service-fields>
          <div class="col-md-6"><label class="form-label">Nama Servis</label><input type="text" class="form-control" name="rows[${index}][service_name]" value="${row.service_name || ''}"></div>
          <div class="col-md-3"><label class="form-label">Harga Servis</label><input type="number" min="1" class="form-control" name="rows[${index}][service_price_rupiah]" value="${row.service_price_rupiah || ''}" data-service-price></div>
          <div class="col-md-3"><label class="form-label">Subtotal</label><input type="text" class="form-control" value="0" data-subtotal readonly></div>
        </div>
        <div class="row g-3 mt-1 ${type === 'product' ? '' : 'd-none'}" data-product-fields>
          <div class="col-md-6"><label class="form-label">Produk</label><select class="form-select" name="rows[${index}][product_id]" data-product-id>${optionsHtml(row.product_id || '')}</select></div>
          <div class="col-md-3"><label class="form-label">Qty</label><input type="number" min="1" class="form-control" name="rows[${index}][qty]" value="${row.qty || ''}" data-product-qty></div>
          <div class="col-md-3"><label class="form-label">Subtotal</label><input type="text" class="form-control" value="0" data-subtotal readonly></div>
        </div>
      </div>
    </div>`;
  };

  const recalc = () => {
    let total = 0;
    rowsRoot.querySelectorAll('[data-row]').forEach((row) => {
      const type = row.querySelector('[data-line-type]').value;
      const subtotal = type === 'service'
        ? intVal(row.querySelector('[data-service-price]').value)
        : productPrice(row.querySelector('[data-product-id]').value) * intVal(row.querySelector('[data-product-qty]').value);
      row.querySelectorAll('[data-subtotal]').forEach((el) => { el.value = money(subtotal); });
      total += subtotal;
    });
    totalText.textContent = money(total);
  };

  const appendRow = (row) => { rowsRoot.insertAdjacentHTML('beforeend', rowHtml(rowIndex++, row)); recalc(); };
  rowsRoot.addEventListener('click', (e) => { if (e.target.matches('[data-remove-row]')) { e.target.closest('[data-row]').remove(); recalc(); } });
  rowsRoot.addEventListener('change', (e) => {
    if (e.target.matches('[data-line-type]')) {
      const row = e.target.closest('[data-row]');
      row.querySelector('[data-service-fields]').classList.toggle('d-none', e.target.value !== 'service');
      row.querySelector('[data-product-fields]').classList.toggle('d-none', e.target.value !== 'product');
    }
    recalc();
  });
  rowsRoot.addEventListener('input', recalc);
  document.getElementById('add-service-row').addEventListener('click', () => appendRow({ line_type: 'service' }));
  document.getElementById('add-product-row').addEventListener('click', () => appendRow({ line_type: 'product' }));
  oldRows.forEach((row) => appendRow(row));
});
