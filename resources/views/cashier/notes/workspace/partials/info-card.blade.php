<div class="col-12 col-xl-4">
    <div class="card">
        <div class="card-header">
            <h4 class="card-title mb-1">Informasi Nota</h4>
            <p class="mb-0 text-muted">Header nota dan ringkasan transaksi.</p>
        </div>

        <div class="card-body">
            <div class="form-group mb-4">
                <label for="note_customer_name" class="form-label">Nama Customer</label>
                <input
                    type="text"
                    id="note_customer_name"
                    name="note[customer_name]"
                    value="{{ $oldNote['customer_name'] }}"
                    class="form-control"
                    placeholder="Contoh: {{ $defaultCustomerName }}"
                >
            </div>

            <div class="form-group mb-4">
                <label for="note_customer_phone" class="form-label">No. HP Customer</label>
                <input
                    type="text"
                    id="note_customer_phone"
                    name="note[customer_phone]"
                    value="{{ $oldNote['customer_phone'] }}"
                    class="form-control"
                    placeholder="Contoh: 0812xxxx"
                >
            </div>

            <div class="form-group mb-4">
                <label for="note_transaction_date" class="form-label">Tanggal Nota</label>
                <input
                    type="date"
                    data-ui-date="single"
                    id="note_transaction_date"
                    name="note[transaction_date]"
                    value="{{ $oldNote['transaction_date'] }}"
                    class="form-control"
                >
            </div>

            <div class="border rounded p-3 mb-3">
                <div class="small text-muted">Grand Total</div>
                <div class="fs-4 fw-bold" id="workspace-grand-total-text">0</div>
            </div>

            <div class="border rounded p-3 mb-3">
                <div class="small text-muted">Dibayar Sekarang</div>
                <div class="fs-5 fw-semibold" id="workspace-paid-now-text">0</div>
            </div>

            <div class="border rounded p-3 mb-3">
                <div class="small text-muted">Sisa Tagihan</div>
                <div class="fs-5 fw-semibold" id="workspace-outstanding-text">0</div>
            </div>

            <div class="border rounded p-3 mb-4">
                <div class="small text-muted mb-1">Status Pembayaran</div>
                <div class="fw-semibold" id="workspace-payment-decision-text">Skip</div>
                <div class="text-muted small" id="workspace-payment-method-text">Belum diatur</div>
            </div>

            <div class="d-grid gap-2">
                <button
                    type="button"
                    class="btn btn-outline-primary"
                    data-bs-toggle="modal"
                    data-bs-target="#workspace-payment-modal"
                >
                    Atur Pembayaran
                </button>

                <button type="submit" class="btn btn-primary">Simpan Nota</button>
            </div>
        </div>
    </div>
</div>
