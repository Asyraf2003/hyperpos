<div id="product-filter-backdrop" class="position-fixed top-0 start-0 w-100 h-100 bg-dark bg-opacity-25 d-none" style="z-index: 1040;"></div>

<div id="product-filter-drawer" class="position-fixed top-0 end-0 h-100 bg-body border-start shadow d-none" style="width: 360px; z-index: 1050; overflow-y: auto;">
    <div class="p-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="mb-0">Filter Produk</h5>
            <button type="button" id="close-product-filter" class="btn btn-sm btn-light-secondary">Tutup</button>
        </div>

        <form id="product-filter-form">
            <div class="form-group mb-3">
                <label class="form-label" for="filter-merek">Merek</label>
                <input type="text" id="filter-merek" name="merek" class="form-control" placeholder="Contoh: Federal">
            </div>

            <div class="row">
                <div class="col-6">
                    <div class="form-group mb-3">
                        <label class="form-label" for="filter-ukuran-min">Ukuran Min</label>
                        <input type="number" id="filter-ukuran-min" name="ukuran_min" class="form-control" min="0" step="1">
                    </div>
                </div>

                <div class="col-6">
                    <div class="form-group mb-3">
                        <label class="form-label" for="filter-ukuran-max">Ukuran Max</label>
                        <input type="number" id="filter-ukuran-max" name="ukuran_max" class="form-control" min="0" step="1">
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-6">
                    <div class="form-group mb-3">
                        <label class="form-label" for="filter-harga-min">Harga Min</label>
                        <input type="number" id="filter-harga-min" name="harga_min" class="form-control" min="0" step="1">
                    </div>
                </div>

                <div class="col-6">
                    <div class="form-group mb-4">
                        <label class="form-label" for="filter-harga-max">Harga Max</label>
                        <input type="number" id="filter-harga-max" name="harga_max" class="form-control" min="0" step="1">
                    </div>
                </div>
            </div>

            <div class="d-grid gap-2">
                <button type="submit" class="btn btn-primary">Terapkan Filter</button>
                <button type="button" id="reset-product-filter" class="btn btn-light-secondary">Reset</button>
            </div>
        </form>
    </div>
</div>
