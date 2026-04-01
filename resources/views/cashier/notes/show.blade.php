@extends('layouts.app')

@section('title', $pageTitle)
@section('heading', $pageTitle)

@section('content')
<div class="page-content">
    <div class="d-flex flex-column gap-4">
    <section aria-labelledby="note-summary-heading">
        <div class="mb-2">
            <h2 id="note-summary-heading" class="h5 mb-1">Ringkasan Nota</h2>
            <p class="text-muted mb-0">Informasi utama nota dan status pembayaran saat ini.</p>
        </div>

        @include('cashier.notes.partials.note-overview')
    </section>

    <section aria-labelledby="note-actions-heading">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <h2 id="note-actions-heading" class="h5 mb-1">Tindakan Nota</h2>
                <p class="text-muted mb-0">Tambah rincian baru atau catat pembayaran untuk nota yang masih berjalan.</p>
            </div>
        </div>

        @include('cashier.notes.partials.add-rows-form')
        @include('cashier.notes.partials.payment-form')
    </section>

    <section aria-labelledby="note-corrections-heading">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <h2 id="note-corrections-heading" class="h5 mb-1">Koreksi Setelah Lunas</h2>
                <p class="text-muted mb-0">Gunakan bagian ini hanya untuk koreksi pada nota yang sudah dibayar.</p>
            </div>
        </div>

        @include('cashier.notes.partials.correction-actions')
        @include('cashier.notes.partials.correction-history')
    </section>

    <section aria-labelledby="note-rows-heading">
        <div class="mb-2">
            <h2 id="note-rows-heading" class="h5 mb-1">Rincian Nota</h2>
            <p class="text-muted mb-0">Daftar seluruh baris transaksi yang sudah tercatat pada nota ini.</p>
        </div>

        @include('cashier.notes.partials.note-rows-table')
    </section>
</div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('assets/static/js/pages/cashier-note-add-rows.js') }}"></script>
<script src="{{ asset('assets/static/js/pages/cashier-note-payment.js') }}"></script>
@endpush
