<div id="expense-filter-backdrop" class="position-fixed top-0 start-0 w-100 h-100 bg-dark bg-opacity-25 d-none" style="z-index: 1040;"></div>

<div id="expense-filter-drawer" class="position-fixed top-0 end-0 h-100 bg-body border-start shadow d-none" style="width: 360px; z-index: 1050; overflow-y: auto;">
    <div class="p-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="mb-0">Filter Pengeluaran</h5>
            <button type="button" id="close-expense-filter" class="btn btn-sm btn-light-secondary">Tutup</button>
        </div>

        <form id="expense-filter-form">
            <div class="form-group mb-3">
                <label class="form-label" for="filter-category-id">Kategori</label>
                <select id="filter-category-id" name="category_id" class="form-select">
                    <option value="">Semua kategori</option>
                    @foreach ($categoryOptions as $option)
                        <option value="{{ $option['id'] }}">{{ $option['label'] }}</option>
                    @endforeach
                </select>
            </div>

            <div class="row">
                <div class="col-6">
                    <div class="form-group mb-3">
                        <label class="form-label" for="filter-date-from">Tanggal Mulai</label>
                        <input type="date" id="filter-date-from" name="date_from" class="form-control">
                    </div>
                </div>

                <div class="col-6">
                    <div class="form-group mb-4">
                        <label class="form-label" for="filter-date-to">Tanggal Akhir</label>
                        <input type="date" id="filter-date-to" name="date_to" class="form-control">
                    </div>
                </div>
            </div>

            <div class="d-grid gap-2">
                <button type="submit" class="btn btn-primary">Terapkan Filter</button>
                <button type="button" id="reset-expense-filter" class="btn btn-light-secondary">Reset</button>
            </div>
        </form>
    </div>
</div>
