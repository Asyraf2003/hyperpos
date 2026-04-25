<div class="card">
  <div class="card-header">
    <h4 class="card-title mb-0">Status & Aksi Nota</h4>
  </div>

  <div class="card-body">
    <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
      <span class="text-muted">Grand Total</span>
      <strong>{{ number_format($note['grand_total_rupiah'], 0, ',', '.') }}</strong>
    </div>

    <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
      <span class="text-muted">Sudah Dibayar</span>
      <strong>{{ number_format($note['net_paid_rupiah'], 0, ',', '.') }}</strong>
    </div>

    <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
      <span class="text-muted">Total Refund</span>
      <strong>{{ number_format($note['total_refunded_rupiah'], 0, ',', '.') }}</strong>
    </div>

    <div class="d-flex justify-content-between align-items-center py-3 border-bottom">
      <span class="fw-semibold">Sisa Tagihan</span>
      <strong class="fs-5">{{ number_format($note['outstanding_rupiah'], 0, ',', '.') }}</strong>
    </div>

    <div class="border rounded p-3 bg-light mb-3">
      <div class="small text-muted mb-1">Status Operasional</div>
      <div class="fw-bold text-uppercase">{{ $note['payment_status_label'] ?? '-' }}</div>
    </div>

    @if ($note['can_show_payment_form'] ?? false)
      <div class="d-grid gap-2">
        @if ($note['can_show_partial_payment_action'] ?? false)
          <button
            type="button"
            class="btn btn-primary js-open-payment-intent"
            data-bs-toggle="modal"
            data-bs-target="#note-payment-modal"
            data-payment-intent="pay"
            data-payment-preset="manual"
          >
            Bayar Sebagian
          </button>
        @endif

        @if ($note['can_show_settle_payment_action'] ?? false)
          <button
            type="button"
            class="btn btn-outline-primary js-open-payment-intent"
            data-bs-toggle="modal"
            data-bs-target="#note-payment-modal"
            data-payment-intent="settle"
            data-payment-preset="manual"
          >
            Lunasi
          </button>
        @endif
      </div>
    @endif
  </div>
</div>
