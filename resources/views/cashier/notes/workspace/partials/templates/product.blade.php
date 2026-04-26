<template id="workspace-template-product">
    <div class="border rounded p-3 mb-3" data-line-item data-item-type="product">
        <div class="d-flex justify-content-between align-items-center gap-2 mb-3">
            <div>
                <h6 class="mb-0" data-line-title>Rincian</h6>
                <small class="text-muted">Produk dari stok toko.</small>
            </div>
            <button type="button" class="btn btn-sm btn-light-danger" data-remove-line>Hapus</button>
        </div>

        <input type="hidden" name="items[__INDEX__][entry_mode]" value="product">
        <input type="hidden" name="items[__INDEX__][part_source]" value="none">
        <input type="hidden" name="items[__INDEX__][pay_now]" value="0" data-pay-now>

        <div class="row">
            <div class="col-12">
                <div class="form-group mb-3 position-relative">
                    <label class="form-label">Produk</label>
                    <input type="hidden" name="items[__INDEX__][product_lines][0][product_id]" value="" data-product-id>
                    <input type="hidden" name="items[__INDEX__][product_lines][0][price_basis]" value="current_catalog" data-price-basis>
                    <input type="text" class="form-control" placeholder="Ketik minimal 2 huruf untuk mencari produk" autocomplete="off" data-product-search>
                    <div class="list-group position-absolute w-100 shadow-sm d-none" style="z-index: 20;" data-product-results></div>
                </div>
            </div>

            <div class="col-12 col-lg-4">
                <div class="form-group mb-3">
                    <label class="form-label">Qty</label>
                    <input type="text" inputmode="numeric" name="items[__INDEX__][product_lines][0][qty]" value="1" class="form-control" data-qty-input>
                    <large class="text-muted d-block mt-1" data-stock-text>Stok tersedia: -</large>
                    <large class="text-danger d-none" data-stock-error>Qty melebihi stok tersedia.</large>
                </div>
            </div>

            <div class="col-12 col-lg-8">
                <div class="form-group mb-3" data-money-input-group>
                    <label class="form-label">Harga Jual (Rupiah)</label>
                    <input type="hidden" name="items[__INDEX__][product_lines][0][unit_price_rupiah]" value="" data-money-raw>
                    <input type="text" inputmode="numeric" value="" class="form-control" placeholder="Contoh: 150.000" data-money-display data-price-input>
                    <large class="text-muted d-block mt-1" data-min-price-text>Harga minimum: -</large>
                    <large class="text-danger d-none" data-min-price-warning>Harga tidak boleh di bawah minimum.</large>
                </div>
            </div>

            <div class="col-12">
                <div class="form-group mb-0">
                    <label class="form-label">Catatan</label>
                    <textarea name="items[__INDEX__][description]" rows="2" class="form-control" placeholder="Catatan tambahan produk"></textarea>
                </div>
            </div>
        </div>
    </div>
</template>
