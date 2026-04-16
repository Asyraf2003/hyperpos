@extends('layouts.app')
@include('layouts.partials.date-picker-assets')

@section('title', 'Laba Kas Operasional')
@section('heading', 'Laba Kas Operasional')

@section('content')
@include('admin.reporting.partials.period_filter', [
    'formId' => 'operational-profit-report-filter-form',
    'action' => route('admin.reports.operational_profit.index'),
    'resetUrl' => route('admin.reports.operational_profit.index'),
    'rangeLabelText' => 'Rentang event aktif',
    'basisDateLabel' => 'Tanggal event komponen kas dan biaya',
    'basisDateNote' => 'Mode harian hanya menghitung event yang jatuh tepat pada hari itu. Tidak ada carry-forward ke hari berikutnya.',
    'noteText' => 'Laporan ini sekarang cash-based: uang masuk dikurangi refund, harga beli produk, biaya operasional, gaji, dan hutang karyawan.',
])

<div class="row g-3">
    <div class="col-12 col-md-6 col-xl-3">
        <div class="card"><div class="card-body">
            <div class="text-muted small">Uang Masuk</div>
            <div class="fs-5 fw-bold text-success">Rp {{ number_format($row['cash_in_rupiah'] ?? 0, 0, ',', '.') }}</div>
        </div></div>
    </div>

    <div class="col-12 col-md-6 col-xl-3">
        <div class="card"><div class="card-body">
            <div class="text-muted small">Refund</div>
            <div class="fs-5 fw-bold text-danger">Rp {{ number_format($row['refunded_rupiah'] ?? 0, 0, ',', '.') }}</div>
        </div></div>
    </div>

    <div class="col-12 col-md-6 col-xl-3">
        <div class="card"><div class="card-body">
            <div class="text-muted small">Pembelian External</div>
            <div class="fs-5 fw-bold">Rp {{ number_format($row['external_purchase_cost_rupiah'] ?? 0, 0, ',', '.') }}</div>
        </div></div>
    </div>

    <div class="col-12 col-md-6 col-xl-3">
        <div class="card"><div class="card-body">
            <div class="text-muted small">COGS Stok Toko</div>
            <div class="fs-5 fw-bold">Rp {{ number_format($row['store_stock_cogs_rupiah'] ?? 0, 0, ',', '.') }}</div>
        </div></div>
    </div>

    <div class="col-12 col-md-6 col-xl-3">
        <div class="card"><div class="card-body">
            <div class="text-muted small">Harga Beli Produk</div>
            <div class="fs-5 fw-bold text-danger">Rp {{ number_format($row['product_purchase_cost_rupiah'] ?? 0, 0, ',', '.') }}</div>
        </div></div>
    </div>

    <div class="col-12 col-md-6 col-xl-3">
        <div class="card"><div class="card-body">
            <div class="text-muted small">Biaya Operasional</div>
            <div class="fs-5 fw-bold text-danger">Rp {{ number_format($row['operational_expense_rupiah'] ?? 0, 0, ',', '.') }}</div>
        </div></div>
    </div>

    <div class="col-12 col-md-6 col-xl-3">
        <div class="card"><div class="card-body">
            <div class="text-muted small">Gaji</div>
            <div class="fs-5 fw-bold text-danger">Rp {{ number_format($row['payroll_disbursement_rupiah'] ?? 0, 0, ',', '.') }}</div>
        </div></div>
    </div>

    <div class="col-12 col-md-6 col-xl-3">
        <div class="card"><div class="card-body">
            <div class="text-muted small">Hutang Karyawan</div>
            <div class="fs-5 fw-bold text-danger">Rp {{ number_format($row['employee_debt_cash_out_rupiah'] ?? 0, 0, ',', '.') }}</div>
        </div></div>
    </div>

    <div class="col-12">
        <div class="card"><div class="card-body">
            <div class="text-muted small">Laba Kas Operasional</div>
            <div class="fs-3 fw-bold {{ ($row['cash_operational_profit_rupiah'] ?? 0) >= 0 ? 'text-success' : 'text-danger' }}">
                Rp {{ number_format($row['cash_operational_profit_rupiah'] ?? 0, 0, ',', '.') }}
            </div>
        </div></div>
    </div>
</div>
@endsection
