@extends('layouts.app')

@section('title', 'Arus Kas Transaksi')
@section('heading', 'Arus Kas Transaksi')

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
                    action="{{ route('admin.reports.transaction_cash_ledger.index') }}"
                    class="row g-3"
                    id="transaction-cash-ledger-filter-form"
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
                        <a href="{{ route('admin.reports.transaction_cash_ledger.index') }}" class="btn btn-outline-secondary">
                            Reset
                        </a>
                    </div>
                </form>

                <div class="alert alert-light border mt-3 mb-0">
                    Screen ini memakai handler report existing.
                    Rentang aktif: <strong>{{ $filters['range_label'] }}</strong>
                </div>
            </div>
        </div>
    </div>
</div>

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
                                <th>Note</th>
                                <th>Jenis Event</th>
                                <th>Arah</th>
                                <th class="text-end">Nominal</th>
                                <th>Payment ID</th>
                                <th>Refund ID</th>
                            </tr>
                        </thead>
                        <tbody id="transaction-cash-ledger-table-body">
                            @forelse ($rows as $row)
                                <tr>
                                    <td>{{ $row['event_date'] }}</td>
                                    <td>{{ $row['note_id'] }}</td>
                                    <td>{{ $row['event_type'] }}</td>
                                    <td>
                                        <span class="badge {{ $row['direction'] === 'in' ? 'bg-success' : 'bg-danger' }}">
                                            {{ $row['direction'] }}
                                        </span>
                                    </td>
                                    <td class="text-end">Rp {{ number_format($row['event_amount_rupiah'], 0, ',', '.') }}</td>
                                    <td>{{ $row['customer_payment_id'] ?? '-' }}</td>
                                    <td>{{ $row['refund_id'] ?? '-' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center text-muted">Belum ada event kas pada periode ini.</td>
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
