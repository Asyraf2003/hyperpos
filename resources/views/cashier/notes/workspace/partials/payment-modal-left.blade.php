<div class="col-12 col-xl-7">
    <div class="border rounded p-3 mb-3">
        <div class="small text-muted mb-2">Pembayaran Nota</div>

        <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
            <span class="text-muted">Total Nota</span>
            <strong id="workspace-modal-total-text">0</strong>
        </div>

        <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
            <span class="text-muted">Dibayar Sekarang</span>
            <strong id="workspace-modal-payable-text">0</strong>
        </div>

        <div class="d-flex justify-content-between align-items-center py-2">
            <span class="text-muted">Sisa Setelah Bayar</span>
            <strong id="workspace-modal-remaining-text">0</strong>
        </div>
    </div>

    <div class="border rounded p-3 mb-3">
        <div class="small text-muted">Mode Aktif</div>
        <div class="fw-semibold" id="workspace-payment-mode-text">Bayar Penuh</div>
    </div>

    <div id="workspace-payment-panel-full" class="d-none">
        <div class="border rounded p-3 mb-3">
            <div class="fw-semibold mb-1">Bayar penuh</div>
            <div class="text-muted small">
                Transfer langsung simpan pembayaran penuh. Cash membuka kalkulator dengan total nota.
            </div>
        </div>
    </div>

    <div id="workspace-payment-panel-partial" class="d-none">
        <div class="border rounded p-3 mb-3">
            <div class="fw-semibold mb-1">Isi nominal pembayaran sebagian</div>
            <div class="text-muted small">
                Masukkan nominal yang dibayar sekarang. Partial payment tidak lagi dipilih per rincian.
            </div>
        </div>

        <div class="form-group mb-3">
            <label for="inline_payment_amount_paid_display" class="form-label">Nominal Dibayar Sekarang</label>
            <input
                type="text"
                id="inline_payment_amount_paid_display"
                value="{{ !empty($oldInlinePayment['amount_paid_rupiah']) ? number_format((int) $oldInlinePayment['amount_paid_rupiah'], 0, ',', '.') : '' }}"
                class="form-control"
                inputmode="numeric"
                placeholder="Masukkan nominal pembayaran sebagian"
            >
            <div class="form-text">Nominal akan dibatasi maksimal sebesar total nota.</div>
        </div>

        <div class="border rounded p-3">
            <div class="small text-muted">Nominal Tercatat</div>
            <div class="fs-5 fw-bold" id="workspace-partial-selected-total-text">0</div>
        </div>
    </div>
</div>
