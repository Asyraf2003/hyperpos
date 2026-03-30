<div class="col-12 col-xl-5">
    <div class="border rounded p-3 h-100">
        <div class="d-flex justify-content-between align-items-start mb-3">
            <div>
                <div class="fw-semibold">Kalkulator Cash</div>
                <div class="text-muted small">Hitung uang pelanggan dan kembalian.</div>
            </div>
            <span class="badge bg-light text-dark" id="workspace-cash-status-badge">Siaga</span>
        </div>

        <div id="workspace-cash-shell-hint" class="alert alert-light border mb-3">
            Pilih tombol <strong>Cash</strong> untuk mengaktifkan kalkulator.
        </div>

        <div id="workspace-payment-panel-cash" class="d-none">
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
            </div>
        </div>
    </div>
</div>
