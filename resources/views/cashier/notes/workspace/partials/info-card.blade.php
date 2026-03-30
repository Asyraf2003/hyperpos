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

            <div class="border rounded p-3 mb-4">
                <div class="small text-muted">Sisa Tagihan</div>
                <div class="fs-5 fw-semibold" id="workspace-outstanding-text">0</div>
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

            <div class="form-group mb-3">
                <label for="inline_payment_paid_at" class="form-label">Tanggal Bayar</label>
                <input type="date" id="inline_payment_paid_at" name="inline_payment[paid_at]" value="{{ $oldInlinePayment['paid_at'] }}" class="form-control">
            </div>

            <div class="form-group mb-3">
                <label for="inline_payment_amount_paid_rupiah" class="form-label">Nominal Dibayar</label>
                <input type="text" id="inline_payment_amount_paid_rupiah" name="inline_payment[amount_paid_rupiah]" value="{{ $oldInlinePayment['amount_paid_rupiah'] }}" class="form-control" inputmode="numeric">
            </div>

            <div class="form-group mb-4">
                <label for="inline_payment_amount_received_rupiah" class="form-label">Uang Masuk</label>
                <input type="text" id="inline_payment_amount_received_rupiah" name="inline_payment[amount_received_rupiah]" value="{{ $oldInlinePayment['amount_received_rupiah'] }}" class="form-control" inputmode="numeric">
            </div>

            <button type="submit" class="btn btn-primary">Simpan Nota</button>
        </div>
    </div>
</div>
