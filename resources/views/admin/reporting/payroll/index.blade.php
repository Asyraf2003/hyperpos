@extends('layouts.app')
@include('layouts.partials.date-picker-assets')

@section('title', 'Laporan Gaji')
@section('heading', 'Laporan Gaji')

@section('content')
@include('admin.reporting.partials.period_filter', [
    'formId' => 'payroll-report-filter-form',
    'action' => route('admin.reports.payroll.index'),
    'resetUrl' => route('admin.reports.payroll.index'),
    'rangeLabelText' => 'Rentang pencairan aktif',
    'basisDateLabel' => 'Tanggal pencairan gaji',
    'basisDateNote' => 'Mode harian hanya menghitung payroll yang cair pada tanggal tersebut. Payroll yang direversal tidak ikut dihitung.',
])

<div class="row g-3 mb-4">
    <div class="col-12 col-md-6 col-xl-2">
        <div class="card"><div class="card-body">
            <div class="text-muted small">Jumlah Pencairan</div>
            <div class="fs-5 fw-bold">{{ number_format($summary['total_rows'] ?? 0, 0, ',', '.') }}</div>
        </div></div>
    </div>

    <div class="col-12 col-md-6 col-xl-3">
        <div class="card"><div class="card-body">
            <div class="text-muted small">Total Nominal</div>
            <div class="fs-5 fw-bold text-danger">Rp {{ number_format($summary['total_amount_rupiah'] ?? 0, 0, ',', '.') }}</div>
        </div></div>
    </div>

    <div class="col-12 col-md-6 col-xl-2">
        <div class="card"><div class="card-body">
            <div class="text-muted small">Tanggal Terakhir</div>
            <div class="fs-5 fw-bold">{{ $summary['latest_disbursement_date'] ?? '-' }}</div>
        </div></div>
    </div>

    <div class="col-12 col-md-6 col-xl-3">
        <div class="card"><div class="card-body">
            <div class="text-muted small">Mode Terbesar</div>
            <div class="fs-5 fw-bold">{{ $summary['top_mode_label'] ?? '-' }}</div>
        </div></div>
    </div>

    <div class="col-12 col-md-6 col-xl-2">
        <div class="card"><div class="card-body">
            <div class="text-muted small">Rata-rata Harian</div>
            <div class="fs-5 fw-bold">Rp {{ number_format($summary['average_daily_rupiah'] ?? 0, 0, ',', '.') }}</div>
        </div></div>
    </div>
</div>

<div class="row g-3">
    <div class="col-12 col-xl-4">
        <div class="card h-100">
            <div class="card-body">
                <h5 class="card-title mb-3">Rincian Per Tanggal</h5>

                <div class="table-responsive">
                    <table class="table table-sm align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Tanggal</th>
                                <th class="text-end">Data</th>
                                <th class="text-end">Nominal</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($periodRows as $row)
                                <tr>
                                    <td>{{ $row['period_label'] }}</td>
                                    <td class="text-end">{{ number_format($row['total_rows'], 0, ',', '.') }}</td>
                                    <td class="text-end">Rp {{ number_format($row['total_amount_rupiah'], 0, ',', '.') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-center text-muted">Belum ada payroll pada periode ini.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12 col-xl-3">
        <div class="card h-100">
            <div class="card-body">
                <h5 class="card-title mb-3">Rincian Mode</h5>

                <div class="table-responsive">
                    <table class="table table-sm align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Mode</th>
                                <th class="text-end">Data</th>
                                <th class="text-end">Nominal</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($modeRows as $row)
                                <tr>
                                    <td>{{ $row['mode_label'] }}</td>
                                    <td class="text-end">{{ number_format($row['total_rows'], 0, ',', '.') }}</td>
                                    <td class="text-end">Rp {{ number_format($row['total_amount_rupiah'], 0, ',', '.') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-center text-muted">Belum ada mode pada periode ini.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12 col-xl-5">
        <div class="card h-100">
            <div class="card-body">
                <h5 class="card-title mb-3">Detail Pencairan Gaji</h5>

                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Tanggal</th>
                                <th>Karyawan</th>
                                <th>Mode</th>
                                <th>Catatan</th>
                                <th class="text-end">Nominal</th>
                            </tr>
                        </thead>
                        <tbody id="payroll-report-table-body">
                            @forelse ($rows as $row)
                                <tr>
                                    <td>{{ $row['disbursement_date'] }}</td>
                                    <td>{{ $row['employee_name'] }}</td>
                                    <td>{{ $row['mode_label'] }}</td>
                                    <td>{{ $row['notes'] ?? '-' }}</td>
                                    <td class="text-end">Rp {{ number_format($row['amount_rupiah'], 0, ',', '.') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted">Belum ada payroll pada periode ini.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-3 d-flex justify-content-end">
                    @include('layouts.partials.pagination', ['paginator' => $rows])
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
