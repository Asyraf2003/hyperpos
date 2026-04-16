@extends('layouts.app')
@include('layouts.partials.date-picker-assets')

@section('title', 'Hutang Supplier')
@section('heading', 'Hutang Supplier')

@section('content')
@include('admin.reporting.partials.period_filter', [
    'formId' => 'supplier-payable-report-filter-form',
    'action' => route('admin.reports.supplier_payable.index'),
    'resetUrl' => route('admin.reports.supplier_payable.index'),
    'rangeLabelText' => 'Rentang pengiriman aktif',
    'basisDateLabel' => 'Tanggal pengiriman invoice',
    'basisDateNote' => 'Data invoice masuk dihitung dari tanggal pengiriman. Status jatuh tempo dievaluasi terhadap tanggal referensi.',
])

<div class="row g-3 mb-4">
    <div class="col-12 col-md-6 col-xl-2">
        <div class="card">
            <div class="card-body">
                <div class="text-muted small">Total Invoice</div>
                <div class="fs-5 fw-bold">{{ number_format($summary['total_rows'] ?? 0, 0, ',', '.') }}</div>
            </div>
        </div>
    </div>

    <div class="col-12 col-md-6 col-xl-2">
        <div class="card">
            <div class="card-body">
                <div class="text-muted small">Grand Total</div>
                <div class="fs-5 fw-bold">Rp {{ number_format($summary['grand_total_rupiah'] ?? 0, 0, ',', '.') }}</div>
            </div>
        </div>
    </div>

    <div class="col-12 col-md-6 col-xl-2">
        <div class="card">
            <div class="card-body">
                <div class="text-muted small">Total Dibayar</div>
                <div class="fs-5 fw-bold text-success">Rp {{ number_format($summary['total_paid_rupiah'] ?? 0, 0, ',', '.') }}</div>
            </div>
        </div>
    </div>

    <div class="col-12 col-md-6 col-xl-2">
        <div class="card">
            <div class="card-body">
                <div class="text-muted small">Outstanding</div>
                <div class="fs-5 fw-bold text-danger">Rp {{ number_format($summary['outstanding_rupiah'] ?? 0, 0, ',', '.') }}</div>
            </div>
        </div>
    </div>

    <div class="col-12 col-md-6 col-xl-2">
        <div class="card">
            <div class="card-body">
                <div class="text-muted small">Open</div>
                <div class="fs-5 fw-bold">{{ number_format($summary['open_rows'] ?? 0, 0, ',', '.') }}</div>
            </div>
        </div>
    </div>

    <div class="col-12 col-md-6 col-xl-2">
        <div class="card">
            <div class="card-body">
                <div class="text-muted small">Settled</div>
                <div class="fs-5 fw-bold">{{ number_format($summary['settled_rows'] ?? 0, 0, ',', '.') }}</div>
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
                                <th class="text-end">Invoice</th>
                                <th class="text-end">Outstanding</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($periodRows as $row)
                                <tr>
                                    <td>{{ $row['period_label'] }}</td>
                                    <td class="text-end">{{ number_format($row['total_rows'], 0, ',', '.') }}</td>
                                    <td class="text-end">Rp {{ number_format($row['outstanding_rupiah'], 0, ',', '.') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-center text-muted">Belum ada invoice pada periode ini.</td>
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
                <h5 class="card-title mb-3">Breakdown Supplier</h5>

                <div class="table-responsive">
                    <table class="table table-sm align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Supplier ID</th>
                                <th class="text-end">Invoice</th>
                                <th class="text-end">Outstanding</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($supplierRows as $row)
                                <tr>
                                    <td>{{ $row['supplier_id'] }}</td>
                                    <td class="text-end">{{ number_format($row['total_rows'], 0, ',', '.') }}</td>
                                    <td class="text-end">Rp {{ number_format($row['outstanding_rupiah'], 0, ',', '.') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-center text-muted">Belum ada supplier pada periode ini.</td>
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
                <h5 class="card-title mb-3">Detail Hutang Supplier</h5>

                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Invoice ID</th>
                                <th>Supplier ID</th>
                                <th>Shipment</th>
                                <th>Due Date</th>
                                <th class="text-end">Grand Total</th>
                                <th class="text-end">Dibayar</th>
                                <th class="text-end">Outstanding</th>
                            </tr>
                        </thead>
                        <tbody id="supplier-payable-report-table-body">
                            @forelse ($rows as $row)
                                <tr>
                                    <td>{{ $row['supplier_invoice_id'] }}</td>
                                    <td>{{ $row['supplier_id'] }}</td>
                                    <td>{{ $row['shipment_date'] }}</td>
                                    <td>{{ $row['due_date'] }}</td>
                                    <td class="text-end">Rp {{ number_format($row['grand_total_rupiah'], 0, ',', '.') }}</td>
                                    <td class="text-end">Rp {{ number_format($row['total_paid_rupiah'], 0, ',', '.') }}</td>
                                    <td class="text-end">Rp {{ number_format($row['outstanding_rupiah'], 0, ',', '.') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center text-muted">Belum ada invoice pada periode ini.</td>
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
