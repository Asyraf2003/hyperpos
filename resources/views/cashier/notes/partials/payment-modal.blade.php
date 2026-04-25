@if ($note['can_show_payment_form'] ?? false)
<div
  class="modal fade"
  id="note-payment-modal"
  tabindex="-1"
  aria-hidden="true"
  data-total-rupiah="{{ (int) ($note['grand_total_rupiah'] ?? 0) }}"
  data-outstanding-rupiah="{{ (int) ($note['outstanding_rupiah'] ?? 0) }}"
>
  <div class="modal-dialog modal-xl modal-dialog-centered" id="note-payment-modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <div>
          <h5 class="modal-title mb-1" id="note-payment-modal-title">Proses Pembayaran</h5>
          <p class="mb-0 text-muted small" id="note-payment-modal-subtitle">
            Pilih transfer atau cash. Sistem otomatis memilih tagihan aktif yang masih outstanding.
          </p>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
      </div>

      <form method="POST" action="{{ $paymentModalConfig['action'] ?? $paymentAction }}" id="note-payment-form">
        @csrf
        <input type="hidden" name="payment_scope" value="partial">
        <input type="hidden" name="payment_method" id="detail-payment-method" value="">
        <input type="hidden" name="paid_at" id="detail-payment-paid-at" value="{{ $paymentModalConfig['date_default'] ?? $paymentDateDefault }}">
        <input type="hidden" name="amount_paid" id="detail-payment-amount-paid" value="">
        <input type="hidden" name="amount_received" id="detail-payment-amount-received" value="">
        <input type="hidden" id="detail-payment-intent" value="{{ ($note['can_show_partial_payment_action'] ?? false) ? 'pay' : 'settle' }}">

        <div id="payment-selected-row-ids"></div>

        <div class="d-none" id="detail-payment-row-source">
          @foreach (($note['billing_rows'] ?? []) as $row)
            @if (!($row['is_paid'] ?? false) && (int) ($row['outstanding_rupiah'] ?? 0) > 0 && ($row['can_select_manually'] ?? true))
              <span
                data-payment-row-source
                data-billing-row-id="{{ $row['id'] }}"
                data-row-id="{{ $row['id'] }}"
                data-work-item-id="{{ $row['work_item_id'] }}"
                data-label="Line {{ $row['line_no'] }} · {{ $row['component_label'] }}"
                data-type-label="{{ $row['domain_type_label'] ?? '-' }}"
                data-outstanding-rupiah="{{ (int) ($row['outstanding_rupiah'] ?? 0) }}"
              ></span>
            @endif
          @endforeach
        </div>

        <div class="modal-body">
          <div id="detail-payment-standard-view">
            <div class="row g-4">
              <div class="col-12 col-lg-7">
                <div class="border rounded p-3 h-100">
                  <div class="d-flex flex-wrap justify-content-between align-items-start gap-2 mb-3">
                    <div>
                      <div class="fw-semibold">Ringkasan Pembayaran</div>
                      <div class="small text-muted">
                        Tagihan aktif dipilih otomatis. Detail teknis billing row disimpan hidden untuk menjaga allocation.
                      </div>
                    </div>
                    <span class="badge bg-light text-dark border" id="detail-payment-mode-badge">{{ ($note['can_show_partial_payment_action'] ?? false) ? 'Bayar Sebagian' : 'Lunasi' }}</span>
                  </div>

                  <div class="d-flex flex-column gap-2" id="detail-payment-line-summary">
                    <div class="p-3 text-muted small">Belum ada tagihan outstanding.</div>
                  </div>
                </div>
              </div>

              <div class="col-12 col-lg-5">
                <div class="border rounded p-3 mb-3">
                  <div class="small text-muted mb-2">Total Terpilih</div>
                  <div class="d-flex justify-content-between py-2 border-bottom">
                    <span class="text-muted">Outstanding Terpilih</span>
                    <strong id="detail-payment-selected-total">0</strong>
                  </div>
                  <div class="d-flex justify-content-between pt-2">
                    <span class="fw-semibold">Dibayar</span>
                    <strong id="detail-payment-payable-text">0</strong>
                  </div>
                </div>

                @if ($note['can_show_partial_payment_action'] ?? false)
                  <div class="border rounded p-3 mb-3" id="detail-payment-partial-panel">
                    <label class="form-label">Nominal Bayar Sebagian</label>
                    <input
                      type="text"
                      class="form-control"
                      id="detail-payment-amount-paid-display"
                      value="{{ old('amount_paid') }}"
                      placeholder="Contoh: 50.000"
                    >
                    <div class="small text-muted mt-2">
                      Default mengikuti tagihan outstanding terpilih. Nominal masih bisa diedit.
                    </div>
                  </div>
                @endif

                <div class="border rounded p-3">
                  <div class="d-flex justify-content-between py-2 border-bottom">
                    <span class="text-muted">Sisa Setelah Bayar</span>
                    <strong id="detail-payment-remaining-text">0</strong>
                  </div>
                  <div class="d-flex justify-content-between pt-2">
                    <span class="text-muted">Tanggal Bayar</span>
                    <strong>{{ $paymentModalConfig['date_default'] ?? $paymentDateDefault }}</strong>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div id="detail-payment-cash-view" class="d-none">
            <div class="border rounded p-3 mb-3 bg-light">
              <div class="small text-muted mb-1">Mode Cash</div>
              <div class="fw-semibold" id="detail-payment-cash-mode-text">{{ ($note['can_show_partial_payment_action'] ?? false) ? 'Bayar Sebagian' : 'Lunasi' }}</div>
            </div>

            <div class="row g-3">
              <div class="col-12">
                <label class="form-label">Uang Masuk</label>
                <input
                  type="text"
                  class="form-control form-control-lg"
                  id="detail-payment-amount-received-display"
                  value="{{ old('amount_received') }}"
                  placeholder="Masukkan uang pelanggan"
                >
              </div>
              <div class="col-12">
                <div class="border rounded p-3">
                  <div class="d-flex justify-content-between py-2 border-bottom">
                    <span class="text-muted">Harus Dibayar</span>
                    <strong id="detail-payment-cash-payable-text">0</strong>
                  </div>
                  <div class="d-flex justify-content-between pt-2">
                    <span class="text-muted">Kembalian Cash</span>
                    <strong id="detail-payment-change-text">0</strong>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="modal-footer">
          <div class="ui-form-actions w-100 justify-content-between" id="detail-payment-footer-main">
            <button type="button" class="btn btn-light-secondary" data-bs-dismiss="modal">Batal</button>
            <div class="d-flex gap-2">
              <button type="submit" class="btn btn-outline-primary" id="detail-payment-submit-transfer">
                Bayar Transfer
              </button>
              <button type="button" class="btn btn-primary" id="detail-payment-open-cash">
                Bayar Cash
              </button>
            </div>
          </div>

          <div class="ui-form-actions w-100 justify-content-between d-none" id="detail-payment-footer-cash">
            <button type="button" class="btn btn-light-secondary" id="detail-payment-back-cash">Kembali</button>
            <button type="submit" class="btn btn-primary" id="detail-payment-submit-cash">
              Simpan Cash
            </button>
          </div>
        </div>
      </form>
    </div>
  </div>
</div>
@endif
