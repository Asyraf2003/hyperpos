<div class="col-12 col-xl-7">
    <div class="border rounded p-3 mb-3">
        <div class="d-flex justify-content-between align-items-start gap-3 mb-3">
            <div>
                <div class="fw-semibold">Ringkasan Nota</div>
                <div class="text-muted small">Cek isi transaksi sebelum disimpan.</div>
            </div>

            <span class="badge bg-light text-dark border" id="workspace-payment-mode-badge">
                Pilih Aksi
            </span>
        </div>

        <div
            id="workspace-payment-line-summary"
            class="border rounded"
            style="max-height: 260px; overflow-y: auto;"
        ></div>
    </div>

    <div class="border rounded p-3 mb-3">
        <div class="small text-muted">Total Nota</div>
        <div class="fs-4 fw-bold" id="workspace-modal-total-text">0</div>
    </div>

    <div class="border rounded p-3 mb-3">
        <div class="fw-semibold mb-2">Pilih Aksi</div>

        <div class="d-grid gap-2" id="workspace-payment-choice-list">
            <button
                type="button"
                class="btn btn-light text-start"
                id="workspace-payment-choice-full"
                data-payment-choice="full"
            >
                Bayar Penuh
            </button>

            <button
                type="button"
                class="btn btn-light text-start"
                id="workspace-payment-choice-partial"
                data-payment-choice="partial"
            >
                Bayar Sebagian
            </button>

            <button
                type="button"
                class="btn btn-light text-start"
                id="workspace-payment-choice-skip"
                data-payment-choice="skip"
            >
                Simpan Nota Tanpa Pembayaran
            </button>
        </div>
    </div>

    <div id="workspace-payment-panel-partial" class="d-none">
        <div class="border rounded p-3">
            <div class="fw-semibold mb-1">Nominal Dibayar Sekarang</div>
            <div class="text-muted small mb-3">
                Isi nominal pembayaran sebagian. Tekan Enter untuk lanjut ke pilihan transfer atau cash.
            </div>

            <div class="form-group mb-0">
                <label for="inline_payment_amount_paid_display" class="form-label">Nominal Dibayar Sekarang</label>
                <input
                    type="text"
                    id="inline_payment_amount_paid_display"
                    value="{{ !empty($oldInlinePayment['amount_paid_rupiah']) ? number_format((int) $oldInlinePayment['amount_paid_rupiah'], 0, ',', '.') : '' }}"
                    class="form-control"
                    inputmode="numeric"
                    placeholder="Masukkan nominal pembayaran sebagian"
                >
                <div class="form-text">
                    Nominal akan dibatasi maksimal sebesar total nota.
                </div>
            </div>
        </div>
    </div>
</div>
