<div id="expense-category-filter-backdrop" class="position-fixed top-0 start-0 w-100 h-100 bg-dark bg-opacity-25 d-none" style="z-index: 1040;"></div>

<div id="expense-category-filter-drawer" class="position-fixed top-0 end-0 h-100 bg-body border-start shadow d-none" style="width: 360px; z-index: 1050; overflow-y: auto;">
    <div class="p-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="mb-0">Filter Kategori Pengeluaran</h5>
            <button type="button" id="close-expense-category-filter" class="btn btn-sm btn-light-secondary">Tutup</button>
        </div>

        <form id="expense-category-filter-form">
            <div class="form-group mb-4">
                <label class="form-label" for="filter-category-status">Status</label>
                <select id="filter-category-status" name="is_active" class="form-select">
                    <option value="">Semua status</option>
                    <option value="1">Aktif</option>
                    <option value="0">Nonaktif</option>
                </select>
            </div>

            <div class="d-grid gap-2">
                <button type="submit" class="btn btn-primary">Terapkan Filter</button>
                <button type="button" id="reset-expense-category-filter" class="btn btn-light-secondary">Reset</button>
            </div>
        </form>
    </div>
</div>
