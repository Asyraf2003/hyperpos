@php
    $dashboardFilterFormId = 'admin-dashboard-filter';
    $dashboardFilterOpenButtonId = 'admin-dashboard-filter-open-filter';
    $dashboardFilterCloseButtonId = 'admin-dashboard-filter-close-filter';
    $dashboardFilterDrawerId = 'admin-dashboard-filter-drawer';
    $dashboardFilterBackdropId = 'admin-dashboard-filter-backdrop';
    $dashboardActiveMonth = (string) ($dashboard['period']['active_month'] ?? now()->format('Y-m'));
@endphp

<div
    id="{{ $dashboardFilterBackdropId }}"
    class="position-fixed top-0 start-0 w-100 h-100 bg-dark bg-opacity-25 d-none"
    style="z-index: 1040;"
></div>

<div
    id="{{ $dashboardFilterDrawerId }}"
    class="position-fixed top-0 end-0 h-100 bg-body border-start shadow d-none"
    style="width: 420px; max-width: 100%; z-index: 1050; overflow-y: auto;"
>
    <div class="p-4">
        <div class="d-flex justify-content-between align-items-start gap-3 mb-4">
            <div>
                <h5 class="mb-1 fw-bold">Filter Dashboard</h5>
                <p class="mb-0 text-muted small">
                    Atur periode dashboard dan buka cetak laporan resmi dari periode aktif.
                </p>
            </div>

            <button
                type="button"
                id="{{ $dashboardFilterCloseButtonId }}"
                class="btn btn-sm btn-light-secondary"
            >
                Tutup
            </button>
        </div>

        <form
            method="get"
            action="{{ route('admin.dashboard') }}"
            id="{{ $dashboardFilterFormId }}"
            data-report-period-filter="1"
            data-filter-open-button-id="{{ $dashboardFilterOpenButtonId }}"
            data-filter-close-button-id="{{ $dashboardFilterCloseButtonId }}"
            data-filter-drawer-id="{{ $dashboardFilterDrawerId }}"
            data-filter-backdrop-id="{{ $dashboardFilterBackdropId }}"
            class="d-grid gap-4"
        >
            <section class="d-grid gap-3">
                <div>
                    <div class="section-title mb-1">Periode Dashboard</div>
                    <p class="section-subtitle">
                        Data dashboard sedang membaca periode
                        {{ $dashboard['period']['date_from'] ?? '-' }}
                        s.d.
                        {{ $dashboard['period']['date_to'] ?? '-' }}.
                    </p>
                </div>

                <div class="form-group">
                    <label for="{{ $dashboardFilterFormId }}-month" class="form-label fw-bold">Pilih Bulan</label>
                    <input
                        type="month"
                        id="{{ $dashboardFilterFormId }}-month"
                        name="month"
                        class="form-control"
                        value="{{ $dashboardActiveMonth }}"
                    >
                </div>

                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-primary fw-bold">
                        <i class="bi bi-funnel me-2"></i>
                        Terapkan Periode
                    </button>

                    <a href="{{ route('admin.dashboard') }}" class="btn btn-light-secondary fw-bold">
                        Reset Bulan Aktif
                    </a>
                </div>
            </section>

            <section class="d-grid gap-3">
                <div>
                    <div class="section-title mb-1">Cetak Laporan Resmi</div>
                    <p class="section-subtitle">
                        Shortcut ke PDF/Excel laporan canonical untuk bulan aktif.
                    </p>
                </div>

                <div class="helper-note">
                    Dashboard tidak membuat export sendiri. Tombol di bawah membuka export laporan resmi agar angka tetap mengikuti dataset report, bukan chart atau DOM dashboard.
                </div>

                <div class="d-grid gap-3">
                    @foreach ($dashboardReportExportShortcuts as $shortcut)
                        <div class="report-export-shortcut-card">
                            <div class="inventory-title mb-2">{{ $shortcut['label'] }}</div>
                            <p class="inventory-meta mb-3">
                                Periode dashboard: {{ $dashboardActiveMonth }}
                            </p>

                            <div class="report-export-shortcut-actions">
                                <a href="{{ route($shortcut['pdf'], $dashboardExportQuery) }}" class="btn btn-sm btn-outline-danger fw-bold">
                                    <i class="bi bi-file-earmark-pdf me-1"></i>
                                    PDF
                                </a>

                                <a href="{{ route($shortcut['excel'], $dashboardExportQuery) }}" class="btn btn-sm btn-outline-success fw-bold">
                                    <i class="bi bi-file-earmark-spreadsheet me-1"></i>
                                    Excel
                                </a>

                                <a href="{{ route($shortcut['index'], $dashboardExportQuery) }}" class="btn btn-sm btn-outline-primary fw-bold">
                                    Buka
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
            </section>
        </form>
    </div>
</div>

@push('scripts')
    <script src="{{ asset('assets/static/js/shared/admin-report-period-filter.js') }}?v={{ filemtime(public_path('assets/static/js/shared/admin-report-period-filter.js')) }}"></script>
@endpush
