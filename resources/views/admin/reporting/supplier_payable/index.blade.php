@extends('layouts.app')

@section('title', 'Hutang Supplier')
@section('heading', 'Hutang Supplier')

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
                    action="{{ route('admin.reports.supplier_payable.index') }}"
                    class="row g-3"
                    id="supplier-payable-report-filter-form"
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
                        <a href="{{ route('admin.reports.supplier_payable.index') }}" class="btn btn-outline-secondary">
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
