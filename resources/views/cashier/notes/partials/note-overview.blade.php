<div class="row g-3">
    <div class="col-12 col-xl-6">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between align-items-start gap-3">
                <div>
                    <h4 class="card-title mb-1">Identitas Nota</h4>
                    <p class="mb-0 text-muted">Informasi utama nota pelanggan.</p>
                </div>

                @if ($note['can_show_edit_actions'] && ($note['total_allocated_rupiah'] ?? 0) === 0)
                    <a
                        href="{{ route('cashier.notes.workspace.edit', ['noteId' => $note['id']]) }}"
                        class="btn btn-outline-primary btn-sm"
                    >
                        Edit Nota
                    </a>
                @endif
            </div>

            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start py-2 border-bottom">
                    <div class="text-muted">Customer</div>
                    <div class="text-end fw-semibold">{{ $note['customer_name'] }}</div>
                </div>

                <div class="d-flex justify-content-between align-items-start py-2 border-bottom">
                    <div class="text-muted">No. Telp</div>
                    <div class="text-end fw-semibold">
                        {{ !empty($note['customer_phone']) ? $note['customer_phone'] : '-' }}
                    </div>
                </div>

                <div class="d-flex justify-content-between align-items-start py-2 border-bottom">
                    <div class="text-muted">Tanggal Nota</div>
                    <div class="text-end fw-semibold">{{ $note['transaction_date'] }}</div>
                </div>

                <div class="d-flex justify-content-between align-items-start py-2 border-bottom">
                    <div class="text-muted">Status Nota</div>
                    <div class="text-end">
                        <span class="badge bg-light text-dark text-uppercase">
                            {{ $note['is_closed'] ? 'closed' : 'open' }}
                        </span>
                    </div>
                </div>

                <div class="d-flex justify-content-between align-items-start py-2 border-bottom">
                    <div class="text-muted">Status Pembayaran</div>
                    <div class="text-end">
                        <span class="badge bg-light text-dark text-uppercase">{{ $note['payment_status'] }}</span>
                    </div>
                </div>

                <div class="d-flex justify-content-between align-items-start py-2">
                    <div class="text-muted">Jumlah Rincian</div>
                    <div class="text-end fw-semibold">{{ count($note['rows']) }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12 col-xl-6">
        <div class="card h-100">
            <div class="card-header">
                <h4 class="card-title mb-1">Ringkasan Pembayaran</h4>
                <p class="mb-0 text-muted">Susunan angka dibuat menurun supaya lebih cepat dibaca kasir.</p>
            </div>

            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                    <span class="text-muted">Grand Total</span>
                    <strong>{{ number_format($note['grand_total_rupiah'], 0, ',', '.') }}</strong>
                </div>

                <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                    <span class="text-muted">Total Dialokasikan</span>
                    <strong>{{ number_format($note['total_allocated_rupiah'], 0, ',', '.') }}</strong>
                </div>

                <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                    <span class="text-muted">Total Refund</span>
                    <strong>{{ number_format($note['total_refunded_rupiah'], 0, ',', '.') }}</strong>
                </div>

                <div class="d-flex justify-content-between align-items-center py-3">
                    <span class="fw-semibold">Sisa Tagihan</span>
                    <strong class="fs-5">{{ number_format($note['outstanding_rupiah'], 0, ',', '.') }}</strong>
                </div>
            </div>
        </div>
    </div>
</div>
