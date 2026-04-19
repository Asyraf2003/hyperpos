<div id="cashier-note-filter-backdrop" class="position-fixed top-0 start-0 w-100 h-100 bg-dark bg-opacity-25 d-none" style="z-index: 1040;"></div>

<div id="cashier-note-filter-drawer" class="position-fixed top-0 end-0 h-100 bg-body border-start shadow d-none" style="width: 360px; z-index: 1050; overflow-y: auto;">
    <div class="p-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="mb-0">Filter Daftar Nota Kasir</h5>
            <button type="button" id="close-cashier-note-filter" class="btn btn-sm btn-light-secondary">Tutup</button>
        </div>

        <form id="cashier-note-filter-form">
            <div class="form-group mb-4">
                <label for="cashier-note-line-status" class="form-label">Status Line</label>
                <select id="cashier-note-line-status" class="form-select">
                    <option value="" @selected($filters['line_status'] === '')>Semua Status</option>
                    <option value="open" @selected($filters['line_status'] === 'open')>Open</option>
                    <option value="close" @selected($filters['line_status'] === 'close')>Close</option>
                    <option value="refund" @selected($filters['line_status'] === 'refund')>Refund</option>
                </select>
                <div class="form-text">
                    Daftar kasir selalu memakai window hari ini dan kemarin. Filter dipersempit berdasarkan komposisi line pada nota.
                </div>
            </div>

            <div class="d-grid gap-2">
                <button type="submit" class="btn btn-primary">Terapkan Filter</button>
                <button type="button" id="reset-cashier-note-filter" class="btn btn-light-secondary">Reset</button>
            </div>
        </form>
    </div>
</div>
