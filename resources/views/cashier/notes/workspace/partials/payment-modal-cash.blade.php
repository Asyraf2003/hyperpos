<div id="workspace-payment-panel-cash" class="d-none">
    <div class="border rounded p-3 mb-3">
        <div class="fw-semibold mb-1">Kalkulator Cash</div>
        <div class="text-muted small">Masukkan uang pelanggan untuk menghitung kembalian.</div>
    </div>

    <div class="row g-3 mb-3">
        <div class="col-12 col-md-4">
            <div class="border rounded p-3 h-100">
                <div class="small text-muted">Tagihan</div>
                <div class="fs-5 fw-bold" id="workspace-cash-payable-text">0</div>
            </div>
        </div>
        <div class="col-12 col-md-4">
            <div class="border rounded p-3 h-100">
                <div class="small text-muted">Uang Pelanggan</div>
                <div class="fs-5 fw-bold" id="workspace-cash-received-text">0</div>
            </div>
        </div>
        <div class="col-12 col-md-4">
            <div class="border rounded p-3 h-100">
                <div class="small text-muted">Kembalian</div>
                <div class="fs-5 fw-bold" id="workspace-cash-change-text">0</div>
            </div>
        </div>
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
