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
                    <div class="row g-3 mb-4">
                        <div class="col-12 col-md-6">
                            <div class="border rounded p-3 h-100">
                                <div class="small text-muted">Nominal Dibayar Otomatis</div>
                                <div class="fs-5 fw-bold" id="workspace-modal-full-paid-text">0</div>
                            </div>
                        </div>

                        <div class="col-12 col-md-6">
                            <div class="border rounded p-3 h-100">
                                <div class="small text-muted">Kembalian Cash</div>
                                <div class="fs-5 fw-bold" id="workspace-modal-full-change-text">0</div>
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
                </div>

                <div id="workspace-modal-payment-fields" class="d-none">
                    <div class="row">
                        <div class="col-12 col-md-6">
                            <div class="form-group mb-3">
                                <label for="inline_payment_method" class="form-label">Metode Bayar</label>
                                <select id="inline_payment_method" name="inline_payment[payment_method]" class="form-select">
                                    @foreach ($paymentMethodOptions as $option)
                                        <option value="{{ $option['value'] }}" {{ ($oldInlinePayment['payment_method'] ?? 'cash') === $option['value'] ? 'selected' : '' }}>
                                            {{ $option['label'] }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="col-12 col-md-6">
                            <div class="form-group mb-3">
                                <label for="inline_payment_paid_at" class="form-label">Tanggal Bayar</label>
                                <input type="date" id="inline_payment_paid_at" name="inline_payment[paid_at]" value="{{ $oldInlinePayment['paid_at'] }}" class="form-control">
                            </div>
                        </div>

                        <div class="col-12 col-md-6 d-none" id="workspace-modal-amount-paid-group">
                            <div class="form-group mb-3">
                                <label for="inline_payment_amount_paid_rupiah" class="form-label">Nominal Dibayar</label>
                                <input
                                    type="text"
                                    id="inline_payment_amount_paid_rupiah"
                                    name="inline_payment[amount_paid_rupiah]"
                                    value="{{ $oldInlinePayment['amount_paid_rupiah'] }}"
                                    class="form-control"
                                    inputmode="numeric"
                                    placeholder="Isi untuk bayar sebagian"
                                >
                            </div>
                        </div>

                        <div class="col-12 col-md-6 d-none" id="workspace-modal-amount-received-group">
                            <div class="form-group mb-3">
                                <label for="inline_payment_amount_received_rupiah" class="form-label">Uang Masuk</label>
                                <input
                                    type="text"
                                    id="inline_payment_amount_received_rupiah"
                                    name="inline_payment[amount_received_rupiah]"
                                    value="{{ $oldInlinePayment['amount_received_rupiah'] }}"
                                    class="form-control"
                                    inputmode="numeric"
                                    placeholder="Wajib untuk cash"
                                >
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Tutup</button>
                <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Gunakan Pengaturan Ini</button>
            </div>
        </div>
    </div>
</div>
