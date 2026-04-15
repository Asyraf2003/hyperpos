@if ($note['can_show_payment_form'])
    <div class="card">
        <div class="card-header">
            <div class="d-flex flex-wrap justify-content-between align-items-start gap-2">
                <div>
                    <h4 class="card-title mb-1">Pembayaran Nota</h4>
                    <p class="mb-0 text-muted">
                        Catat pembayaran untuk nota open tanpa keluar dari halaman detail kasir.
                    </p>
                </div>

                <span class="badge bg-light text-dark border">Mode Payment</span>
            </div>

            <p class="mt-2 mb-0 text-muted small">
                Pilih bayar penuh atau sebagian. Status note akan dihitung ulang dari outstanding terbaru setelah pembayaran dicatat.
            </p>
        </div>

        <div class="card-body">
            <form method="POST" action="{{ $paymentAction }}" id="note-payment-form">
                @csrf

                @if ($errors->has('payment'))
                    <div class="alert alert-danger py-2 px-3 mb-3">
                        {{ $errors->first('payment') }}
                    </div>
                @endif

                <div class="border rounded p-3 mb-4">
                    <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                        <span class="text-muted">Grand Total</span>
                        <strong>{{ number_format($note['grand_total_rupiah'], 0, ',', '.') }}</strong>
                    </div>

                    <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                        <span class="text-muted">Net Paid</span>
                        <strong>{{ number_format($note['net_paid_rupiah'], 0, ',', '.') }}</strong>
                    </div>

                    <div class="d-flex justify-content-between align-items-center pt-2">
                        <span class="fw-semibold">Sisa Tagihan</span>
                        <strong id="note-outstanding-display" data-outstanding-rupiah="{{ $note['outstanding_rupiah'] }}">
                            {{ number_format($note['outstanding_rupiah'], 0, ',', '.') }}
                        </strong>
                    </div>
                </div>

                <div class="row g-3">
                    <div class="col-12">
                        <label class="form-label d-block mb-2">Jenis Pembayaran</label>

                        <div class="d-flex flex-wrap gap-3">
                            <div class="form-check">
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
                    </div>

                    <div class="col-12">
                        <label class="form-label">Metode</label>
                        <select class="form-select" name="payment_method" id="payment-method">
                            <option value="cash" {{ old('payment_method') === 'cash' ? 'selected' : '' }}>Cash</option>
                            <option value="tf" {{ old('payment_method') === 'tf' ? 'selected' : '' }}>Transfer</option>
                        </select>
                    </div>

                    <div class="col-12">
                        <label class="form-label">Tanggal Bayar</label>
                        <input
                            type="date"
                            class="form-control"
                            name="paid_at"
                            value="{{ old('paid_at', $paymentDateDefault) }}"
                            required
                        >
                    </div>

                    <div class="col-12">
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

                    <div class="col-12">
                        <label class="form-label">Uang Masuk</label>
                        <input
                            type="text"
                            class="form-control"
                            name="amount_received"
                            id="amount-received"
                            value="{{ old('amount_received') }}"
                            placeholder="Wajib untuk pembayaran cash"
                        >
                    </div>
                </div>

                <div class="border rounded p-3 mt-4">
                    <div class="d-flex justify-content-between align-items-center border-bottom pb-2 mb-2">
                        <span class="text-muted">Dibayar Sekarang</span>
                        <strong id="selected-payment-total">0</strong>
                    </div>

                    <div class="d-flex justify-content-between align-items-center border-bottom pb-2 mb-2">
                        <span class="text-muted">Sisa Setelah Bayar</span>
                        <strong id="payment-remaining-text">0</strong>
                    </div>

                    <div class="d-flex justify-content-between align-items-center">
                        <span class="text-muted">Kembalian Cash</span>
                        <strong id="payment-change-text">0</strong>
                    </div>
                </div>

                <div class="d-grid mt-3">
                    <button type="submit" class="btn btn-primary">Bayar Sekarang</button>
                </div>
            </form>
        </div>
    </div>
@endif
