@if ($note['outstanding_rupiah'] > 0)
    <div class="card mt-3">
        <div class="card-body">
            <form method="POST" action="{{ $paymentAction }}" id="note-payment-form">
                @csrf

                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label d-block">Jenis Pembayaran</label>

                        <div class="form-check mb-2">
                            <input
                                type="radio"
                                class="form-check-input"
                                name="payment_scope"
                                id="payment-scope-full"
                                value="full"
                                {{ old('payment_scope', 'full') === 'full' ? 'checked' : '' }}
                            >
                            <label class="form-check-label" for="payment-scope-full">Bayar Penuh</label>
                        </div>

                        <div class="form-check">
                            <input
                                type="radio"
                                class="form-check-input"
                                name="payment_scope"
                                id="payment-scope-partial"
                                value="partial"
                                {{ old('payment_scope') === 'partial' ? 'checked' : '' }}
                            >
                            <label class="form-check-label" for="payment-scope-partial">Bayar Sebagian</label>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Metode</label>
                        <select class="form-select" name="payment_method" id="payment-method">
                            <option value="cash" {{ old('payment_method') === 'cash' ? 'selected' : '' }}>Cash</option>
                            <option value="tf" {{ old('payment_method') === 'tf' ? 'selected' : '' }}>TF</option>
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Tanggal Bayar</label>
                        <input
                            type="date"
                            class="form-control"
                            name="paid_at"
                            value="{{ old('paid_at', $paymentDateDefault) }}"
                            required
                        >
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Grand Total</label>
                        <input
                            type="text"
                            class="form-control"
                            value="{{ number_format($note['grand_total_rupiah'], 0, ',', '.') }}"
                            readonly
                        >
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Sudah Dibayar</label>
                        <input
                            type="text"
                            class="form-control"
                            value="{{ number_format($note['net_paid_rupiah'], 0, ',', '.') }}"
                            readonly
                        >
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Sisa Tagihan</label>
                        <input
                            type="text"
                            class="form-control"
                            id="note-outstanding-display"
                            value="{{ number_format($note['outstanding_rupiah'], 0, ',', '.') }}"
                            readonly
                            data-outstanding-rupiah="{{ $note['outstanding_rupiah'] }}"
                        >
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Nominal Dibayar Sekarang</label>
                        <input
                            type="text"
                            class="form-control"
                            name="amount_paid"
                            id="amount-paid"
                            value="{{ old('amount_paid') }}"
                            placeholder="Isi jika bayar sebagian"
                        >
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Uang Masuk</label>
                        <input
                            type="text"
                            class="form-control"
                            name="amount_received"
                            id="amount-received"
                            value="{{ old('amount_received') }}"
                            placeholder="Wajib untuk cash"
                        >
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Dibayar Sekarang</label>
                        <input
                            type="text"
                            class="form-control"
                            id="selected-payment-total"
                            value="0"
                            readonly
                        >
                    </div>
                </div>

                <div class="row g-3 mt-1">
                    <div class="col-md-6">
                        <div class="small text-muted">Sisa Tagihan Setelah Bayar</div>
                        <div class="fw-bold" id="payment-remaining-text">0</div>
                    </div>

                    <div class="col-md-6">
                        <div class="small text-muted">Kembalian Cash</div>
                        <div class="fw-bold" id="payment-change-text">0</div>
                    </div>
                </div>

                <div class="mt-3">
                    <button type="submit" class="btn btn-primary">Bayar Sekarang</button>
                </div>
            </form>
        </div>
    </div>
@else
    <div class="card mt-3"><div class="card-body"><span class="fw-bold">Nota sudah lunas.</span></div></div>
@endif
