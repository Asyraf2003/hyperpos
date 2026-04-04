@extends('layouts.app')

@section('title', $pageTitle)
@section('heading', $pageTitle)

@section('content')
<section class="section">
    <div class="card">
        <div class="card-header">
            <div class="d-flex flex-column flex-xl-row justify-content-between align-items-xl-center gap-3">
                <div>
                    <h4 class="card-title mb-1">Riwayat Nota Admin</h4>
                    <p class="mb-0 text-muted">
                        Workspace operasional admin untuk membuka nota, memantau status pembayaran,
                        melihat histori perubahan, dan menangani mode edit sesuai policy.
                    </p>
                </div>

                <div class="d-flex flex-column flex-md-row gap-2">
                    <form class="d-flex flex-column gap-1" id="admin-note-search-form">
                        <input
                            type="text"
                            id="admin-note-search-input"
                            class="form-control"
                            placeholder="Cari no nota, customer, no telp"
                            autocomplete="off"
                            value="{{ $filters['search'] }}"
                        >
                    </form>

                    <button type="button" id="open-admin-note-filter" class="btn btn-primary">
                        Filter
                    </button>
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
                                Sedang menyiapkan riwayat nota admin...
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mt-3">
                <small id="admin-note-table-summary" class="text-muted">
                    Memuat ringkasan riwayat admin...
                </small>
                <div id="admin-note-table-pagination"></div>
            </div>
        </div>
    </div>

    @include('admin.notes.partials.filter-drawer')
</section>

<script id="admin-note-index-config" type="application/json">@json([
    'endpoint' => route('admin.notes.table'),
    'filters' => $filters,
])</script>
@push('scripts')
<script src="{{ asset('assets/static/js/pages/admin-note-index.js') }}?v={{ filemtime(public_path('assets/static/js/pages/admin-note-index.js')) }}"></script>
@endpush

@endsection
