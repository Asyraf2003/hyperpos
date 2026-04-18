<div class="row g-3">
    <div class="col-12 col-xl-6">
        <div class="card h-100">
            <div class="card-header">
                <div class="d-flex flex-wrap justify-content-between align-items-start gap-2">
                    <div>
                        <h4 class="card-title mb-1">Header Nota</h4>
                        <p class="mb-0 text-muted">
                            Ringkasan identitas nota dan komposisi line untuk area kerja kasir.
                        </p>
                    </div>

                    <span class="badge bg-light text-dark border">
                        {{ count($note['rows']) }} Line
                    </span>
                </div>

                <p class="mt-2 mb-0 text-muted small">
                    Nota dibaca sebagai wadah transaksi. Operasi harian mengikuti status masing-masing line.
                </p>
            </div>

            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start py-2 border-bottom">
                    <div class="text-muted">No. Nota</div>
                    <div class="text-end fw-semibold">{{ $note['id'] }}</div>
                </div>

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

                <div class="d-flex justify-content-between align-items-start py-2">
                    <div class="text-muted">Ringkasan Line</div>
                    <div class="text-end fw-semibold">
                        {{ $note['line_summary']['summary_label'] ?? 'Belum ada line.' }}
                    </div>
                </div>

                <div class="border rounded p-3 mt-3">
                    <div class="small text-muted mb-2">Komposisi Status Line</div>

                    <div class="d-flex flex-wrap gap-2">
                        <span class="badge bg-light text-dark border">
                            Open: {{ (int) ($note['line_summary']['open_count'] ?? 0) }}
                        </span>
                        <span class="badge bg-light text-dark border">
                            Close: {{ (int) ($note['line_summary']['close_count'] ?? 0) }}
                        </span>
                        <span class="badge bg-light text-dark border">
                            Refund: {{ (int) ($note['line_summary']['refund_count'] ?? 0) }}
                        </span>
                    </div>
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

                    <div class="small text-muted">
                        Edit besar tetap lewat workspace. Operasi harian utama akan mengikuti status per line.
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12 col-xl-6">
        <div class="card h-100">
            <div class="card-header">
                <h4 class="card-title mb-1">Ringkasan Angka</h4>
                <p class="mb-0 text-muted">
                    Angka utama nota untuk membaca posisi pembayaran, refund, dan sisa tagihan saat ini.
                </p>
            </div>

            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                    <span class="text-muted">Grand Total</span>
                    <strong>{{ number_format($note['grand_total_rupiah'], 0, ',', '.') }}</strong>
                </div>

                <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                    <span class="text-muted">Sudah Dibayar</span>
                    <strong>{{ number_format($note['net_paid_rupiah'], 0, ',', '.') }}</strong>
                </div>

                <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                    <span class="text-muted">Total Refund</span>
                    <strong>{{ number_format($note['total_refunded_rupiah'], 0, ',', '.') }}</strong>
                </div>

                <div class="d-flex justify-content-between align-items-center py-2">
                    <span class="text-muted">Sisa Tagihan</span>
                    <strong>{{ number_format($note['outstanding_rupiah'], 0, ',', '.') }}</strong>
                </div>

                @if ((int) $note['refund_required_rupiah'] > 0)
                    <div class="border rounded p-3 mt-3 bg-light">
                        <div class="small text-muted">Refund Wajib Saat Ini</div>
                        <div class="fs-5 fw-bold">{{ number_format($note['refund_required_rupiah'], 0, ',', '.') }}</div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
