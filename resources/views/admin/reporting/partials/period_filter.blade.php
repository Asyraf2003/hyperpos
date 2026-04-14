<div class="card mb-4">
    <div class="card-body">
        <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
            
            <div class="alert alert-light border mb-0 flex-grow-1">
                @if (!empty($noteText))
                    <div>{{ $noteText }}</div>
                @endif
                {{ $rangeLabelText }}: <strong>{{ $filters['range_label'] }}</strong>
            </div>

            <div class="d-flex gap-2 shadow-sm shadow-md-none">
                <button
                    type="button"
                    id="{{ $formId }}-open-filter"
                    class="btn btn-primary text-nowrap"
                >
                    Filter
                </button>

                <a href="{{ $resetUrl }}" class="btn btn-outline-secondary text-nowrap">
                    Reset
                </a>
            </div>
        </div>
    </div>
</div>

<div
    id="{{ $formId }}-filter-backdrop"
    class="position-fixed top-0 start-0 w-100 h-100 bg-dark bg-opacity-25 d-none"
    style="z-index: 1040;"
></div>

<div
    id="{{ $formId }}-filter-drawer"
    class="position-fixed top-0 end-0 h-100 bg-body border-start shadow d-none"
    style="width: 360px; z-index: 1050; overflow-y: auto;"
>
    <div class="p-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="mb-0">Filter Laporan</h5>
            <button
                type="button"
                id="{{ $formId }}-close-filter"
                class="btn btn-sm btn-light-secondary"
            >
                Tutup
            </button>
        </div>

        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0 ps-3">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form
            method="get"
            action="{{ $action }}"
            id="{{ $formId }}"
            data-report-period-filter="1"
            data-filter-open-button-id="{{ $formId }}-open-filter"
            data-filter-close-button-id="{{ $formId }}-close-filter"
            data-filter-drawer-id="{{ $formId }}-filter-drawer"
            data-filter-backdrop-id="{{ $formId }}-filter-backdrop"
            class="d-grid gap-3"
        >
            <div class="form-group">
                <label for="{{ $formId }}-period-mode" class="form-label">Mode Periode</label>
                <select
                    name="period_mode"
                    id="{{ $formId }}-period-mode"
                    class="form-select"
                >
                    <option value="daily" {{ $filters['period_mode'] === 'daily' ? 'selected' : '' }}>Harian</option>
                    <option value="weekly" {{ $filters['period_mode'] === 'weekly' ? 'selected' : '' }}>Mingguan</option>
                    <option value="monthly" {{ $filters['period_mode'] === 'monthly' ? 'selected' : '' }}>Bulanan</option>
                    <option value="custom" {{ $filters['period_mode'] === 'custom' ? 'selected' : '' }}>Custom</option>
                </select>
            </div>

            <div class="form-group">
                <label for="{{ $formId }}-reference-date" class="form-label">Reference Date</label>
                <input
                    type="date"
                    name="reference_date"
                    id="{{ $formId }}-reference-date"
                    class="form-control"
                    value="{{ $filters['reference_date'] }}"
                    data-ui-date="single"
                    data-ui-date-placeholder="Pilih tanggal referensi"
                    autocomplete="off"
                >
            </div>

            <div class="form-group">
                <label class="form-label" for="{{ $formId }}-date-range">Rentang Tanggal</label>

                <div data-report-range-enhanced-wrap class="d-none">
                    <input
                        type="text"
                        id="{{ $formId }}-date-range"
                        class="form-control"
                        data-ui-date="range-single"
                        data-ui-date-placeholder="Pilih rentang tanggal"
                        data-range-start-name="date_from"
                        data-range-end-name="date_to"
                        autocomplete="off"
                    >
                </div>

                <div data-report-range-fallback-wrap class="row g-2">
                    <div class="col-6">
                        <label
                            class="form-label small text-muted"
                            for="{{ $formId }}-date-from-fallback"
                        >
                            Dari
                        </label>
                        <input
                            type="date"
                            id="{{ $formId }}-date-from-fallback"
                            class="form-control"
                            data-report-date-fallback-from
                            value="{{ $filters['date_from'] }}"
                            autocomplete="off"
                        >
                    </div>

                    <div class="col-6">
                        <label
                            class="form-label small text-muted"
                            for="{{ $formId }}-date-to-fallback"
                        >
                            Sampai
                        </label>
                        <input
                            type="date"
                            id="{{ $formId }}-date-to-fallback"
                            class="form-control"
                            data-report-date-fallback-to
                            value="{{ $filters['date_to'] }}"
                            autocomplete="off"
                        >
                    </div>

                    <div class="col-12">
                        <small class="text-muted">Fallback tanggal aktif bila kalender tidak tersedia.</small>
                    </div>
                </div>

                <input type="hidden" name="date_from" value="{{ $filters['date_from'] }}">
                <input type="hidden" name="date_to" value="{{ $filters['date_to'] }}">
            </div>

            <div class="d-grid gap-2">
                <button type="submit" class="btn btn-primary">Terapkan Filter</button>
                <a href="{{ $resetUrl }}" class="btn btn-light-secondary">Reset</a>
            </div>
        </form>
    </div>
</div>

@push('scripts')
    <script src="{{ asset('assets/static/js/shared/admin-report-period-filter.js') }}?v={{ filemtime(public_path('assets/static/js/shared/admin-report-period-filter.js')) }}"></script>
@endpush
