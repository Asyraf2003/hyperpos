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
                        <h4 class="card-title mb-1">Riwayat Nota Admin</h4>
                        <p class="mb-0 text-muted">
                            Workspace operasional admin untuk membuka nota, memantau status pembayaran,
                            melihat histori perubahan, dan nanti menangani editability sesuai policy.
                        </p>
                    </div>

                    <div class="d-flex flex-wrap gap-2">
                        <span class="badge bg-light-secondary text-dark">Workspace Admin</span>
                    </div>
                </div>

                <div class="row g-3">
                    <div class="col-md-4">
                        <label for="admin-note-date-from" class="form-label">Tanggal Mulai</label>
                        <input
                            type="date"
                            id="admin-note-date-from"
                            class="form-control"
                            data-ui-date="single"
                            value="{{ $filters['date_from'] }}"
                        >
                    </div>

                    <div class="col-md-4">
                        <label for="admin-note-date-to" class="form-label">Tanggal Akhir</label>
                        <input
                            type="date"
                            id="admin-note-date-to"
                            class="form-control"
                            data-ui-date="single"
                            value="{{ $filters['date_to'] }}"
                        >
                    </div>

                    <div class="col-md-4">
                        <label for="admin-note-search-input" class="form-label">Pencarian</label>
                        <input
                            type="text"
                            id="admin-note-search-input"
                            class="form-control"
                            placeholder="Cari no nota, customer, no telp"
                            autocomplete="off"
                            value="{{ $filters['search'] }}"
                        >
                    </div>

                    <div class="col-md-4">
                        <label for="admin-note-payment-status" class="form-label">Status Pembayaran</label>
                        <select id="admin-note-payment-status" class="form-select">
                            <option value="" @selected($filters['payment_status'] === '')>Semua Status</option>
                            <option value="unpaid" @selected($filters['payment_status'] === 'unpaid')>Belum Dibayar</option>
                            <option value="partial" @selected($filters['payment_status'] === 'partial')>Dibayar Sebagian</option>
                            <option value="paid" @selected($filters['payment_status'] === 'paid')>Lunas</option>
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label for="admin-note-editability" class="form-label">Mode Edit</label>
                        <select id="admin-note-editability" class="form-select">
                            <option value="" @selected($filters['editability'] === '')>Semua Mode</option>
                            <option value="editable_normal" @selected($filters['editability'] === 'editable_normal')>Editable Normal</option>
                            <option value="admin_strict" @selected($filters['editability'] === 'admin_strict')>Admin Ketat</option>
                            <option value="correction_only" @selected($filters['editability'] === 'correction_only')>Correction Only</option>
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label for="admin-note-work-summary" class="form-label">Ringkasan Pengerjaan</label>
                        <select id="admin-note-work-summary" class="form-select">
                            <option value="" @selected($filters['work_summary'] === '')>Semua Ringkasan</option>
                            <option value="has_open" @selected($filters['work_summary'] === 'has_open')>Ada Open</option>
                            <option value="has_done" @selected($filters['work_summary'] === 'has_done')>Ada Selesai</option>
                            <option value="has_canceled" @selected($filters['work_summary'] === 'has_canceled')>Ada Batal</option>
                        </select>
                    </div>
                </div>

                <div class="alert alert-light-secondary mb-0">
                    Admin tidak membuat transaksi dari halaman ini. Slice berikutnya akan menghubungkan
                    filter, daftar nota, histori perubahan, dan aksi admin sesuai policy edit.
                </div>
            </div>
        </div>

        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-lg" id="admin-note-table">
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
                            <th>Mode Edit</th>
                            <th style="width: 120px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="admin-note-table-body">
                        <tr>
                            <td colspan="11" class="text-center text-muted py-4">
                                Skeleton riwayat admin siap. Data akan dihubungkan pada slice berikutnya.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mt-3">
                <small id="admin-note-table-summary" class="text-muted">
                    Scope admin: riwayat operasional note terpisah dari laporan.
                </small>
                <div id="admin-note-table-pagination"></div>
            </div>
        </div>
    </div>
</section>

<script id="admin-note-index-config" type="application/json">@json([
    'filters' => $filters,
])</script>
@push('scripts')
<script src="{{ asset('assets/static/js/pages/admin-note-index.js') }}"></script>
@endpush

@endsection
