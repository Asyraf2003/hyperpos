<div class="card">
    <div class="card-header">
        <div class="d-flex flex-wrap justify-content-between align-items-start gap-2">
            <div>
                <h4 class="card-title mb-1">Refund Line Close Terpilih</h4>
                <p class="mb-0 text-muted">
                    Form ini membaca line Close yang dipilih dari tabel line. Refund dicatat untuk line yang dicentang, sambil tetap menjaga histori sumber pembayaran.
                </p>
            </div>

            <span class="badge bg-light text-dark border">Panel Refund</span>
        </div>

        <p class="mt-2 mb-0 text-muted small">
            Sumber pembayaran lama tetap dipakai sebagai jejak histori, tetapi pilihan line Close sekarang menjadi input utama refund.
        </p>
    </div>

    <div class="card-body">
        @if ($errors->has('refund') || $errors->has('selected_row_ids') || $errors->has('customer_payment_id') || $errors->has('amount_rupiah') || $errors->has('refunded_at') || $errors->has('reason'))
            <div class="alert alert-danger py-2 px-3 mb-3">
                @if ($errors->has('refund'))
                    <div>{{ $errors->first('refund') }}</div>
                @endif
                @if ($errors->has('selected_row_ids'))
                    <div>{{ $errors->first('selected_row_ids') }}</div>
                @endif
                @if ($errors->has('customer_payment_id'))
                    <div>{{ $errors->first('customer_payment_id') }}</div>
                @endif
                @if ($errors->has('amount_rupiah'))
                    <div>{{ $errors->first('amount_rupiah') }}</div>
                @endif
                @if ($errors->has('refunded_at'))
                    <div>{{ $errors->first('refunded_at') }}</div>
                @endif
                @if ($errors->has('reason'))
                    <div>{{ $errors->first('reason') }}</div>
                @endif
            </div>
        @endif

        <form method="POST" action="{{ $refundAction }}" id="note-refund-form">
            @csrf

            <div id="selected-refund-row-inputs"></div>

            <div class="border rounded p-3 mb-4">
                <div class="small text-muted mb-2">Ringkasan Line Refund Terpilih</div>

                <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                    <span class="text-muted">Jumlah Line Dipilih</span>
                    <strong id="selected-refund-row-count">0</strong>
                </div>

                <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                    <span class="text-muted">Total Refundable Line Dipilih</span>
                    <strong id="selected-refund-refundable-total">0</strong>
                </div>

                <div class="d-flex justify-content-between align-items-center pt-2">
                    <span class="fw-semibold">Nominal Refund Sekarang</span>
                    <strong id="selected-refund-total">0</strong>
                </div>
            </div>

            <div class="border rounded p-3 mb-4 bg-light">
                <div class="fw-semibold mb-1">Catatan Transisi Selesai</div>
                <div class="small text-muted">
                    Refund sekarang mengikuti line Close yang dipilih. Sumber pembayaran tetap dipakai untuk memastikan histori refund tetap konsisten dengan alokasi payment sebelumnya.
                </div>
            </div>

            @if (($note['refund_payment_options'] ?? []) !== [])
                <div class="form-group mb-4">
                    <label for="refund_customer_payment_id" class="form-label">Sumber Pembayaran Histori</label>
                    <select
                        id="refund_customer_payment_id"
                        name="customer_payment_id"
                        class="form-select"
                        required
                    >
                        @foreach ($note['refund_payment_options'] as $option)
                            <option
                                value="{{ $option['value'] }}"
                                {{ old('customer_payment_id') === $option['value'] ? 'selected' : '' }}
                            >
                                {{ $option['label'] }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group mb-4">
                    <label for="refund_refunded_at" class="form-label">Tanggal Refund</label>
                    <input
                        type="date"
                        id="refund_refunded_at"
                        name="refunded_at"
                        value="{{ old('refunded_at', $refundDateDefault) }}"
                        class="form-control"
                        required
                    >
                </div>

                <div class="form-group mb-4">
                    <label for="refund_amount_rupiah" class="form-label">Nominal Refund</label>
                    <input
                        type="number"
                        min="1"
                        id="refund_amount_rupiah"
                        name="amount_rupiah"
                        value="{{ old('amount_rupiah') }}"
                        class="form-control"
                        placeholder="Isi nominal refund untuk line Close yang dipilih"
                        required
                    >
                </div>

                <div class="form-group mb-4">
                    <label for="refund_reason" class="form-label">Alasan Refund</label>
                    <textarea
                        id="refund_reason"
                        name="reason"
                        rows="3"
                        class="form-control"
                        placeholder="Jelaskan line atau kejadian yang menjadi alasan refund"
                        required
                    >{{ old('reason') }}</textarea>
                </div>

                <div class="ui-form-actions">
                    <button type="submit" class="btn btn-primary" id="note-refund-submit">Catat Refund Line</button>
                </div>
            @else
                <div class="border rounded p-3 bg-light">
                    <div class="fw-semibold mb-1">Belum ada sumber refund yang bisa dipilih</div>
                    <div class="text-muted small">
                        Semua pembayaran pada nota ini sudah habis direfund atau data pembayaran lama belum tersedia untuk flow refund.
                    </div>
                </div>
            @endif
        </form>
    </div>
</div>
