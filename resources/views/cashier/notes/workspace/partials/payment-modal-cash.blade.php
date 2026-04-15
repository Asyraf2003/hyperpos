<div class="border rounded p-4">
    <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
        <div>
            <div class="fw-semibold fs-4">Kalkulator Cash</div>
            <div class="text-muted">
                Masukkan uang pelanggan. Format nominal akan otomatis dirapikan.
            </div>
        </div>

        <div class="text-start text-xl-end">
            <div class="small text-muted">Mode Pembayaran</div>
            <div class="fw-semibold fs-5" id="workspace-cash-mode-text">Bayar Penuh</div>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-12 col-lg-4">
            <div class="border rounded p-4 h-100">
                <div class="small text-muted mb-2">Tagihan</div>
                <div class="fs-1 fw-bold lh-sm" id="workspace-cash-payable-text">0</div>
            </div>
        </div>

        <div class="col-12 col-lg-4">
            <div class="border rounded p-4 h-100">
                <div class="small text-muted mb-2">Uang Pelanggan</div>
                <div class="fs-1 fw-bold lh-sm" id="workspace-cash-received-text">0</div>
            </div>
        </div>

        <div class="col-12 col-lg-4">
            <div class="border rounded p-4 h-100">
                <div class="small text-muted mb-2">Kembalian</div>
                <div class="fs-1 fw-bold lh-sm" id="workspace-cash-change-text">0</div>
            </div>
        </div>
    </div>

    <div class="border rounded p-4" data-money-input-group>
        <label for="inline_payment_amount_received_display" class="form-label fw-semibold fs-5 mb-3">
            Uang Dari Pelanggan
        </label>

        <input type="hidden" value="" data-money-raw data-cash-received-raw>

        <input
            type="text"
            id="inline_payment_amount_received_display"
            value=""
            class="form-control form-control-lg fs-2 fw-bold py-3"
            inputmode="numeric"
            placeholder="Masukkan uang dari pelanggan"
            data-money-display
        >

        <div class="form-text mt-3">
            Tekan Enter untuk simpan pembayaran cash saat nominal sudah cukup.
        </div>
    </div>
</div>
