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
            <div class="fw-semibold mb-1">Pilih rincian yang mau dibayar</div>
            <div class="text-muted small">Centang rincian yang dibayar sekarang. Total akan dihitung otomatis.</div>
        </div>

        <div id="workspace-partial-selection-list" class="d-flex flex-column gap-2 mb-3"></div>

        <div class="border rounded p-3">
            <div class="small text-muted">Total Rincian Terpilih</div>
            <div class="fs-5 fw-bold" id="workspace-partial-selected-total-text">0</div>
        </div>
    </div>
</div>
