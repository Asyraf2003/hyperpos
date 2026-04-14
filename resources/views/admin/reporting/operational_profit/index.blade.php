@extends('layouts.app')

@section('title', 'Laba Kas Operasional')
@section('heading', 'Laba Kas Operasional')

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
                    action="{{ route('admin.reports.operational_profit.index') }}"
                    class="row g-3"
                    id="operational-profit-report-filter-form"
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
                        <a href="{{ route('admin.reports.operational_profit.index') }}" class="btn btn-outline-secondary">
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

<div class="row g-3">
    <div class="col-12 col-md-6 col-xl-3">
        <div class="card"><div class="card-body">
            <div class="text-muted small">Gross Revenue</div>
            <div class="fs-5 fw-bold">Rp {{ number_format($row['gross_revenue_rupiah'] ?? 0, 0, ',', '.') }}</div>
        </div></div>
    </div>

    <div class="col-12 col-md-6 col-xl-3">
        <div class="card"><div class="card-body">
            <div class="text-muted small">Refunded</div>
            <div class="fs-5 fw-bold text-danger">Rp {{ number_format($row['refunded_rupiah'] ?? 0, 0, ',', '.') }}</div>
        </div></div>
    </div>

    <div class="col-12 col-md-6 col-xl-3">
        <div class="card"><div class="card-body">
            <div class="text-muted small">Net Revenue</div>
            <div class="fs-5 fw-bold">Rp {{ number_format($row['net_revenue_rupiah'] ?? 0, 0, ',', '.') }}</div>
        </div></div>
    </div>

    <div class="col-12 col-md-6 col-xl-3">
        <div class="card"><div class="card-body">
            <div class="text-muted small">Direct Cost</div>
            <div class="fs-5 fw-bold text-danger">Rp {{ number_format($row['direct_cost_rupiah'] ?? 0, 0, ',', '.') }}</div>
        </div></div>
    </div>

    <div class="col-12 col-md-6 col-xl-3">
        <div class="card"><div class="card-body">
            <div class="text-muted small">Gross Profit</div>
            <div class="fs-5 fw-bold">Rp {{ number_format($row['gross_profit_rupiah'] ?? 0, 0, ',', '.') }}</div>
        </div></div>
    </div>

    <div class="col-12 col-md-6 col-xl-3">
        <div class="card"><div class="card-body">
            <div class="text-muted small">Operational Expense</div>
            <div class="fs-5 fw-bold text-danger">Rp {{ number_format($row['operational_expense_rupiah'] ?? 0, 0, ',', '.') }}</div>
        </div></div>
    </div>

    <div class="col-12 col-md-6 col-xl-3">
        <div class="card"><div class="card-body">
            <div class="text-muted small">Payroll Disbursement</div>
            <div class="fs-5 fw-bold text-danger">Rp {{ number_format($row['payroll_disbursement_rupiah'] ?? 0, 0, ',', '.') }}</div>
        </div></div>
    </div>

    <div class="col-12 col-md-6 col-xl-3">
        <div class="card"><div class="card-body">
            <div class="text-muted small">Net Operational Profit</div>
            <div class="fs-5 fw-bold {{ ($row['net_operational_profit_rupiah'] ?? 0) >= 0 ? 'text-success' : 'text-danger' }}">
                Rp {{ number_format($row['net_operational_profit_rupiah'] ?? 0, 0, ',', '.') }}
            </div>
        </div></div>
    </div>
</div>
@endsection
