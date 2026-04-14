@extends('layouts.app')
@include('layouts.partials.date-picker-assets')

@section('title', 'Laba Kas Operasional')
@section('heading', 'Laba Kas Operasional')

@section('content')
@include('admin.reporting.partials.period_filter', [
    'formId' => 'operational-profit-report-filter-form',
    'action' => route('admin.reports.operational_profit.index'),
    'resetUrl' => route('admin.reports.operational_profit.index'),
    'rangeLabelText' => 'Rentang aktif',
])

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
