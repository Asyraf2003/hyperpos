@if ($note['can_show_payment_form'] ?? false)
<div class="modal fade" id="note-payment-modal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <div>
          <h5 class="modal-title mb-1">Pembayaran Nota</h5>
          <p class="mb-0 text-muted small">Pilih line open yang ingin dibayar, lalu tentukan nominal dan metode pembayaran.</p>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
      </div>
      <form method="POST" action="{{ $paymentModalConfig['action'] ?? $paymentAction }}" id="note-payment-form">
        @csrf
        <div class="modal-body">
          <div class="row g-4">
            <div class="col-12 col-lg-7">
              <div class="border rounded p-3 h-100">
                <div class="fw-semibold mb-2">Line Open yang Bisa Dibayar</div>
                <div class="d-flex flex-column gap-2">
                  @foreach (($note['payment_rows'] ?? []) as $row)
                    <label class="border rounded px-3 py-2 d-flex align-items-start gap-2">
                      <input type="checkbox" class="form-check-input mt-1" name="selected_row_ids[]" value="{{ $row['id'] }}"
                        data-payment-row-checkbox data-row-id="{{ $row['id'] }}"
                        data-outstanding-rupiah="{{ (int) ($row['outstanding_rupiah'] ?? 0) }}"
                        {{ in_array($row['id'], old('selected_row_ids', []), true) ? 'checked' : '' }}>
                      <span class="w-100 d-flex justify-content-between align-items-start gap-3">
                        <span>
                          <span class="d-block fw-semibold">Line {{ $row['line_no'] }} · {{ $row['type_label'] }}</span>
                          <small class="text-muted d-block">Status: {{ $row['line_status'] }} · Subtotal: {{ number_format((int) ($row['subtotal_rupiah'] ?? 0), 0, ',', '.') }}</small>
                        </span>
                        <strong>{{ number_format((int) ($row['outstanding_rupiah'] ?? 0), 0, ',', '.') }}</strong>
                      </span>
                    </label>
                  @endforeach
                </div>
              </div>
            </div>
            <div class="col-12 col-lg-5">
              <div class="border rounded p-3 mb-3">
                <div class="small text-muted mb-2">Ringkasan Pilihan</div>
                <div class="d-flex justify-content-between py-2 border-bottom"><span class="text-muted">Jumlah Line Dipilih</span><strong id="payment-modal-selected-count">{{ count(old('selected_row_ids', [])) }}</strong></div>
                <div class="d-flex justify-content-between py-2 border-bottom"><span class="text-muted">Outstanding Terpilih</span><strong id="payment-modal-selected-total">0</strong></div>
                <div class="d-flex justify-content-between pt-2"><span class="fw-semibold">Nominal Dibayar Sekarang</span><strong id="payment-modal-pay-now">0</strong></div>
              </div>
              <input type="hidden" name="payment_scope" value="partial">
              <div class="mb-3"><label class="form-label">Metode</label><select class="form-select" name="payment_method" id="payment-method"><option value="cash" {{ old('payment_method') === 'cash' ? 'selected' : '' }}>Cash</option><option value="tf" {{ old('payment_method') === 'tf' ? 'selected' : '' }}>Transfer</option></select></div>
              <div class="mb-3"><label class="form-label">Tanggal Bayar</label><input type="date" class="form-control" name="paid_at" value="{{ old('paid_at', $paymentModalConfig['date_default'] ?? $paymentDateDefault) }}" required></div>
              <div class="mb-3"><label class="form-label">Nominal Dibayar Sekarang</label><input type="text" class="form-control" name="amount_paid" id="amount-paid" value="{{ old('amount_paid') }}" placeholder="Kosongkan untuk bayar penuh sesuai line terpilih"></div>
              <div class="mb-3"><label class="form-label">Uang Masuk</label><input type="text" class="form-control" name="amount_received" id="amount-received" value="{{ old('amount_received') }}" placeholder="Dipakai untuk cash"></div>
              <div class="border rounded p-3">
                <div class="d-flex justify-content-between py-2 border-bottom"><span class="text-muted">Sisa Setelah Bayar</span><strong id="payment-remaining-text">0</strong></div>
                <div class="d-flex justify-content-between pt-2"><span class="text-muted">Kembalian Cash</span><strong id="payment-change-text">0</strong></div>
              </div>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <div class="ui-form-actions w-100 justify-content-between">
            <button type="button" class="btn btn-light-secondary" data-bs-dismiss="modal">Batal</button>
            <button type="submit" class="btn btn-primary" id="note-payment-submit">Catat Pembayaran</button>
          </div>
        </div>
      </form>
    </div>
  </div>
</div>
@endif
