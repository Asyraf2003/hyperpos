@extends('layouts.app')

@section('title', $pageTitle)
@section('heading', $pageTitle)

@section('content')
<div class="page-content">
    <div class="row g-4 align-items-start">
        <div class="col-12 col-xl-8">
            <div class="d-flex flex-column gap-4">
                <section aria-labelledby="note-summary-heading">
                    <div class="mb-2">
                        <h2 id="note-summary-heading" class="h5 mb-1">Ringkasan Nota</h2>
                        <p class="text-muted mb-0">Informasi utama nota dan status pembayaran saat ini.</p>
                    </div>

                    @include('cashier.notes.partials.note-overview')
                </section>

                <section aria-labelledby="note-rows-heading">
                    <div class="mb-2">
                        <h2 id="note-rows-heading" class="h5 mb-1">Rincian Nota</h2>
                        <p class="text-muted mb-0">Daftar seluruh baris transaksi yang sudah tercatat pada nota ini.</p>
                    </div>

                    @include('cashier.notes.partials.note-rows-table')
                </section>

                <section aria-labelledby="note-corrections-heading">
                    <div class="mb-2">
                        <h2 id="note-corrections-heading" class="h5 mb-1">Koreksi Setelah Lunas</h2>
                        <p class="text-muted mb-0">Gunakan bagian ini hanya untuk koreksi pada nota yang sudah dibayar.</p>
                    </div>

                        @include('cashier.notes.partials.correction-actions')
                        @include('cashier.notes.partials.correction-history')
                </section>
            </div>
        </div>

        <div class="col-12 col-xl-4">
            <div class="d-flex flex-column gap-4">
                <section aria-labelledby="note-actions-heading">
                    <div class="mb-2">
                        <h2 id="note-actions-heading" class="h5 mb-1">Tindakan Nota</h2>
                        <p class="text-muted mb-0">Tambah rincian baru atau catat pembayaran untuk nota yang masih berjalan.</p>
                    </div>

                    @include('cashier.notes.partials.add-rows-form')
                    @include('cashier.notes.partials.payment-form')
                </section>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('assets/static/js/pages/cashier-note-add-rows.js') }}"></script>
<script src="{{ asset('assets/static/js/pages/cashier-note-payment.js') }}"></script>
@endpush
