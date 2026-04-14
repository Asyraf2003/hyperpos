@extends('layouts.app')

@section('title', 'Stok dan Nilai Persediaan')
@section('heading', 'Stok dan Nilai Persediaan')

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
                    action="{{ route('admin.reports.inventory_stock_value.index') }}"
                    class="row g-3"
                    id="inventory-stock-value-report-filter-form"
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
                        <a href="{{ route('admin.reports.inventory_stock_value.index') }}" class="btn btn-outline-secondary">
                            Reset
                        </a>
                    </div>
                </form>

                <div class="alert alert-light border mt-3 mb-0">
                    Rentang movement aktif: <strong>{{ $filters['range_label'] }}</strong>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-12 col-md-6 col-xl-2">
        <div class="card"><div class="card-body">
            <div class="text-muted small">Produk Snapshot</div>
            <div class="fs-5 fw-bold">{{ number_format($summary['snapshot_product_rows'] ?? 0, 0, ',', '.') }}</div>
        </div></div>
    </div>

    <div class="col-12 col-md-6 col-xl-2">
        <div class="card"><div class="card-body">
            <div class="text-muted small">Produk Movement</div>
            <div class="fs-5 fw-bold">{{ number_format($summary['movement_product_rows'] ?? 0, 0, ',', '.') }}</div>
        </div></div>
    </div>

    <div class="col-12 col-md-6 col-xl-2">
        <div class="card"><div class="card-body">
            <div class="text-muted small">Qty On Hand</div>
            <div class="fs-5 fw-bold">{{ number_format($summary['total_qty_on_hand'] ?? 0, 0, ',', '.') }}</div>
        </div></div>
    </div>

    <div class="col-12 col-md-6 col-xl-2">
        <div class="card"><div class="card-body">
            <div class="text-muted small">Nilai Persediaan</div>
            <div class="fs-5 fw-bold">Rp {{ number_format($summary['total_inventory_value_rupiah'] ?? 0, 0, ',', '.') }}</div>
        </div></div>
    </div>

    <div class="col-12 col-md-6 col-xl-2">
        <div class="card"><div class="card-body">
            <div class="text-muted small">Qty In Periode</div>
            <div class="fs-5 fw-bold text-success">{{ number_format($summary['period_qty_in'] ?? 0, 0, ',', '.') }}</div>
        </div></div>
    </div>

    <div class="col-12 col-md-6 col-xl-2">
        <div class="card"><div class="card-body">
            <div class="text-muted small">Qty Out Periode</div>
            <div class="fs-5 fw-bold text-danger">{{ number_format($summary['period_qty_out'] ?? 0, 0, ',', '.') }}</div>
        </div></div>
    </div>

    <div class="col-12 col-md-6 col-xl-3">
        <div class="card"><div class="card-body">
            <div class="text-muted small">Net Qty Periode</div>
            <div class="fs-5 fw-bold">{{ number_format($summary['period_net_qty_delta'] ?? 0, 0, ',', '.') }}</div>
        </div></div>
    </div>

    <div class="col-12 col-md-6 col-xl-3">
        <div class="card"><div class="card-body">
            <div class="text-muted small">Net Cost Periode</div>
            <div class="fs-5 fw-bold">Rp {{ number_format($summary['period_net_cost_delta_rupiah'] ?? 0, 0, ',', '.') }}</div>
        </div></div>
    </div>
</div>

<div class="row g-3">
    <div class="col-12 col-xl-6">
        <div class="card h-100">
            <div class="card-body">
                <h5 class="card-title mb-3">Snapshot Stok Saat Ini</h5>

                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Product ID</th>
                                <th>Kode</th>
                                <th>Nama</th>
                                <th class="text-end">Qty</th>
                                <th class="text-end">Avg Cost</th>
                                <th class="text-end">Inventory Value</th>
                            </tr>
                        </thead>
                        <tbody id="inventory-stock-value-snapshot-table-body">
                            @forelse ($snapshotRows as $row)
                                <tr>
                                    <td>{{ $row['product_id'] }}</td>
                                    <td>{{ $row['kode_barang'] ?? '-' }}</td>
                                    <td>{{ $row['nama_barang'] }}</td>
                                    <td class="text-end">{{ number_format($row['current_qty_on_hand'], 0, ',', '.') }}</td>
                                    <td class="text-end">Rp {{ number_format($row['current_avg_cost_rupiah'], 0, ',', '.') }}</td>
                                    <td class="text-end">Rp {{ number_format($row['current_inventory_value_rupiah'], 0, ',', '.') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center text-muted">Belum ada snapshot inventory.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12 col-xl-6">
        <div class="card h-100">
            <div class="card-body">
                <h5 class="card-title mb-3">Movement Summary Periode</h5>

                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Product ID</th>
                                <th class="text-end">Qty In</th>
                                <th class="text-end">Qty Out</th>
                                <th class="text-end">Net Qty</th>
                                <th class="text-end">Net Cost</th>
                            </tr>
                        </thead>
                        <tbody id="inventory-stock-value-movement-table-body">
                            @forelse ($movementRows as $row)
                                <tr>
                                    <td>{{ $row['product_id'] }}</td>
                                    <td class="text-end">{{ number_format($row['qty_in'], 0, ',', '.') }}</td>
                                    <td class="text-end">{{ number_format($row['qty_out'], 0, ',', '.') }}</td>
                                    <td class="text-end">{{ number_format($row['net_qty_delta'], 0, ',', '.') }}</td>
                                    <td class="text-end">Rp {{ number_format($row['net_cost_delta_rupiah'], 0, ',', '.') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted">Belum ada movement pada periode ini.</td>
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
