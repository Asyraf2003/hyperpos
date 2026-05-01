@extends('layouts.app')
@include('layouts.partials.date-picker-assets')

@section('title', 'Laporan Hutang Karyawan')
@section('heading', 'Laporan Hutang Karyawan')

@section('content')
@include('admin.reporting.partials.period_filter', [
    'formId' => 'employee-debt-report-filter-form',
    'action' => route('admin.reports.employee_debt.index'),
    'resetUrl' => route('admin.reports.employee_debt.index'),
    'rangeLabelText' => 'Rentang pencatatan aktif',
    'basisDateLabel' => 'Tanggal pencatatan hutang',
    'basisDateNote' => 'Mode harian hanya menghitung hutang yang dicatat pada tanggal tersebut. Data tidak dibawa ke hari berikutnya.',
    'supportsCustomRange' => true,
])

<div class="row g-3 mb-4">
    <div class="col-12 col-md-6 col-xl-2">
        <div class="card">
            <div class="card-body">
                <div class="text-muted small">Total Hutang</div>
                <div class="fs-5 fw-bold">Rp {{ number_format($summary['total_debt'] ?? 0, 0, ',', '.') }}</div>
            </div>
        </div>
    </div>

    <div class="col-12 col-md-6 col-xl-2">
        <div class="card">
            <div class="card-body">
                <div class="text-muted small">Sudah Dibayar</div>
                <div class="fs-5 fw-bold text-success">Rp {{ number_format($summary['total_paid_amount'] ?? 0, 0, ',', '.') }}</div>
            </div>
        </div>
    </div>

    <div class="col-12 col-md-6 col-xl-2">
        <div class="card">
            <div class="card-body">
                <div class="text-muted small">Sisa Hutang</div>
                <div class="fs-5 fw-bold text-danger">Rp {{ number_format($summary['total_remaining_balance'] ?? 0, 0, ',', '.') }}</div>
            </div>
        </div>
    </div>

    <div class="col-12 col-md-6 col-xl-2">
        <div class="card">
            <div class="card-body">
                <div class="text-muted small">Jumlah Data</div>
                <div class="fs-5 fw-bold">{{ number_format($summary['total_rows'] ?? 0, 0, ',', '.') }}</div>
            </div>
        </div>
    </div>

    <div class="col-12 col-md-6 col-xl-2">
        <div class="card">
            <div class="card-body">
                <div class="text-muted small">Status Lunas</div>
                <div class="fs-5 fw-bold">{{ number_format($summary['paid_rows'] ?? 0, 0, ',', '.') }}</div>
            </div>
        </div>
    </div>

    <div class="col-12 col-md-6 col-xl-2">
        <div class="card">
            <div class="card-body">
                <div class="text-muted small">Status Belum Lunas</div>
                <div class="fs-5 fw-bold">{{ number_format($summary['unpaid_rows'] ?? 0, 0, ',', '.') }}</div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3">
    <div class="col-12 col-xl-4">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title mb-3">Rincian Per Tanggal</h5>

                <div class="table-responsive">
                    <table class="table table-sm align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Tanggal</th>
                                <th class="text-end">Data</th>
                                <th class="text-end">Sisa</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($periodRows as $row)
                                <tr>
                                    <td>{{ $row['period_label'] }}</td>
                                    <td class="text-end">{{ number_format($row['total_rows'], 0, ',', '.') }}</td>
                                    <td class="text-end">Rp {{ number_format($row['total_remaining_balance'], 0, ',', '.') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-center text-muted">Belum ada data hutang pada periode ini.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12 col-xl-3">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title mb-3">Rincian Status</h5>

                <div class="table-responsive">
                    <table class="table table-sm align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Status</th>
                                <th class="text-end">Data</th>
                                <th class="text-end">Sisa</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($statusRows as $row)
                                <tr>
                                    <td>{{ $row['status'] }}</td>
                                    <td class="text-end">{{ number_format($row['total_rows'], 0, ',', '.') }}</td>
                                    <td class="text-end">Rp {{ number_format($row['total_remaining_balance'], 0, ',', '.') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-center text-muted">Belum ada data status pada periode ini.</td>
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
                <h5 class="card-title mb-3">Detail Hutang</h5>

                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Tanggal Catat</th>
                                <th>Referensi Hutang</th>
                                <th>Employee ID</th>
                                <th>Status</th>
                                <th class="text-end">Total</th>
                                <th class="text-end">Dibayar</th>
                                <th class="text-end">Sisa</th>
                            </tr>
                        </thead>
                        <tbody id="employee-debt-report-table-body">
                            @forelse ($rows as $row)
                                <tr>
                                    <td>{{ $row['recorded_at'] }}</td>
                                    <td>{{ $row['debt_id'] }}</td>
                                    <td>{{ $row['employee_id'] }}</td>
                                    <td>{{ $row['status'] }}</td>
                                    <td class="text-end">Rp {{ number_format($row['total_debt'], 0, ',', '.') }}</td>
                                    <td class="text-end">Rp {{ number_format($row['total_paid_amount'], 0, ',', '.') }}</td>
                                    <td class="text-end">Rp {{ number_format($row['remaining_balance'], 0, ',', '.') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center text-muted">Belum ada data hutang pada periode ini.</td>
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
