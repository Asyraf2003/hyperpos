@extends('layouts.app')

@section('title', $pageTitle)
@section('heading', $pageTitle)

@section('content')
<section class="section">
    <div class="card">
        <div class="card-header">
            <div class="d-flex flex-column gap-3">
                <div class="d-flex flex-column flex-xl-row justify-content-between align-items-xl-center gap-3">
                    <div>
                        <h4 class="card-title mb-1">Riwayat Nota Kasir</h4>
                        <p class="mb-0 text-muted">
                            Menampilkan nota dalam window kasir hari ini dan kemarin.
                        </p>
                    </div>

                    <div class="d-flex flex-column flex-md-row gap-2">
                        <form class="d-flex flex-column gap-1" id="cashier-note-search-form">
                            <input
                                type="text"
                                id="cashier-note-search-input"
                                class="form-control"
                                placeholder="Cari no nota, nama customer, atau no telp"
                                autocomplete="off"
                                value="{{ $filters['search'] }}"
                            >
                        </form>

                        <a href="{{ route('cashier.notes.workspace.create') }}" class="btn btn-primary">
                            Buat Nota
                        </a>
                    </div>
                </div>

                <div class="row g-3">
                    <div class="col-md-4">
                        <label for="cashier-note-date" class="form-label">Tanggal Acuan</label>
                        <input
                            type="date"
                            id="cashier-note-date"
                            class="form-control"
                            value="{{ $filters['date'] }}"
                            data-ui-date="single"
                        >
                        <div class="form-text">Default riwayat kasir dimulai dari tanggal hari ini.</div>
                    </div>

                    <div class="col-md-4">
                        <label for="cashier-note-payment-status" class="form-label">Status Pembayaran</label>
                        <select id="cashier-note-payment-status" class="form-select">
                            <option value="" @selected($filters['payment_status'] === '')>Semua Status</option>
                            <option value="unpaid" @selected($filters['payment_status'] === 'unpaid')>Belum Dibayar</option>
                            <option value="partial" @selected($filters['payment_status'] === 'partial')>Dibayar Sebagian</option>
                            <option value="paid" @selected($filters['payment_status'] === 'paid')>Lunas</option>
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label for="cashier-note-work-status" class="form-label">Status Pengerjaan</label>
                        <select id="cashier-note-work-status" class="form-select">
                            <option value="" @selected($filters['work_status'] === '')>Semua Status</option>
                            <option value="open" @selected($filters['work_status'] === 'open')>Open</option>
                            <option value="done" @selected($filters['work_status'] === 'done')>Selesai</option>
                            <option value="canceled" @selected($filters['work_status'] === 'canceled')>Batal</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-lg" id="cashier-note-table">
                    <thead>
                        <tr class="text-nowrap">
                            <th style="width: 64px;">No</th>
                            <th>Tanggal</th>
                            <th>No Nota</th>
                            <th>Customer</th>
                            <th class="text-end">Grand Total</th>
                            <th class="text-end">Sudah Dibayar</th>
                            <th class="text-end">Sisa Tagihan</th>
                            <th>Status Bayar</th>
                            <th>Ringkasan Pengerjaan</th>
                            <th style="width: 120px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="cashier-note-table-body">
                        <tr>
                            <td colspan="10" class="text-center text-muted py-4">
                                Skeleton riwayat kasir siap. Data akan dihubungkan pada slice berikutnya.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mt-3">
                <small id="cashier-note-table-summary" class="text-muted">
                    Scope default: window kasir hari ini dan kemarin.
                </small>
                <div id="cashier-note-table-pagination"></div>
            </div>
        </div>
    </div>
</section>

<script id="cashier-note-index-config" type="application/json">@json([
    'endpoint' => route('cashier.notes.table'),
    'filters' => $filters,
])</script>
@push('scripts')
<script src="{{ asset('assets/static/js/pages/cashier-note-index.js') }}"></script>
@endpush

@endsection
