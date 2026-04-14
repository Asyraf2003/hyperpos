@extends('layouts.app')

@section('title', 'Laporan Hutang Karyawan')
@section('heading', 'Laporan Hutang Karyawan')

@section('content')
<div class="row g-3 mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
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
                    action="{{ route('admin.reports.employee_debt.index') }}"
                    class="row g-3"
                    id="employee-debt-report-filter-form"
                >
                    <div class="col-12 col-lg-3">
                        <label for="period_mode" class="form-label">Mode Periode</label>
                        <select name="period_mode" id="period_mode" class="form-select">
                            <option value="daily" {{ $filters['period_mode'] === 'daily' ? 'selected' : '' }}>Harian</option>
                            <option value="weekly" {{ $filters['period_mode'] === 'weekly' ? 'selected' : '' }}>Mingguan</option>
                            <option value="monthly" {{ $filters['period_mode'] === 'monthly' ? 'selected' : '' }}>Bulanan</option>
                            <option value="custom" {{ $filters['period_mode'] === 'custom' ? 'selected' : '' }}>Custom</option>
                        </select>
                    </div>

                    <div class="col-12 col-lg-3">
                        <label for="reference_date" class="form-label">Reference Date</label>
                        <input
                            type="date"
                            name="reference_date"
                            id="reference_date"
                            class="form-control"
                            value="{{ $filters['reference_date'] }}"
                        >
                    </div>

                    <div class="col-12 col-lg-3">
                        <label for="date_from" class="form-label">Tanggal Mulai</label>
                        <input
                            type="date"
                            name="date_from"
                            id="date_from"
                            class="form-control"
                            value="{{ $filters['date_from'] }}"
                        >
                    </div>

                    <div class="col-12 col-lg-3">
                        <label for="date_to" class="form-label">Tanggal Akhir</label>
                        <input
                            type="date"
                            name="date_to"
                            id="date_to"
                            class="form-control"
                            value="{{ $filters['date_to'] }}"
                        >
                    </div>

                    <div class="col-12 d-flex gap-2">
                        <button type="submit" class="btn btn-primary">Terapkan Filter</button>
                        <a href="{{ route('admin.reports.employee_debt.index') }}" class="btn btn-outline-secondary">
                            Reset
                        </a>
                    </div>
                </form>

                <div class="alert alert-light border mt-3 mb-0">
                    Rentang aktif: <strong>{{ $filters['range_label'] }}</strong>
                </div>
            </div>
        </div>
    </div>
</div>

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
                <div class="text-muted small">Status Paid</div>
                <div class="fs-5 fw-bold">{{ number_format($summary['paid_rows'] ?? 0, 0, ',', '.') }}</div>
            </div>
        </div>
    </div>

    <div class="col-12 col-md-6 col-xl-2">
        <div class="card">
            <div class="card-body">
                <div class="text-muted small">Status Unpaid</div>
                <div class="fs-5 fw-bold">{{ number_format($summary['unpaid_rows'] ?? 0, 0, ',', '.') }}</div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3">
    <div class="col-12 col-xl-4">
        <div class="card h-100">
            <div class="card-body">
                <h5 class="card-title mb-3">Breakdown Per Tanggal</h5>

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
        <div class="card h-100">
            <div class="card-body">
                <h5 class="card-title mb-3">Breakdown Status</h5>

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
                                <th>Recorded At</th>
                                <th>Debt ID</th>
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
            </div>
        </div>
    </div>
</div>
@endsection
