@if ($note['can_show_refund_form'] ?? false)
<div class="modal fade" id="note-refund-modal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <div>
          <h5 class="modal-title mb-1">Refund Nota</h5>
          <p class="mb-0 text-muted small">Refund hanya berlaku untuk line yang sudah dipilih dari tabel detail.</p>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
      </div>

      <form method="POST" action="{{ $refundModalConfig['action'] ?? $refundAction }}" id="note-refund-form">
        @csrf
        <div id="note-refund-hidden-selected-rows"></div>
        <input type="hidden" id="refund_amount_rupiah" name="amount_rupiah" value="">

        <div class="modal-body">
          <div class="border rounded p-3 mb-4">
            <div class="fw-semibold mb-2">Line Terpilih</div>
            <div id="note-refund-selected-lines" class="d-flex flex-column gap-2">
              <div class="small text-muted">Belum ada line dipilih.</div>
            </div>
          </div>

          <div class="border rounded p-3 mb-4">
            <div class="small text-muted mb-2">Akibat Refund</div>
            <div class="d-flex justify-content-between py-2 border-bottom">
              <span class="text-muted">Jumlah Line Dipilih</span>
              <strong id="refund-modal-selected-count">0</strong>
            </div>
            <div class="d-flex justify-content-between py-2 border-bottom">
              <span class="text-muted">Nominal Refund Otomatis</span>
              <strong id="refund-modal-selected-total">0</strong>
            </div>
            <div class="d-flex justify-content-between py-2 border-bottom">
              <span class="text-muted">Stok Toko Kembali</span>
              <strong id="refund-modal-stock-return-count">0</strong>
            </div>
            <div class="d-flex justify-content-between py-2 border-bottom">
              <span class="text-muted">Komponen External Dinetralkan</span>
              <strong id="refund-modal-external-count">0</strong>
            </div>
            <div class="pt-2 small text-muted" id="refund-modal-impact-note">
              Refund akan dicatat untuk line yang dipilih sesuai total refundable saat ini.
            </div>
          </div>

          <div class="border rounded p-3 mb-4">
            <div class="fw-semibold mb-2">Barang / Stok Toko Kembali</div>
            <div id="refund-modal-store-returns" class="d-flex flex-column gap-2">
              <div class="small text-muted">Tidak ada stok toko yang kembali.</div>
            </div>
          </div>

          <div class="border rounded p-3 mb-4">
            <div class="fw-semibold mb-2">Komponen External yang Dinetralkan</div>
            <div id="refund-modal-external-returns" class="d-flex flex-column gap-2">
              <div class="small text-muted">Tidak ada komponen external yang dinetralkan.</div>
            </div>
          </div>

          <div class="mb-3">
            <label class="form-label">Sumber Pembayaran Histori</label>
            <select name="customer_payment_id" class="form-select" required>
              @foreach (($note['refund_payment_options'] ?? []) as $option)
                <option value="{{ $option['value'] }}" {{ old('customer_payment_id') === $option['value'] ? 'selected' : '' }}>{{ $option['label'] }}</option>
              @endforeach
            </select>
          </div>

          <div class="mb-3">
            <label class="form-label">Tanggal Refund</label>
            <input type="date" name="refunded_at" value="{{ old('refunded_at', $refundModalConfig['date_default'] ?? $refundDateDefault) }}" class="form-control" required>
          </div>

          <div class="mb-0">
            <label class="form-label">Alasan Refund</label>
            <input
              type="text"
              id="note-refund-reason"
              name="reason"
              value="{{ old('reason') }}"
              class="form-control"
              required
              autocomplete="off"
              placeholder="Tulis alasan refund lalu tekan Enter"
            >
          </div>
        </div>

        <div class="modal-footer">
          <div class="ui-form-actions w-100 justify-content-between">
            <button type="button" class="btn btn-light-secondary" data-bs-dismiss="modal">Batal</button>
            <button type="submit" class="btn btn-primary" id="note-refund-submit" disabled>Catat Refund</button>
          </div>
        </div>
      </form>
    </div>
  </div>
</div>
@endif
