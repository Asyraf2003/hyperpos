<div id="admin-note-filter-backdrop" class="position-fixed top-0 start-0 w-100 h-100 bg-dark bg-opacity-25 d-none" style="z-index: 1040;"></div>

<div id="admin-note-filter-drawer" class="position-fixed top-0 end-0 h-100 bg-body border-start shadow d-none" style="width: 360px; z-index: 1050; overflow-y: auto;">
    <div class="p-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="mb-0">Filter Nota Admin</h5>
            <button type="button" id="close-admin-note-filter" class="btn btn-sm btn-light-secondary">Tutup</button>
        </div>

        <form id="admin-note-filter-form">
            <div class="form-group mb-3">
                <label for="admin-note-date-from" class="form-label">Tanggal Mulai</label>
                <input
                    type="date"
                    id="admin-note-date-from"
                    class="form-control"
                    data-ui-date="single"
                    value="{{ $filters['date_from'] }}"
                >
            </div>

            <div class="form-group mb-3">
                <label for="admin-note-date-to" class="form-label">Tanggal Akhir</label>
                <input
                    type="date"
                    id="admin-note-date-to"
                    class="form-control"
                    data-ui-date="single"
                    value="{{ $filters['date_to'] }}"
                >
            </div>

            <div class="form-group mb-3">
                <label for="admin-note-payment-status" class="form-label">Status Pembayaran</label>
                <select id="admin-note-payment-status" class="form-select">
                    <option value="" @selected($filters['payment_status'] === '')>Semua Status</option>
                    <option value="unpaid" @selected($filters['payment_status'] === 'unpaid')>Belum Dibayar</option>
                    <option value="partial" @selected($filters['payment_status'] === 'partial')>Dibayar Sebagian</option>
                    <option value="paid" @selected($filters['payment_status'] === 'paid')>Lunas</option>
                </select>
            </div>

            <div class="form-group mb-3">
                <label for="admin-note-editability" class="form-label">Mode Edit</label>
                <select id="admin-note-editability" class="form-select">
                    <option value="" @selected($filters['editability'] === '')>Semua Mode</option>
                    <option value="editable_normal" @selected($filters['editability'] === 'editable_normal')>Editable Normal</option>
                    <option value="admin_strict" @selected($filters['editability'] === 'admin_strict')>Admin Ketat</option>
                    <option value="correction_only" @selected($filters['editability'] === 'correction_only')>Correction Only</option>
                </select>
            </div>

            <div class="form-group mb-4">
                <label for="admin-note-work-summary" class="form-label">Ringkasan Pengerjaan</label>
                <select id="admin-note-work-summary" class="form-select">
                    <option value="" @selected($filters['work_summary'] === '')>Semua Ringkasan</option>
                    <option value="has_open" @selected($filters['work_summary'] === 'has_open')>Ada Open</option>
                    <option value="has_done" @selected($filters['work_summary'] === 'has_done')>Ada Selesai</option>
                    <option value="has_canceled" @selected($filters['work_summary'] === 'has_canceled')>Ada Batal</option>
                </select>
            </div>

            <div class="d-grid gap-2">
                <button type="submit" class="btn btn-primary">Terapkan Filter</button>
                <button type="button" id="reset-admin-note-filter" class="btn btn-light-secondary">Reset</button>
            </div>
        </form>
    </div>
</div>
