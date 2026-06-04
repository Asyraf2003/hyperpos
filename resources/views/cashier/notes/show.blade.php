@extends('layouts.app')

@section('title', $pageTitle)
@section('heading', $pageTitle)
@section('back_url', route('cashier.notes.index'))

@section('content')
<section class="section">
  <style>
    .cashier-note-detail {
      --detail-card: #ffffff;
      --detail-border: rgba(15, 23, 42, .10);
      --detail-muted: #64748b;
      --detail-text: #0f172a;
      --detail-primary-soft: rgba(var(--bs-primary-rgb), .10);
      --detail-primary-border: rgba(var(--bs-primary-rgb), .24);
      --detail-shadow: 0 .85rem 1.8rem rgba(15, 23, 42, .06);
      max-width: 860px;
      margin: 0 auto;
    }

    .cashier-note-detail-shell {
      display: grid;
      gap: 1rem;
    }

    .cashier-note-detail-step {
      border: 1px solid var(--detail-border);
      border-radius: 1rem;
      background: var(--detail-card);
      box-shadow: var(--detail-shadow);
      overflow: visible;
    }

    .cashier-note-detail-header {
      display: flex;
      align-items: flex-start;
      gap: .85rem;
      padding: 1rem 1rem .75rem;
      border-bottom: 1px solid rgba(15, 23, 42, .07);
    }

    .cashier-note-detail-number {
      width: 2.25rem;
      height: 2.25rem;
      flex: 0 0 2.25rem;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      border-radius: 999px;
      color: var(--bs-primary);
      background: var(--detail-primary-soft);
      border: 1px solid var(--detail-primary-border);
      font-weight: 800;
    }

    .cashier-note-detail-title {
      margin: 0;
      color: var(--detail-text);
      font-size: 1rem;
      font-weight: 800;
      line-height: 1.35;
    }

    .cashier-note-detail-help {
      margin: .18rem 0 0;
      color: var(--detail-muted);
      font-size: .9rem;
      line-height: 1.55;
    }

    .cashier-note-detail-body {
      padding: 1rem;
    }

    .cashier-note-detail .ui-card-stack {
      gap: 1rem;
    }

    .cashier-note-detail .card {
      border-color: rgba(15, 23, 42, .08);
      border-radius: .9rem;
      box-shadow: none;
      overflow: hidden;
    }

    .cashier-note-detail .table-responsive {
      border-radius: .85rem;
      -webkit-overflow-scrolling: touch;
    }

    .cashier-note-detail .btn {
      min-height: 2.75rem;
      border-radius: .85rem;
      font-weight: 800;
    }

    @media (max-width: 575.98px) {
      .cashier-note-detail {
        max-width: none;
      }

      .cashier-note-detail-header,
      .cashier-note-detail-body {
        padding-inline: .9rem;
      }
    }
  </style>

  <div class="cashier-note-detail">
    <div class="ui-page-intro">
      <div class="small text-muted text-uppercase fw-semibold">Workspace Nota Kasir</div>
      <h4 class="ui-page-intro-title">Detail Nota Root + Revision</h4>
      <p class="ui-page-intro-subtitle">Baca konteks nota, cek line, lalu pilih aksi pembayaran atau refund.</p>
    </div>

    <div class="cashier-note-detail-shell">
      <div class="cashier-note-detail-step">
        <div class="cashier-note-detail-header">
          <span class="cashier-note-detail-number">1</span>
          <div>
            <h5 class="cashier-note-detail-title">Konteks Nota</h5>
            <p class="cashier-note-detail-help">Identitas, ringkasan angka, dan status revision saat ini.</p>
          </div>
        </div>

        <div class="cashier-note-detail-body">
          <div class="ui-card-stack">
            @include('cashier.notes.partials.note-overview')
            @include('cashier.notes.partials.note-revision-timeline', [
              'revision' => $note['revision_timeline'] ?? ['current' => [], 'baseline' => [], 'timeline' => []],
              'currentRevision' => ($note['revision_timeline']['current'] ?? []),
              'baselineRevision' => ($note['revision_timeline']['baseline'] ?? []),
              'timelineRevisions' => ($note['revision_timeline']['timeline'] ?? []),
            ])
            @include('cashier.notes.partials.correction-history')
          </div>
        </div>
      </div>

      <div class="cashier-note-detail-step">
        <div class="cashier-note-detail-header">
          <span class="cashier-note-detail-number">2</span>
          <div>
            <h5 class="cashier-note-detail-title">Line & Billing</h5>
            <p class="cashier-note-detail-help">Klik line yang eligible untuk menyiapkan refund; billing dipakai untuk pembayaran.</p>
          </div>
        </div>

        <div class="cashier-note-detail-body">
          <div class="ui-card-stack">
            @include('cashier.notes.partials.note-rows-table')
            @include('cashier.notes.partials.billing-table')
          </div>
        </div>
      </div>

      <div class="cashier-note-detail-step">
        <div class="cashier-note-detail-header">
          <span class="cashier-note-detail-number">3</span>
          <div>
            <h5 class="cashier-note-detail-title">Aksi Nota</h5>
            <p class="cashier-note-detail-help">Lanjut edit, bayar, atau refund sesuai status note dan line terpilih.</p>
          </div>
        </div>

        <div class="cashier-note-detail-body">
          <div class="ui-card-stack">
            @include('cashier.notes.partials.payment-actions')

            @if ($note['can_show_workspace_panel'] ?? false)
              @include('cashier.notes.partials.add-rows-form')
            @endif
          </div>
        </div>
      </div>
    </div>
  </div>

  @include('cashier.notes.partials.payment-modal')
  @include('cashier.notes.partials.refund-modal')
</section>
@endsection

@push('scripts')
<script src="{{ asset('assets/static/js/pages/cashier-note-payment.js') }}?v={{ config('app.asset_version') }}"></script>
<script src="{{ asset('assets/static/js/pages/cashier-note-refund.js') }}?v={{ config('app.asset_version') }}"></script>
@endpush
