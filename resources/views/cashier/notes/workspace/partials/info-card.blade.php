<div class="col-12 col-xl-4">
    <div class="card">
        <div class="card-header">
            <h4 class="card-title mb-1">Informasi Nota</h4>
            <p class="mb-0 text-muted">Header nota dan total biaya saja. Pembayaran diatur dari aksi bawah.</p>
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

            <div class="border rounded p-3 mb-4">
                <div class="small text-muted">Total Biaya Nota</div>
                <div class="fs-4 fw-bold" id="workspace-note-total-text">0</div>
            </div>

            <div class="d-grid gap-2">
                <button type="button" class="btn btn-light" id="workspace-submit-skip">Skip</button>
                <button type="button" class="btn btn-outline-primary" data-open-payment="partial">Bayar Sebagian</button>
                <button type="button" class="btn btn-primary" data-open-payment="full">Bayar Penuh</button>
            </div>
        </div>
    </div>
</div>
