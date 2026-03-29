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
    return `<div class="border rounded p-3 mb-3 bg-white" data-add-row>
      <div class="d-flex justify-content-between align-items-center gap-2 mb-3">
        <h6 class="mb-0">Baris Tambahan</h6>
        <button type="button" class="btn btn-sm btn-light-danger" data-remove-row>Hapus</button>
      </div>
      <div class="mb-3"><label class="form-label">Tipe Baris</label><select class="form-select" name="rows[${index}][line_type]" data-line-type><option value="service" ${type === 'service' ? 'selected' : ''}>Servis</option><option value="product" ${type === 'product' ? 'selected' : ''}>Produk</option></select></div>
      <div class="row g-3 ${type === 'service' ? '' : 'd-none'}" data-service-fields>
        <div class="col-md-8"><label class="form-label">Nama Servis</label><input type="text" class="form-control" name="rows[${index}][service_name]" value="${row.service_name || ''}"></div>
        <div class="col-md-4"><label class="form-label">Harga Servis</label><input type="number" min="1" class="form-control" name="rows[${index}][service_price_rupiah]" value="${row.service_price_rupiah || ''}"></div>
      </div>
      <div class="row g-3 ${type === 'product' ? '' : 'd-none'}" data-product-fields>
        <div class="col-md-8"><label class="form-label">Produk</label><select class="form-select" name="rows[${index}][product_id]">${optionsHtml(row.product_id || '')}</select></div>
        <div class="col-md-4"><label class="form-label">Qty</label><input type="number" min="1" class="form-control" name="rows[${index}][qty]" value="${row.qty || '1'}"></div>
      </div>
    </div>`;
  };

  const insertRow = (row, isPrepend = false) => {
    const html = rowHtml(rowIndex++, row);
    if (isPrepend) rowsRoot.insertAdjacentHTML('afterbegin', html);
    else rowsRoot.insertAdjacentHTML('beforeend', html);
  };

  rowsRoot.addEventListener('click', (e) => { if (e.target.matches('[data-remove-row]')) e.target.closest('[data-add-row]').remove(); });
  rowsRoot.addEventListener('change', (e) => {
    if (!e.target.matches('[data-line-type]')) return;
    const row = e.target.closest('[data-add-row]');
    row.querySelector('[data-service-fields]').classList.toggle('d-none', e.target.value !== 'service');
    row.querySelector('[data-product-fields]').classList.toggle('d-none', e.target.value !== 'product');
  });

  document.getElementById('detail-add-service-row')?.addEventListener('click', () => insertRow({ line_type: 'service' }, true));
  document.getElementById('detail-add-product-row')?.addEventListener('click', () => insertRow({ line_type: 'product' }, true));
  oldRows.forEach((row) => insertRow(row, false));
});
