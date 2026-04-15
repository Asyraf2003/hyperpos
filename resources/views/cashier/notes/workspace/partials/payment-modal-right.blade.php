<div class="col-12 col-xl-5">
    <div class="border rounded p-3 h-100 d-flex flex-column">
        <div class="fw-semibold mb-1">Pembayaran Cash</div>
        <div class="text-muted small mb-3">
            Dipakai kalau pelanggan bayar tunai.
        </div>

        <div id="workspace-cash-shell-hint" class="alert alert-light border mb-3">
            Pilih <strong>Bayar Cash</strong> setelah menentukan aksi nota.
        </div>

        <div id="workspace-payment-panel-cash" class="d-none mt-auto">
            <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                <span class="text-muted">Tagihan</span>
                <strong id="workspace-cash-payable-text">0</strong>
            </div>

            <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                <span class="text-muted">Uang Pelanggan</span>
                <strong id="workspace-cash-received-text">0</strong>
            </div>

            <div class="d-flex justify-content-between align-items-center py-2 mb-3">
                <span class="text-muted">Kembalian</span>
                <strong id="workspace-cash-change-text">0</strong>
            </div>

            <div class="form-group mb-0" data-money-input-group>
                <label for="inline_payment_amount_received_display" class="form-label">Uang Dari Pelanggan</label>
                <input type="hidden" value="" data-money-raw data-cash-received-raw>
                <input
                    type="text"
                    id="inline_payment_amount_received_display"
                    value=""
                    class="form-control"
                    inputmode="numeric"
                    placeholder="Masukkan uang dari pelanggan"
                    data-money-display
                >
                <div class="form-text">
                    Tekan Enter kalau nominal sudah pas atau cukup untuk kembalian.
                </div>
            </div>
        </div>
    </div>
</div>
