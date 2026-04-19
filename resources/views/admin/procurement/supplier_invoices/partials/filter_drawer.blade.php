<div id="procurement-filter-backdrop" class="position-fixed top-0 start-0 w-100 h-100 bg-dark bg-opacity-25 d-none" style="z-index: 1040;"></div>

<div id="procurement-filter-drawer" class="position-fixed top-0 end-0 h-100 bg-body border-start shadow d-none" style="width: 360px; z-index: 1050; overflow-y: auto;">
    <div class="p-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="mb-0">Filter Nota Pemasok</h5>
            <button type="button" id="close-procurement-filter" class="btn btn-sm btn-light-secondary">Tutup</button>
        </div>

        <form id="procurement-filter-form">
            <div class="form-group mb-4">
                <label class="form-label" for="filter-payment-status">Status Nota</label>
                <select id="filter-payment-status" name="payment_status" class="form-select">
                    <option value="all">Semua</option>
                    <option value="outstanding">Masih Punya Tagihan</option>
                    <option value="paid">Sudah Lunas</option>
                    <option value="voided">Sudah Dibatalkan</option>
                </select>
            </div>

            <div class="form-group mb-4">
                <label class="form-label" for="shipment-date-range">Rentang Tanggal Kirim</label>
                <input
                    type="text"
                    id="shipment-date-range"
                    class="form-control"
                    data-ui-date="range-single"
                    data-ui-date-placeholder="Pilih rentang tanggal kirim"
                    data-range-start-name="shipment_date_from"
                    data-range-end-name="shipment_date_to"
                    autocomplete="off"
                >
                <input type="hidden" id="filter-shipment-date-from" name="shipment_date_from" value="">
                <input type="hidden" id="filter-shipment-date-to" name="shipment_date_to" value="">
            </div>

            <div class="d-grid gap-2">
                <button type="submit" class="btn btn-primary">Terapkan Filter</button>
                <button type="button" id="reset-procurement-filter" class="btn btn-light-secondary">Reset</button>
            </div>
        </form>
    </div>
</div>
