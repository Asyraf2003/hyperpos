@extends('layouts.app')

@section('title', $pageTitle)
@section('heading', $pageTitle)
@section('back_url', route('cashier.notes.index'))

@section('content')
<div class="page-content">
    <div class="mb-4">
        <div class="small text-muted text-uppercase fw-semibold">Workspace Nota Kasir</div>
        <h3 class="mb-1">Panel Kerja Nota</h3>
        <div class="text-muted">
            Pilih tindakan berdasarkan status masing-masing line. Nota dibaca sebagai wadah, sedangkan operasi harian mengikuti line.
        </div>
    </div>

    <div class="row g-4 align-items-start">
        <div class="col-12 col-xl-8">
            <div class="d-flex flex-column gap-4">
                @include('cashier.notes.partials.note-overview')
                @include('cashier.notes.partials.note-rows-table')
                @include('cashier.notes.partials.correction-history')
            </div>
        </div>

        <div class="col-12 col-xl-4">
            <div class="d-flex flex-column gap-4">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title mb-1">Panel Tindakan Nota</h4>
                        <p class="mb-0 text-muted">
                            Area samping dipakai untuk tindakan lanjutan nota dan form transisi sesuai status line yang aktif.
                        </p>
                    </div>
                </div>

                @if ($note['can_show_workspace_panel'] ?? false)
                    @include('cashier.notes.partials.add-rows-form')
                @endif

                @if ($note['can_show_payment_form'])
                    @include('cashier.notes.partials.payment-form')
                @endif

                @if ($note['can_show_refund_form'] ?? false)
                    @include('cashier.notes.partials.refund-form')
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('assets/static/js/pages/cashier-note-payment.js') }}?v={{ filemtime(public_path('assets/static/js/pages/cashier-note-payment.js')) }}"></script>
<script src="{{ asset('assets/static/js/pages/cashier-note-refund.js') }}?v={{ filemtime(public_path('assets/static/js/pages/cashier-note-refund.js')) }}"></script>
@endpush
