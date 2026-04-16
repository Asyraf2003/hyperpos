<div class="row g-3">
    <div class="col-12 col-xl-6">
        <div class="card h-100">
            <div class="card-header">
                <div class="d-flex flex-wrap justify-content-between align-items-start gap-2">
                    <div>
                        <h4 class="card-title mb-1">Informasi Nota</h4>
                        <p class="mb-0 text-muted">
                            Ringkasan identitas dan status operasional nota untuk kasir.
                        </p>
                    </div>

                    <span class="badge bg-light text-dark border">
                        {{ $note['is_closed'] ? 'Mode Close' : 'Mode Open' }}
                    </span>
                </div>

                <p class="mt-2 mb-0 text-muted small">
                    {{ $note['is_closed']
                        ? 'Nota sudah close. Edit workspace dimatikan dan pembalikan dana dilakukan lewat refund.'
                        : 'Nota masih open. Edit workspace dan pembayaran tetap mengikuti kondisi outstanding terbaru.' }}
                </p>
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
                    <div class="text-muted">Status Operasional</div>
                    <div class="text-end">
                        <span class="badge bg-light text-dark text-uppercase border">
                            {{ $note['operational_status'] }}
                        </span>
                    </div>
                </div>

                <div class="d-flex justify-content-between align-items-start py-2">
                    <div class="text-muted">Jumlah Rincian</div>
                    <div class="text-end fw-semibold">{{ count($note['rows']) }}</div>
                </div>

                <div class="border rounded p-3 mt-3">
                    <div class="small text-muted">
                        {{ $note['is_closed'] ? 'Detail Close' : 'Detail Open' }}
                    </div>
                    <div class="fs-4 fw-bold">{{ number_format($note['grand_total_rupiah'], 0, ',', '.') }}</div>
                </div>

                <div class="d-grid gap-2 mt-3">
                    @if ($note['can_edit_workspace'] ?? false)
                        <a
                            href="{{ route('cashier.notes.workspace.edit', ['noteId' => $note['id']]) }}"
                            class="btn btn-primary"
                        >
                            Edit Nota
                        </a>
                    @else
                        <button type="button" class="btn btn-light" disabled>
                            Workspace Nonaktif
                        </button>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="col-12 col-xl-6">
        <div class="card h-100">
            <div class="card-header">
                <h4 class="card-title mb-1">Ringkasan Operasional</h4>
                <p class="mb-0 text-muted">
                    Angka operasional membaca total terbaru, net paid, dan kebutuhan refund bila ada.
                </p>
            </div>

            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                    <span class="text-muted">Grand Total</span>
                    <strong>{{ number_format($note['grand_total_rupiah'], 0, ',', '.') }}</strong>
                </div>

                <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                    <span class="text-muted">Sudah Dibayar (Net Paid)</span>
                    <strong>{{ number_format($note['net_paid_rupiah'], 0, ',', '.') }}</strong>
                </div>

                <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                    <span class="text-muted">Total Refund</span>
                    <strong>{{ number_format($note['total_refunded_rupiah'], 0, ',', '.') }}</strong>
                </div>

                <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                    <span class="text-muted">Sisa Tagihan</span>
                    <strong>{{ number_format($note['outstanding_rupiah'], 0, ',', '.') }}</strong>
                </div>

                <div class="d-flex justify-content-between align-items-center pt-3">
                    <span class="fw-semibold">Refund Wajib</span>
                    <strong class="fs-5">{{ number_format($note['refund_required_rupiah'], 0, ',', '.') }}</strong>
                </div>
            </div>
        </div>
    </div>
</div>
