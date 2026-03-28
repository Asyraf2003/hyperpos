document.addEventListener('DOMContentLoaded', () => {
  const cfgNode = document.getElementById('note-add-rows-config');
  if (!cfgNode) return;

  const cfg = JSON.parse(cfgNode.textContent);
  const rowsRoot = document.getElementById('detail-note-rows');
  const products = cfg.productOptions || [];
  const oldRows = (cfg.oldRows && cfg.oldRows.length > 0) ? cfg.oldRows : [{ line_type: 'service' }];
  let rowIndex = 0;

  const optionsHtml = (selected) => ['<option value="">Pilih produk</option>']
    .concat(products.map((p) => `<option value="${p.id}" ${p.id === selected ? 'selected' : ''}>${p.label}</option>`))
    .join('');

  const rowHtml = (index, row) => {
    const type = row.line_type === 'product' ? 'product' : 'service';
    return `<div class="card mt-3" data-add-row><div class="card-body">
      <div class="d-flex justify-content-between gap-2">
        <div class="w-100"><label class="form-label">Tipe Baris</label><select class="form-select" name="rows[${index}][line_type]" data-line-type><option value="service" ${type === 'service' ? 'selected' : ''}>Servis</option><option value="product" ${type === 'product' ? 'selected' : ''}>Produk</option></select></div>
        <button type="button" class="btn btn-outline-danger mt-4" data-remove-row>Hapus</button>
      </div>
      <div class="row g-3 mt-1 ${type === 'service' ? '' : 'd-none'}" data-service-fields>
        <div class="col-md-8"><label class="form-label">Nama Servis</label><input type="text" class="form-control" name="rows[${index}][service_name]" value="${row.service_name || ''}"></div>
        <div class="col-md-4"><label class="form-label">Harga Servis</label><input type="number" min="1" class="form-control" name="rows[${index}][service_price_rupiah]" value="${row.service_price_rupiah || ''}"></div>
      </div>
      <div class="row g-3 mt-1 ${type === 'product' ? '' : 'd-none'}" data-product-fields>
        <div class="col-md-8"><label class="form-label">Produk</label><select class="form-select" name="rows[${index}][product_id]">${optionsHtml(row.product_id || '')}</select></div>
        <div class="col-md-4"><label class="form-label">Qty</label><input type="number" min="1" class="form-control" name="rows[${index}][qty]" value="${row.qty || ''}"></div>
      </div>
    </div></div>`;
  };

  const appendRow = (row) => rowsRoot.insertAdjacentHTML('beforeend', rowHtml(rowIndex++, row));

  rowsRoot.addEventListener('click', (e) => { if (e.target.matches('[data-remove-row]')) e.target.closest('[data-add-row]').remove(); });
  rowsRoot.addEventListener('change', (e) => {
    if (!e.target.matches('[data-line-type]')) return;
    const row = e.target.closest('[data-add-row]');
    row.querySelector('[data-service-fields]').classList.toggle('d-none', e.target.value !== 'service');
    row.querySelector('[data-product-fields]').classList.toggle('d-none', e.target.value !== 'product');
  });

  document.getElementById('detail-add-service-row')?.addEventListener('click', () => appendRow({ line_type: 'service' }));
  document.getElementById('detail-add-product-row')?.addEventListener('click', () => appendRow({ line_type: 'product' }));
  oldRows.forEach((row) => appendRow(row));
});
