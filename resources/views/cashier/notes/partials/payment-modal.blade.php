@if ($note['can_show_payment_form'] ?? false)
<div class="modal fade" id="note-payment-modal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <div>
          <h5 class="modal-title mb-1">Pembayaran Nota</h5>
          <p class="mb-0 text-muted small">Selection dibaca dari billing projection. Submit tetap memakai row contract existing agar route dan request tidak berubah di fase ini.</p>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
      </div>
      <form method="POST" action="{{ $paymentModalConfig['action'] ?? $paymentAction }}" id="note-payment-form">
        @csrf
        <input type="hidden" name="payment_scope" value="partial">
        <input type="hidden" name="payment_intent" id="payment-intent" value="pay">
        <div id="payment-selected-row-ids"></div>

        <div class="modal-body">
          <div class="row g-4">
            <div class="col-12 col-lg-7">
              <div class="border rounded p-3 h-100">
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                  <div>
                    <div class="fw-semibold">Billing Row yang Bisa Dipilih</div>
                    <div class="small text-muted">Bayar manual default kosong. Lunasi akan mengaktifkan semua outstanding yang lolos urutan komponen.</div>
                  </div>
                  <span class="badge bg-light text-dark border" id="payment-intent-badge">Bayar</span>
                </div>

                <div class="d-flex flex-column gap-2">
                  @foreach (($note['billing_rows'] ?? []) as $row)
                    @php($isDisabled = !($row['can_select_manually'] ?? false) && (int) ($row['outstanding_rupiah'] ?? 0) > 0)
                    <label class="border rounded px-3 py-2 d-flex align-items-start gap-2 {{ ($row['is_paid'] ?? false) ? 'bg-light' : '' }}">
                      <input type="checkbox"
                        class="form-check-input mt-1"
                        data-billing-row-checkbox
                        data-billing-row-id="{{ $row['id'] }}"
                        data-work-item-id="{{ $row['work_item_id'] }}"
                        data-component-group="{{ $row['component_group_label'] }}"
                        data-eligible-dp="{{ ($row['eligible_for_dp_preset'] ?? false) ? '1' : '0' }}"
                        data-outstanding-rupiah="{{ (int) ($row['outstanding_rupiah'] ?? 0) }}"
                        {{ ($row['is_paid'] ?? false) ? 'disabled' : '' }}
                        {{ $isDisabled ? 'disabled' : '' }}>
                      <span class="w-100 d-flex justify-content-between align-items-start gap-3">
                        <span>
                          <span class="d-block fw-semibold">Line {{ $row['line_no'] }} · {{ $row['component_label'] }}</span>
                          <small class="text-muted d-block">{{ $row['domain_type_label'] }} · Status: {{ $row['status_label'] }}</small>
                          @if (($row['is_paid'] ?? false))
                            <small class="text-muted d-block">Komponen sudah lunas.</small>
                          @elseif ($isDisabled)
                            <small class="text-muted d-block">{{ $row['selection_blocked_reason'] ?? 'Ikuti urutan komponen existing.' }}</small>
                          @elseif ($row['eligible_for_dp_preset'] ?? false)
                            <small class="text-muted d-block">Masuk prioritas preset DP.</small>
                          @endif
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
                <div class="small text-muted mb-2">Mode Bayar</div>
                <select class="form-select" id="payment-preset-mode">
                  <option value="manual">Manual</option>
                  <option value="dp">Preset DP</option>
                </select>
                <div class="small text-muted mt-2">Preset DP hanya memilih komponen produk yang outstanding. Jasa tidak dipilih default selama komponen produk pada line tersebut belum clear.</div>
              </div>

              <div class="border rounded p-3 mb-3">
                <div class="small text-muted mb-2">Ringkasan Pilihan</div>
                <div class="d-flex justify-content-between py-2 border-bottom"><span class="text-muted">Billing Row Dipilih</span><strong id="payment-modal-selected-count">0</strong></div>
                <div class="d-flex justify-content-between py-2 border-bottom"><span class="text-muted">Line Terdampak</span><strong id="payment-modal-selected-line-count">0</strong></div>
                <div class="d-flex justify-content-between py-2 border-bottom"><span class="text-muted">Outstanding Terpilih</span><strong id="payment-modal-selected-total">0</strong></div>
                <div class="d-flex justify-content-between pt-2"><span class="fw-semibold">Nominal Dibayar Sekarang</span><strong id="payment-modal-pay-now">0</strong></div>
              </div>

              <div class="mb-3"><label class="form-label">Metode</label><select class="form-select" name="payment_method" id="payment-method"><option value="cash" {{ old('payment_method') === 'cash' ? 'selected' : '' }}>Cash</option><option value="tf" {{ old('payment_method') === 'tf' ? 'selected' : '' }}>Transfer</option></select></div>
              <div class="mb-3"><label class="form-label">Tanggal Bayar</label><input type="date" class="form-control" name="paid_at" value="{{ old('paid_at', $paymentModalConfig['date_default'] ?? $paymentDateDefault) }}" required></div>
              <div class="mb-3"><label class="form-label">Nominal Dibayar Sekarang</label><input type="text" class="form-control" name="amount_paid" id="amount-paid" value="{{ old('amount_paid') }}" placeholder="Kosongkan untuk bayar penuh sesuai komponen terpilih"></div>
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
