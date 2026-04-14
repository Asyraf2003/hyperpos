@extends('layouts.app')
@include('layouts.partials.date-picker-assets')

@section('title', 'Stok dan Nilai Persediaan')
@section('heading', 'Stok dan Nilai Persediaan')

@section('content')
@include('admin.reporting.partials.period_filter', [
    'formId' => 'inventory-stock-value-report-filter-form',
    'action' => route('admin.reports.inventory_stock_value.index'),
    'resetUrl' => route('admin.reports.inventory_stock_value.index'),
    'rangeLabelText' => 'Rentang movement aktif',
])

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
