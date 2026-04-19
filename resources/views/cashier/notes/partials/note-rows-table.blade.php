<div class="card h-100">
    <div class="card-header">
        <div class="d-flex flex-wrap justify-content-between align-items-start gap-2">
            <div>
                <h4 class="card-title mb-1">Daftar Line Nota</h4>
            </div>

            <span class="badge bg-light text-dark border">
                {{ $note['line_summary']['summary_label'] ?? 'Belum ada line.' }}
            </span>
        </div>

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
                        <th style="width: 160px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($note['rows'] as $row)
                        <tr>
                            <td>
                                @if (($row['can_pay'] ?? false) && (string) ($row['id'] ?? '') !== '')
                                    <div class="form-check d-flex justify-content-center mb-0">
                                        <input
                                            type="checkbox"
                                            id="{{ 'payment-row-' . $row['id'] }}"
                                            class="form-check-input js-payment-row-selector"
                                            value="{{ $row['id'] }}"
                                            data-row-id="{{ $row['id'] }}"
                                            data-outstanding-rupiah="{{ (int) ($row['outstanding_rupiah'] ?? 0) }}"
                                            data-line-status="{{ (string) ($row['line_status'] ?? '') }}"
                                            aria-label="Pilih line {{ $row['line_no'] }} untuk pembayaran"
                                        >
                                    </div>
                                @elseif (($row['can_refund'] ?? false) && (string) ($row['id'] ?? '') !== '')
                                    <div class="form-check d-flex justify-content-center mb-0">
                                        <input
                                            type="checkbox"
                                            id="{{ 'refund-row-' . $row['id'] }}"
                                            class="form-check-input js-refund-row-selector"
                                            value="{{ $row['id'] }}"
                                            data-row-id="{{ $row['id'] }}"
                                            data-refundable-rupiah="{{ (int) ($row['net_paid_rupiah'] ?? 0) }}"
                                            data-line-status="{{ (string) ($row['line_status'] ?? '') }}"
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
                                    {{ (string) ($row['line_status'] ?? '') !== '' ? $row['line_status'] : '-' }}
                                </span>
                            </td>
                            <td class="text-end">{{ number_format((int) ($row['subtotal_rupiah'] ?? 0), 0, ',', '.') }}</td>
                            <td class="text-end">{{ number_format((int) ($row['net_paid_rupiah'] ?? 0), 0, ',', '.') }}</td>
                            <td class="text-end">{{ number_format((int) ($row['outstanding_rupiah'] ?? 0), 0, ',', '.') }}</td>
                            <td>
                                <div class="d-flex flex-wrap gap-2">
                                    @if (($row['can_pay'] ?? false) && (string) ($row['id'] ?? '') !== '')
                                        <button
                                            type="button"
                                            class="btn btn-sm btn-primary js-line-action"
                                            data-selector-id="{{ 'payment-row-' . $row['id'] }}"
                                            data-target-panel="#note-payment-form"
                                        >
                                            Bayar
                                        </button>
                                    @endif

                                    @if (($row['can_refund'] ?? false) && (string) ($row['id'] ?? '') !== '')
                                        <button
                                            type="button"
                                            class="btn btn-sm btn-warning js-line-action"
                                            data-selector-id="{{ 'refund-row-' . $row['id'] }}"
                                            data-target-panel="#note-refund-form"
                                        >
                                            Refund
                                        </button>
                                    @endif

                                    @if (($row['line_status'] ?? '') === 'refund' && (string) ($row['id'] ?? '') !== '')
                                        <button
                                            type="button"
                                            class="btn btn-sm btn-outline-secondary js-line-detail-focus"
                                            data-target-row-id="{{ $row['id'] }}"
                                        >
                                            Detail
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">
                                Belum ada line pada nota ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="small text-muted mt-3">
            Tombol aksi sekarang terlihat jelas. Klik Bayar atau Refund untuk memilih line otomatis dan berpindah ke panel yang sesuai.
        </div>
    </div>
</div>
