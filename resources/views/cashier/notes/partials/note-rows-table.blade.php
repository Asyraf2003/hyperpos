<div class="card h-100">
    <div class="card-header">
        <div class="d-flex flex-wrap justify-content-between align-items-start gap-2">
            <div>
                <h4 class="card-title mb-1">Daftar Line Nota</h4>
                <p class="mb-0 text-muted">
                    Setiap line menjadi unit kerja utama. Pilih line yang ingin diproses, lalu lanjutkan dari panel samping.
                </p>
            </div>

            <span class="badge bg-light text-dark border">
                {{ $note['line_summary']['summary_label'] ?? 'Belum ada line.' }}
            </span>
        </div>

        <p class="mt-2 mb-0 text-muted small">
            Open dapat dipilih untuk pembayaran. Close dapat dipilih untuk refund. Refund penuh hanya berlaku untuk line yang benar-benar sudah masuk alur refund.
        </p>
    </div>

    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped align-middle mb-0">
                <thead>
                    <tr>
                        <th style="width: 56px;">Pilih</th>
                        <th>Line</th>
                        <th>Tipe</th>
                        <th>Status Line</th>
                        <th class="text-end">Subtotal</th>
                        <th class="text-end">Sudah Dibayar</th>
                        <th class="text-end">Sisa</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($note['rows'] as $row)
                        <tr>
                            <td>
                                @if (($row['can_pay'] ?? false) && (($row['id'] ?? '') !== ''))
                                    <div class="form-check d-flex justify-content-center mb-0">
                                        <input
                                            type="checkbox"
                                            class="form-check-input js-payment-row-selector"
                                            value="{{ (string) ($row['id'] ?? '') }}"
                                            data-row-id="{{ (string) ($row['id'] ?? '') }}"
                                            data-outstanding-rupiah="{{ (int) ($row['outstanding_rupiah'] ?? 0) }}"
                                            data-line-status="{{ $row['line_status'] ?? '' }}"
                                            aria-label="Pilih line {{ $row['line_no'] }} untuk pembayaran"
                                        >
                                    </div>
                                @elseif (($row['can_refund'] ?? false) && (($row['id'] ?? '') !== ''))
                                    <div class="form-check d-flex justify-content-center mb-0">
                                        <input
                                            type="checkbox"
                                            class="form-check-input js-refund-row-selector"
                                            value="{{ (string) ($row['id'] ?? '') }}"
                                            data-row-id="{{ (string) ($row['id'] ?? '') }}"
                                            data-refundable-rupiah="{{ (int) ($row['net_paid_rupiah'] ?? 0) }}"
                                            data-line-status="{{ $row['line_status'] ?? '' }}"
                                            aria-label="Pilih line {{ $row['line_no'] }} untuk refund"
                                        >
                                    </div>
                                @else
                                    <span class="text-muted small">-</span>
                                @endif
                            </td>
                            <td>{{ $row['line_no'] }}</td>
                            <td>{{ $row['type_label'] }}</td>
                            <td>
                                <span class="badge bg-light text-dark border text-uppercase">
                                    {{ $row['line_status'] ?? '-' }}
                                </span>
                            </td>
                            <td class="text-end">{{ number_format((int) ($row['subtotal_rupiah'] ?? 0), 0, ',', '.') }}</td>
                            <td class="text-end">{{ number_format((int) ($row['net_paid_rupiah'] ?? 0), 0, ',', '.') }}</td>
                            <td class="text-end">{{ number_format((int) ($row['outstanding_rupiah'] ?? 0), 0, ',', '.') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">
                                Belum ada line pada nota ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="small text-muted mt-3">
            Kolom aksi placeholder sudah dihapus. Proses line sekarang fokus ke pilihan line dan panel samping agar alurnya tidak membingungkan.
        </div>
    </div>
</div>
