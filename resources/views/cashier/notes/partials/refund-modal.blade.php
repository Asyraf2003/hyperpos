@if ($note['can_show_refund_form'] ?? false)
<div class="modal fade" id="note-refund-modal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <div>
          <h5 class="modal-title mb-1">Refund Nota</h5>
          <p class="mb-0 text-muted small">Refund tetap memilih line domain. Preview di bawah hanya membantu membaca uang kembali, stok toko kembali, dan external yang disederhanakan.</p>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
      </div>
      <form method="POST" action="{{ $refundModalConfig['action'] ?? $refundAction }}" id="note-refund-form">
        @csrf
        <div class="modal-body">
          <div class="border rounded p-3 mb-4">
            <div class="fw-semibold mb-2">Line Domain yang Bisa Direfund</div>
            <div class="d-flex flex-column gap-2">
              @forelse (($note['refund_rows'] ?? []) as $row)
                <label class="border rounded px-3 py-2 d-flex align-items-start gap-2">
                  <input type="checkbox" class="form-check-input mt-1" name="selected_row_ids[]" value="{{ $row['id'] }}"
                    data-refund-row-checkbox data-row-id="{{ $row['id'] }}"
                    data-refundable-rupiah="{{ (int) ($row['net_paid_rupiah'] ?? 0) }}"
                    data-store-return-count="{{ (int) ($row['refund_stock_return_count'] ?? 0) }}"
                    data-external-count="{{ (int) ($row['refund_external_count'] ?? 0) }}"
                    data-money-possible="{{ ($row['refund_money_possible'] ?? false) ? '1' : '0' }}"
                    {{ in_array($row['id'], old('selected_row_ids', []), true) ? 'checked' : '' }}>
                  <span class="w-100 d-flex justify-content-between align-items-start gap-3">
                    <span>
                      <span class="d-block fw-semibold">Line {{ $row['line_no'] }} · {{ $row['type_label'] }}</span>
                      <small class="text-muted d-block">Status: {{ $row['line_status'] }} · Dibayar Bersih: {{ number_format((int) ($row['net_paid_rupiah'] ?? 0), 0, ',', '.') }}</small>
                      <small class="text-muted d-block">{{ $row['refund_preview_label'] ?? 'Refund sederhana mengikuti line domain.' }}</small>
                    </span>
                    <strong>{{ number_format((int) ($row['net_paid_rupiah'] ?? 0), 0, ',', '.') }}</strong>
                  </span>
                </label>
              @empty
                <div class="small text-muted">Belum ada line yang bisa direfund dari status saat ini.</div>
              @endforelse
            </div>
          </div>
          <div class="border rounded p-3 mb-4">
            <div class="small text-muted mb-2">Ringkasan Pilihan</div>
            <div class="d-flex justify-content-between py-2 border-bottom"><span class="text-muted">Jumlah Line Dipilih</span><strong id="refund-modal-selected-count">{{ count(old('selected_row_ids', [])) }}</strong></div>
            <div class="d-flex justify-content-between py-2 border-bottom"><span class="text-muted">Refundable Terpilih</span><strong id="refund-modal-selected-total">0</strong></div>
            <div class="d-flex justify-content-between py-2 border-bottom"><span class="text-muted">Stok Toko Kembali</span><strong id="refund-modal-stock-return-count">0</strong></div>
            <div class="d-flex justify-content-between py-2 border-bottom"><span class="text-muted">External Disederhanakan</span><strong id="refund-modal-external-count">0</strong></div>
            <div class="d-flex justify-content-between pt-2"><span class="fw-semibold">Nominal Refund Sekarang</span><strong id="refund-modal-refund-now">0</strong></div>
          </div>
          <div class="mb-3"><label class="form-label">Sumber Pembayaran Histori</label><select name="customer_payment_id" class="form-select" required>@foreach (($note['refund_payment_options'] ?? []) as $option)<option value="{{ $option['value'] }}" {{ old('customer_payment_id') === $option['value'] ? 'selected' : '' }}>{{ $option['label'] }}</option>@endforeach</select></div>
          <div class="mb-3"><label class="form-label">Tanggal Refund</label><input type="date" name="refunded_at" value="{{ old('refunded_at', $refundModalConfig['date_default'] ?? $refundDateDefault) }}" class="form-control" required></div>
          <div class="mb-3"><label class="form-label">Nominal Refund</label><input type="number" min="1" id="refund_amount_rupiah" name="amount_rupiah" value="{{ old('amount_rupiah') }}" class="form-control" required></div>
          <div class="mb-0"><label class="form-label">Alasan Refund</label><textarea name="reason" rows="3" class="form-control" required>{{ old('reason') }}</textarea></div>
        </div>
        <div class="modal-footer">
          <div class="ui-form-actions w-100 justify-content-between">
            <button type="button" class="btn btn-light-secondary" data-bs-dismiss="modal">Batal</button>
            <button type="submit" class="btn btn-primary" id="note-refund-submit">Catat Refund</button>
          </div>
        </div>
      </form>
    </div>
  </div>
</div>
@endif
