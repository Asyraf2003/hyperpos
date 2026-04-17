<div class="card">
    <div class="card-header">
        <div class="d-flex flex-wrap justify-content-between align-items-start gap-2">
            <div>
                <h4 class="card-title mb-1">Refund Line Tertutup</h4>
                <p class="mb-0 text-muted">
                    Form ini menjadi jembatan menuju flow refund per-line. Fokus refund diarahkan ke line yang sudah close, tanpa memutus histori pembayaran yang sudah tercatat.
                </p>
            </div>

            <span class="badge bg-light text-dark border">Panel Refund</span>
        </div>

        <p class="mt-2 mb-0 text-muted small">
            Pada tahap ini form refund masih memakai sumber pembayaran lama. Finalisasi refund per-line akan dikunci di paket berikutnya.
        </p>
    </div>

    <div class="card-body">
        @if ($errors->has('refund') || $errors->has('customer_payment_id') || $errors->has('amount_rupiah') || $errors->has('refunded_at') || $errors->has('reason'))
            <div class="alert alert-danger py-2 px-3 mb-3">
                @if ($errors->has('refund'))
                    <div>{{ $errors->first('refund') }}</div>
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

        <div class="border rounded p-3 mb-4">
            <div class="small text-muted mb-2">Ringkasan Refund Saat Ini</div>

            <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                <span class="text-muted">Sudah Dibayar</span>
                <strong>{{ number_format($note['net_paid_rupiah'], 0, ',', '.') }}</strong>
            </div>

            <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                <span class="text-muted">Total Refund</span>
                <strong>{{ number_format($note['total_refunded_rupiah'], 0, ',', '.') }}</strong>
            </div>

            <div class="d-flex justify-content-between align-items-center pt-2">
                <span class="fw-semibold">Refund Wajib Saat Ini</span>
                <strong class="fs-5">{{ number_format($note['refund_required_rupiah'], 0, ',', '.') }}</strong>
            </div>
        </div>

        <div class="border rounded p-3 mb-4 bg-light">
            <div class="fw-semibold mb-1">Catatan Transisi</div>
            <div class="small text-muted">
                Setelah flow refund line final selesai, area ini akan membaca line close yang dipilih langsung dari daftar line. Untuk sekarang, sumber pembayaran lama tetap dipakai agar alur refund tidak terputus.
            </div>
        </div>

        @if (($note['refund_payment_options'] ?? []) !== [])
            <form method="POST" action="{{ $refundAction }}">
                @csrf

                <div class="form-group mb-4">
                    <label for="refund_customer_payment_id" class="form-label">Sumber Pembayaran Lama</label>
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

                    <div class="form-text">
                        Sumber ini masih dipakai sementara untuk menjaga konsistensi histori refund sebelum flow line final selesai.
                    </div>
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
                        value="{{ old('amount_rupiah', $note['refund_required_rupiah'] > 0 ? $note['refund_required_rupiah'] : '') }}"
                        class="form-control"
                        placeholder="Contoh: 50000"
                        required
                    >

                    <div class="form-text">
                        Isi nominal yang dibalikkan sesuai kebutuhan line yang sedang direview.
                    </div>
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

                <div class="d-grid">
                    <button type="submit" class="btn btn-primary">Catat Refund</button>
                </div>
            </form>
        @else
            <div class="border rounded p-3 bg-light">
                <div class="fw-semibold mb-1">Belum ada sumber refund yang bisa dipilih</div>
                <div class="text-muted small">
                    Semua pembayaran pada nota ini sudah habis direfund atau data pembayaran lama belum tersedia untuk flow refund transisi.
                </div>
            </div>
        @endif
    </div>
</div>
