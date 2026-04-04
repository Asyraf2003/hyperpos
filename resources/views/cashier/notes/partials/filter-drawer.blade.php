<div id="cashier-note-filter-backdrop" class="position-fixed top-0 start-0 w-100 h-100 bg-dark bg-opacity-25 d-none" style="z-index: 1040;"></div>

<div id="cashier-note-filter-drawer" class="position-fixed top-0 end-0 h-100 bg-body border-start shadow d-none" style="width: 360px; z-index: 1050; overflow-y: auto;">
    <div class="p-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="mb-0">Filter Nota Kasir</h5>
            <button type="button" id="close-cashier-note-filter" class="btn btn-sm btn-light-secondary">Tutup</button>
        </div>

        <form id="cashier-note-filter-form">
            <div class="form-group mb-3">
                <label for="cashier-note-date" class="form-label">Tanggal Acuan</label>
                <input
                    type="date"
                    id="cashier-note-date"
                    class="form-control"
                    value="{{ $filters['date'] }}"
                    data-ui-date="single"
                >
                <div class="form-text">
                    Riwayat kasir memakai acuan hari ini, dengan jangkauan hari ini dan kemarin untuk note yang masih open.
                </div>
            </div>

            <div class="form-group mb-3">
                <label for="cashier-note-payment-status" class="form-label">Status Pembayaran</label>
                <select id="cashier-note-payment-status" class="form-select">
                    <option value="" @selected($filters['payment_status'] === '')>Semua Status</option>
                    <option value="unpaid" @selected($filters['payment_status'] === 'unpaid')>Belum Dibayar</option>
                    <option value="partial" @selected($filters['payment_status'] === 'partial')>Dibayar Sebagian</option>
                    <option value="paid" @selected($filters['payment_status'] === 'paid')>Lunas</option>
                </select>
            </div>

            <div class="form-group mb-4">
                <label for="cashier-note-work-status" class="form-label">Status Pengerjaan</label>
                <select id="cashier-note-work-status" class="form-select">
                    <option value="" @selected($filters['work_status'] === '')>Semua Status</option>
                    <option value="open" @selected($filters['work_status'] === 'open')>Open</option>
                    <option value="done" @selected($filters['work_status'] === 'done')>Selesai</option>
                    <option value="canceled" @selected($filters['work_status'] === 'canceled')>Batal</option>
                </select>
            </div>

            <div class="d-grid gap-2">
                <button type="submit" class="btn btn-primary">Terapkan Filter</button>
                <button type="button" id="reset-cashier-note-filter" class="btn btn-light-secondary">Reset</button>
            </div>
        </form>
    </div>
</div>
