<div
    class="modal fade"
    id="workspace-payment-modal"
    tabindex="-1"
    aria-labelledby="workspace-payment-modal-label"
    aria-hidden="true"
>
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <div>
                    <h5 class="modal-title" id="workspace-payment-modal-label">Pengaturan Pembayaran</h5>
                    <p class="mb-0 text-muted small">Pilih skenario pembayaran yang sesuai untuk nota ini.</p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>

            <div class="modal-body">
                <input
                    type="hidden"
                    id="inline_payment_method_hidden"
                    name="inline_payment[payment_method]"
                    value="{{ ($oldInlinePayment['payment_method'] ?? '') === 'cash' ? 'cash' : (($oldInlinePayment['payment_method'] ?? '') === 'transfer' ? 'transfer' : '') }}"
                >
                <input
                    type="hidden"
                    id="inline_payment_paid_at_hidden"
                    name="inline_payment[paid_at]"
                    value="{{ $oldNote['transaction_date'] }}"
                >

                <div class="row g-3 mb-4">
                    <div class="col-12 col-md-4">
                        <div class="border rounded p-3 h-100">
                            <div class="small text-muted">Grand Total</div>
                            <div class="fs-5 fw-bold" id="workspace-modal-grand-total-text">0</div>
                        </div>
                    </div>

                    <div class="col-12 col-md-4">
                        <div class="border rounded p-3 h-100">
                            <div class="small text-muted">Dibayar Sekarang</div>
                            <div class="fs-5 fw-bold" id="workspace-modal-paid-now-text">0</div>
                        </div>
                    </div>

                    <div class="col-12 col-md-4">
                        <div class="border rounded p-3 h-100">
                            <div class="small text-muted">Sisa Tagihan</div>
                            <div class="fs-5 fw-bold" id="workspace-modal-outstanding-text">0</div>
                        </div>
                    </div>
                </div>

                <div class="form-group mb-4">
                    <label class="form-label d-block">Keputusan Pembayaran</label>
                    @foreach ($paymentDecisionOptions as $option)
                        <div class="form-check mb-2">
                            <input
                                class="form-check-input"
                                type="radio"
                                name="inline_payment[decision]"
                                id="inline_payment_decision_{{ $option['value'] }}"
                                value="{{ $option['value'] }}"
                                {{ ($oldInlinePayment['decision'] ?? 'skip') === $option['value'] ? 'checked' : '' }}
                            >
                            <label class="form-check-label" for="inline_payment_decision_{{ $option['value'] }}">
                                {{ $option['label'] }}
                            </label>
                        </div>
                    @endforeach
                </div>

                <div id="workspace-payment-panel-skip" class="border rounded p-3 mb-4">
                    <div class="fw-semibold mb-1">Pembayaran dilewati dulu</div>
                    <div class="text-muted small">
                        Nota akan disimpan tanpa pembayaran. Pembayaran bisa dilakukan nanti dari detail nota.
                    </div>
                </div>

                <div id="workspace-payment-panel-full" class="d-none">
                    <div class="border rounded p-3 mb-4">
                        <div class="fw-semibold mb-1">Bayar penuh</div>
                        <div class="text-muted small mb-3">
                            Pilih TF untuk langsung simpan sebagai pembayaran transfer, atau pilih Cash untuk masuk ke kalkulator.
                        </div>
                        <div class="row g-3">
                            <div class="col-12 col-md-6">
                                <div class="border rounded p-3 h-100">
                                    <div class="small text-muted">Nominal Dibayar Otomatis</div>
                                    <div class="fs-5 fw-bold" id="workspace-modal-full-paid-text">0</div>
                                </div>
                            </div>
                            <div class="col-12 col-md-6">
                                <div class="border rounded p-3 h-100">
                                    <div class="small text-muted">Metode Saat Ini</div>
                                    <div class="fs-5 fw-bold" id="workspace-modal-full-method-text">Belum dipilih</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div id="workspace-payment-panel-partial" class="d-none">
                    <div class="row g-3 mb-4">
                        <div class="col-12 col-md-4">
                            <div class="border rounded p-3 h-100">
                                <div class="small text-muted">Total Sebelum Bayar</div>
                                <div class="fs-5 fw-bold" id="workspace-modal-partial-before-text">0</div>
                            </div>
                        </div>

                        <div class="col-12 col-md-4">
                            <div class="border rounded p-3 h-100">
                                <div class="small text-muted">Dibayar Sekarang</div>
                                <div class="fs-5 fw-bold" id="workspace-modal-partial-paid-text">0</div>
                            </div>
                        </div>

                        <div class="col-12 col-md-4">
                            <div class="border rounded p-3 h-100">
                                <div class="small text-muted">Sisa Setelah Bayar</div>
                                <div class="fs-5 fw-bold" id="workspace-modal-partial-after-text">0</div>
                            </div>
                        </div>
                    </div>

                    <div class="form-group mb-4" data-money-input-group>
                        <label for="inline_payment_amount_paid_display" class="form-label">Nominal Dibayar</label>
                        <input
                            type="hidden"
                            id="inline_payment_amount_paid_rupiah"
                            name="inline_payment[amount_paid_rupiah]"
                            value="{{ $oldInlinePayment['amount_paid_rupiah'] }}"
                            data-money-raw
                        >
                        <input
                            type="text"
                            id="inline_payment_amount_paid_display"
                            value="{{ $oldInlinePayment['amount_paid_rupiah'] }}"
                            class="form-control"
                            inputmode="numeric"
                            placeholder="Masukkan nominal yang dibayar sekarang"
                            data-money-display
                        >
                    </div>
                </div>

                <div id="workspace-payment-panel-cash" class="d-none">
                    <div class="border rounded p-3 mb-4">
                        <div class="fw-semibold mb-1">Kalkulator Cash</div>
                        <div class="text-muted small">Masukkan uang dari pelanggan untuk menghitung kembalian.</div>
                    </div>

                    <div class="row g-3 mb-4">
                        <div class="col-12 col-md-4">
                            <div class="border rounded p-3 h-100">
                                <div class="small text-muted">Tagihan</div>
                                <div class="fs-5 fw-bold" id="workspace-modal-cash-payable-text">0</div>
                            </div>
                        </div>

                        <div class="col-12 col-md-4">
                            <div class="border rounded p-3 h-100">
                                <div class="small text-muted">Uang Dari Pelanggan</div>
                                <div class="fs-5 fw-bold" id="workspace-modal-cash-received-text">0</div>
                            </div>
                        </div>

                        <div class="col-12 col-md-4">
                            <div class="border rounded p-3 h-100">
                                <div class="small text-muted">Kembalian</div>
                                <div class="fs-5 fw-bold" id="workspace-modal-cash-change-text">0</div>
                            </div>
                        </div>
                    </div>

                    <div class="form-group mb-0" data-money-input-group>
                        <label for="inline_payment_amount_received_display" class="form-label">Uang Dari Pelanggan</label>
                        <input
                            type="hidden"
                            id="inline_payment_amount_received_rupiah"
                            name="inline_payment[amount_received_rupiah]"
                            value="{{ $oldInlinePayment['amount_received_rupiah'] }}"
                            data-money-raw
                        >
                        <input
                            type="text"
                            id="inline_payment_amount_received_display"
                            value="{{ $oldInlinePayment['amount_received_rupiah'] }}"
                            class="form-control"
                            inputmode="numeric"
                            placeholder="Masukkan uang dari pelanggan"
                            data-money-display
                        >
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <div class="w-100 d-flex justify-content-between align-items-center gap-2" id="workspace-payment-footer-default">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Tutup</button>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary d-none" id="workspace-payment-submit-skip">Simpan Nota</button>

                        <button type="submit" class="btn btn-outline-primary d-none" id="workspace-payment-submit-transfer">
                            TF
                        </button>

                        <button type="button" class="btn btn-primary d-none" id="workspace-payment-open-cash">
                            Cash
                        </button>
                    </div>
                </div>

                <div class="w-100 d-none justify-content-between align-items-center gap-2" id="workspace-payment-footer-cash">
                    <button type="button" class="btn btn-light" id="workspace-payment-back-from-cash">Kembali</button>
                    <button type="submit" class="btn btn-primary" id="workspace-payment-submit-cash">OK</button>
                </div>
            </div>
        </div>
    </div>
</div>
