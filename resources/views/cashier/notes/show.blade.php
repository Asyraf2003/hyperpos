@extends('layouts.app')

@section('title', $pageTitle)
@section('heading', $pageTitle)
@section('back_url', route('cashier.notes.index'))

@section('content')
<section class="section">
  <div class="ui-page-intro">
    <div class="small text-muted text-uppercase fw-semibold">Workspace Nota Kasir</div>
    <h4 class="ui-page-intro-title">Panel Kerja Nota</h4>
    <p class="ui-page-intro-subtitle">Detail nota dibaca sebagai ringkasan kerja. Aksi bayar dan refund dibuka lewat modal agar tabel line tetap bersih dan fokus.</p>
  </div>
  <div class="row g-4 align-items-start">
    <div class="col-12 col-xl-8">
      <div class="ui-card-stack">
        @include('cashier.notes.partials.note-overview')
        @include('cashier.notes.partials.note-rows-table')
        @include('cashier.notes.partials.correction-history')
      </div>
    </div>
    <div class="col-12 col-xl-4">
      <div class="ui-card-stack">
        <div class="card">
          <div class="card-header">
            <h4 class="card-title mb-1">Panel Tindakan Nota</h4>
            <p class="mb-0 text-muted">Aksi utama dibuka lewat launcher. Pilihan line dilakukan di dalam modal sesuai konteks aksi, bukan di tabel.</p>
          </div>
          <div class="card-body">
            <div class="d-grid gap-2">
              @if ($note['can_show_payment_form'] ?? false)
                <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#note-payment-modal">Buka Modal Bayar</button>
              @endif
              @if ($note['can_show_refund_form'] ?? false)
                <button type="button" class="btn btn-outline-warning" data-bs-toggle="modal" data-bs-target="#note-refund-modal">Buka Modal Refund</button>
              @endif
            </div>
            <div class="border rounded p-3 mt-3 bg-light">
              <div class="fw-semibold mb-1">Kontrak Aksi</div>
              <div class="small text-muted">Checklist line hanya muncul di modal aksi. Tabel line dipakai untuk membaca data dan memilih launcher yang sesuai.</div>
            </div>
          </div>
        </div>
        @if ($note['can_show_workspace_panel'] ?? false)
          @include('cashier.notes.partials.add-rows-form')
        @endif
      </div>
    </div>
  </div>
  @include('cashier.notes.partials.payment-modal')
  @include('cashier.notes.partials.refund-modal')
</section>
@endsection

@push('scripts')
<script src="{{ asset('assets/static/js/pages/cashier-note-payment.js') }}?v={{ filemtime(public_path('assets/static/js/pages/cashier-note-payment.js')) }}"></script>
<script src="{{ asset('assets/static/js/pages/cashier-note-refund.js') }}?v={{ filemtime(public_path('assets/static/js/pages/cashier-note-refund.js')) }}"></script>
@endpush
