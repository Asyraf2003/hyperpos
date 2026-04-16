<div class="card mb-4">
    <div class="card-body">
        <div class="row g-3 align-items-start">
            <div class="col-12 col-xl-9">
                <div class="alert alert-light border mb-0">
                    <div class="d-flex flex-column gap-2">
                        <div class="d-flex flex-column flex-md-row gap-3">
                            <div>
                                <div class="text-muted small">Mode Aktif</div>
                                <div class="fw-semibold">
                                    @if (($filters['period_mode'] ?? 'daily') === 'weekly')
                                        Mingguan
                                    @elseif (($filters['period_mode'] ?? 'daily') === 'monthly')
                                        Bulanan
                                    @elseif (($filters['period_mode'] ?? 'daily') === 'custom')
                                        Custom
                                    @else
                                        Harian
                                    @endif
                                </div>
                            </div>

                            <div>
                                <div class="text-muted small">Dasar Tanggal</div>
                                <div class="fw-semibold">{{ $basisDateLabel ?? 'Tanggal referensi laporan' }}</div>
                            </div>

                            <div>
                                <div class="text-muted small">{{ $rangeLabelText ?? 'Rentang Aktif' }}</div>
                                <div class="fw-semibold">{{ $filters['range_label'] }}</div>
                            </div>
                        </div>

                        @if (!empty($basisDateNote))
                            <div class="small text-muted">{{ $basisDateNote }}</div>
                        @endif

                        @if (!empty($noteText))
                            <div class="small text-muted">{{ $noteText }}</div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="col-12 col-xl-3">
                <div class="d-flex gap-2 justify-content-xl-end">
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
            data-mode-help-daily="{{ $modeHelpDaily ?? 'Menampilkan data tepat pada tanggal ini.' }}"
            data-mode-help-weekly="{{ $modeHelpWeekly ?? 'Sistem mengambil Senin sampai Minggu dari minggu tanggal ini.' }}"
            data-mode-help-monthly="{{ $modeHelpMonthly ?? 'Sistem mengambil seluruh bulan dari tanggal ini.' }}"
            data-mode-help-custom="{{ $modeHelpCustom ?? 'Pilih tanggal mulai dan tanggal akhir.' }}"
            class="d-grid gap-3"
        >
            <div class="form-group">
                <label for="{{ $formId }}-period-mode" class="form-label">Mode Periode</label>
                <select
                    name="period_mode"
                    id="{{ $formId }}-period-mode"
                    class="form-select"
                    data-report-period-mode-select
                >
                    <option value="daily" {{ $filters['period_mode'] === 'daily' ? 'selected' : '' }}>Harian</option>
                    <option value="weekly" {{ $filters['period_mode'] === 'weekly' ? 'selected' : '' }}>Mingguan</option>
                    <option value="monthly" {{ $filters['period_mode'] === 'monthly' ? 'selected' : '' }}>Bulanan</option>
                    <option value="custom" {{ $filters['period_mode'] === 'custom' ? 'selected' : '' }}>Custom</option>
                </select>
            </div>

            <div
                class="alert alert-light border small mb-0"
                data-report-mode-help
                aria-live="polite"
            >
                @if (($filters['period_mode'] ?? 'daily') === 'weekly')
                    {{ $modeHelpWeekly ?? 'Sistem mengambil Senin sampai Minggu dari minggu tanggal ini.' }}
                @elseif (($filters['period_mode'] ?? 'daily') === 'monthly')
                    {{ $modeHelpMonthly ?? 'Sistem mengambil seluruh bulan dari tanggal ini.' }}
                @elseif (($filters['period_mode'] ?? 'daily') === 'custom')
                    {{ $modeHelpCustom ?? 'Pilih tanggal mulai dan tanggal akhir.' }}
                @else
                    {{ $modeHelpDaily ?? 'Menampilkan data tepat pada tanggal ini.' }}
                @endif
            </div>

            <div
                class="form-group {{ ($filters['period_mode'] ?? 'daily') === 'custom' ? 'd-none' : '' }}"
                data-report-reference-group
            >
                <label for="{{ $formId }}-reference-date" class="form-label">Tanggal Referensi</label>
                <input
                    type="date"
                    name="reference_date"
                    id="{{ $formId }}-reference-date"
                    class="form-control"
                    value="{{ $filters['reference_date'] }}"
                    data-ui-date="single"
                    data-ui-date-placeholder="Pilih tanggal referensi"
                    data-report-reference-input
                    {{ ($filters['period_mode'] ?? 'daily') === 'custom' ? 'disabled' : '' }}
                    autocomplete="off"
                >
            </div>

            <div
                class="form-group {{ ($filters['period_mode'] ?? 'daily') === 'custom' ? '' : 'd-none' }}"
                data-report-range-group
            >
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
                        data-report-range-input
                        {{ ($filters['period_mode'] ?? 'daily') === 'custom' ? '' : 'disabled' }}
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
                            {{ ($filters['period_mode'] ?? 'daily') === 'custom' ? '' : 'disabled' }}
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
                            {{ ($filters['period_mode'] ?? 'daily') === 'custom' ? '' : 'disabled' }}
                            autocomplete="off"
                        >
                    </div>

                    <div class="col-12">
                        <small class="text-muted">Fallback tanggal aktif bila kalender tidak tersedia.</small>
                    </div>
                </div>

                <input
                    type="hidden"
                    name="date_from"
                    value="{{ $filters['date_from'] }}"
                    data-report-hidden-date-from
                    {{ ($filters['period_mode'] ?? 'daily') === 'custom' ? '' : 'disabled' }}
                >
                <input
                    type="hidden"
                    name="date_to"
                    value="{{ $filters['date_to'] }}"
                    data-report-hidden-date-to
                    {{ ($filters['period_mode'] ?? 'daily') === 'custom' ? '' : 'disabled' }}
                >
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
