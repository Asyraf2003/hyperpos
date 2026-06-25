<template id="workspace-template-service_store_stock">
    <div class="workspace-answer-card" data-line-item data-item-type="service_store_stock">
        <div class="workspace-answer-header">
            <div>
                <h6 class="mb-0 small fw-semibold" data-line-title>Rincian</h6>
                <small class="text-muted">Cari paket servis. Produk dan jasa terisi otomatis dari template.</small>
            </div>
            <button type="button" class="btn btn-sm btn-outline-danger py-1" data-remove-line>Hapus</button>
        </div>

        <input type="hidden" name="items[__INDEX__][entry_mode]" value="service">
        <input type="hidden" name="items[__INDEX__][part_source]" value="store_stock">
        <input type="hidden" name="items[__INDEX__][pay_now]" value="0" data-pay-now>
        <input type="hidden" name="items[__INDEX__][pricing_mode]" value="package_auto_split" data-pricing-mode>
        <input type="hidden" name="items[__INDEX__][requires_service_product_template]" value="1" data-requires-service-product-template>
        <input type="hidden" name="items[__INDEX__][service][notes]" value="">
        <input type="hidden" value="" data-service-catalog-id>
        <input type="hidden" value="" data-service-default-fee-rupiah>

        <input
            type="hidden"
            name="items[__INDEX__][service][name]"
            value=""
            data-service-name
            data-template-service-name
        >
        <input
            type="hidden"
            name="items[__INDEX__][service][price_rupiah]"
            value="0"
            data-money-raw
            data-service-price-raw
        >
        <input type="hidden" value="" data-service-price-display>

        <div class="workspace-answer-field">
            <label class="form-label small mb-1">Paket Service x Product <span class="text-danger">*</span></label>
            <div class="position-relative">
                <input
                    type="text"
                    class="form-control form-control-sm"
                    placeholder="Cari paket, service, atau produk"
                    autocomplete="off"
                    data-package-search
                >
                <div class="list-group position-absolute w-100 shadow-sm d-none" style="z-index: 20;" data-package-results></div>
            </div>
            <small class="text-muted d-block mt-1">
                Pilih paket. Service wajib, Produk 1 wajib, Produk 2 dan 3 opsional dari template.
            </small>
            <small class="text-danger d-none" data-package-error>Paket wajib dipilih.</small>
        </div>

        <div class="rounded border bg-light p-2 d-none" data-package-selected-section>
            <div class="small text-muted">Paket terpilih</div>
            <div class="fw-semibold small" data-package-title>-</div>
            <div class="small text-muted mt-1" data-package-description>-</div>
            <div class="small text-muted mt-1" data-package-stock-text>-</div>
        </div>

        <div class="d-none" data-product-lines>
            <div data-product-line>
                <input type="hidden" name="items[__INDEX__][product_lines][0][product_id]" value="" data-product-id>
                <input type="hidden" name="items[__INDEX__][product_lines][0][price_basis]" value="current_catalog" data-price-basis>
                <input type="hidden" name="items[__INDEX__][product_lines][0][unit_price_rupiah]" value="" data-money-raw data-price-input>
                <input type="hidden" name="items[__INDEX__][product_lines][0][qty]" value="1" data-qty-input>
                <input type="hidden" value="" data-product-search>
                <small class="text-danger d-none" data-stock-error>Qty melebihi stok tersedia.</small>
                <small class="text-danger d-none" data-min-price-warning>Harga tidak boleh di bawah minimum.</small>
                <small class="text-muted d-none" data-stock-text>Stok tersedia: -</small>
                <small class="text-muted d-none" data-min-price-text>Harga produk mengikuti katalog.</small>
            </div>

            <template data-product-line-template>
                <div data-product-line>
                    <input type="hidden" name="items[__INDEX__][product_lines][__PRODUCT_INDEX__][product_id]" value="" data-product-id>
                    <input type="hidden" name="items[__INDEX__][product_lines][__PRODUCT_INDEX__][price_basis]" value="current_catalog" data-price-basis>
                    <input type="hidden" name="items[__INDEX__][product_lines][__PRODUCT_INDEX__][unit_price_rupiah]" value="" data-money-raw data-price-input>
                    <input type="hidden" name="items[__INDEX__][product_lines][__PRODUCT_INDEX__][qty]" value="1" data-qty-input>
                    <input type="hidden" value="" data-product-search>
                    <small class="text-danger d-none" data-stock-error>Qty melebihi stok tersedia.</small>
                    <small class="text-danger d-none" data-min-price-warning>Harga tidak boleh di bawah minimum.</small>
                    <small class="text-muted d-none" data-stock-text>Stok tersedia: -</small>
                    <small class="text-muted d-none" data-min-price-text>Harga produk mengikuti katalog.</small>
                    <button type="button" class="d-none" data-remove-product-line>Hapus</button>
                </div>
            </template>
        </div>
    </div>
</template>
