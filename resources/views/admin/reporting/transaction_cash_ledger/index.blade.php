@extends('layouts.app')
@include('layouts.partials.date-picker-assets')

@section('title', 'Arus Kas Transaksi')
@section('heading', 'Arus Kas Transaksi')

@section('content')
@include('admin.reporting.partials.period_filter', [
    'formId' => 'transaction-cash-ledger-filter-form',
    'action' => route('admin.reports.transaction_cash_ledger.index'),
    'resetUrl' => route('admin.reports.transaction_cash_ledger.index'),
    'rangeLabelText' => 'Rentang event aktif',
    'basisDateLabel' => 'Tanggal event kas',
    'basisDateNote' => 'Mode harian hanya menghitung event kas pada tanggal tersebut, bukan akumulasi hari sebelumnya.',
])

<div class="row g-3 mb-4">
    <div class="col-12 col-md-6 col-xl-3">
        <div class="card">
            <div class="card-body">
                <div class="text-muted small">Total Event</div>
                <div class="fs-4 fw-bold">{{ number_format($summary['total_events'], 0, ',', '.') }}</div>
            </div>
        </div>
    </div>

    <div class="col-12 col-md-6 col-xl-3">
        <div class="card">
            <div class="card-body">
                <div class="text-muted small">Cash In</div>
                <div class="fs-4 fw-bold text-success">Rp {{ number_format($summary['total_cash_in_rupiah'], 0, ',', '.') }}</div>
            </div>
        </div>
    </div>

    <div class="col-12 col-md-6 col-xl-3">
        <div class="card">
            <div class="card-body">
                <div class="text-muted small">Cash Out</div>
                <div class="fs-4 fw-bold text-danger">Rp {{ number_format($summary['total_cash_out_rupiah'], 0, ',', '.') }}</div>
            </div>
        </div>
    </div>

    <div class="col-12 col-md-6 col-xl-3">
        <div class="card">
            <div class="card-body">
                <div class="text-muted small">Net Amount</div>
                <div class="fs-4 fw-bold {{ $summary['net_amount_rupiah'] >= 0 ? 'text-primary' : 'text-danger' }}">
                    Rp {{ number_format($summary['net_amount_rupiah'], 0, ',', '.') }}
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3">
    <div class="col-12 col-xl-4">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title mb-3">Agregasi Per Tanggal</h5>

                <div class="table-responsive">
                    <table class="table table-sm align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Tanggal</th>
                                <th class="text-end">Event</th>
                                <th class="text-end">Net</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($periodRows as $row)
                                <tr>
                                    <td>{{ $row['period_label'] }}</td>
                                    <td class="text-end">{{ number_format($row['total_events'], 0, ',', '.') }}</td>
                                    <td class="text-end">Rp {{ number_format($row['net_amount_rupiah'], 0, ',', '.') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-center text-muted">Belum ada event kas pada periode ini.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12 col-xl-8">
        <div class="card h-100">
            <div class="card-body">
                <h5 class="card-title mb-3">Detail Event Kas</h5>

                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Tanggal Event</th>
                                <th>Nota</th>
                                <th>Jenis Event</th>
                                <th>Arah</th>
                                <th class="text-end">Nominal</th>
                                <th>Payment</th>
                                <th>Refund</th>
                            </tr>
                        </thead>
                        <tbody id="transaction-cash-ledger-table-body">
                            @forelse ($rows as $row)
                                <tr>
                                    <td>{{ $row['event_date'] }}</td>
                                    <td>{{ $row['note_label'] ?? $row['note_id'] }}</td>
                                    <td>{{ $row['event_type'] }}</td>
                                    <td>
                                        <span class="badge {{ $row['direction'] === 'in' ? 'bg-success' : 'bg-danger' }}">
                                            {{ $row['direction'] }}
                                        </span>
                                    </td>
                                    <td class="text-end">Rp {{ number_format($row['event_amount_rupiah'], 0, ',', '.') }}</td>
                                    <td>{{ ($row['customer_payment_id'] ?? null) ? 'Ada' : '-' }}</td>
                                    <td>{{ ($row['refund_id'] ?? null) ? 'Ada' : '-' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center text-muted">Belum ada event kas pada periode ini.</td>
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
