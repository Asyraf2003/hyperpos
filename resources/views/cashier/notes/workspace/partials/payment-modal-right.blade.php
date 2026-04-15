<div class="col-12 col-xl-5">
    <div class="border rounded p-3 h-100 d-flex flex-column">
        <div class="fw-semibold mb-1">Pilih Aksi</div>
        <div class="text-muted small mb-3">
            Pakai panah lalu Enter. Setelah aksi dipilih, lanjutkan ke transfer atau cash.
        </div>

        <div class="small text-muted">Mode Aktif</div>
        <div class="fw-semibold fs-5 mb-3" id="workspace-payment-mode-text">Belum dipilih</div>

        <div class="d-grid gap-2 mb-3" id="workspace-payment-choice-list">
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

        <div id="workspace-payment-panel-partial" class="d-none">
            <div class="border rounded p-3">
                <div class="fw-semibold mb-1">Nominal Dibayar Sekarang</div>
                <div class="text-muted small mb-3">
                    Isi nominal pembayaran sebagian. Tekan Enter untuk lanjut ke transfer atau cash.
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

        <div class="alert alert-light border mt-auto mb-0" id="workspace-payment-action-hint">
            Pilih aksi nota terlebih dahulu.
        </div>
    </div>
</div>
