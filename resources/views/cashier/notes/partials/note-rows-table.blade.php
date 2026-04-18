<div class="card h-100">
    <div class="card-header">
        <div class="d-flex flex-wrap justify-content-between align-items-start gap-2">
            <div>
                <h4 class="card-title mb-1">Daftar Line Nota</h4>
                <p class="mb-0 text-muted">
                    Setiap line menjadi unit kerja utama. Gunakan tombol aksi yang sesuai, lalu lanjutkan dari panel samping.
                </p>
            </div>

            <span class="badge bg-light text-dark border">
                {{ $note['line_summary']['summary_label'] ?? 'Belum ada line.' }}
            </span>
        </div>

        <p class="mt-2 mb-0 text-muted small">
            Open dapat dibayar. Close dapat direfund. Refund hanya berlaku untuk line yang memang sudah berada di status close.
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
                        <th style="width: 160px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($note['rows'] as $row)
                        @php
                            $rowId = (string) ($row['id'] ?? '');
                            $canPay = (bool) ($row['can_pay'] ?? false);
                            $canRefund = (bool) ($row['can_refund'] ?? false);
                            $lineStatus = (string) ($row['line_status'] ?? '');
                            $rowOutstanding = (int) ($row['outstanding_rupiah'] ?? 0);
                            $rowRefundable = (int) ($row['net_paid_rupiah'] ?? 0);
                            $paymentSelectorId = 'payment-row-' . $rowId;
                            $refundSelectorId = 'refund-row-' . $rowId;
                        @endphp
                        <tr>
                            <td>
                                @if ($canPay && $rowId !== '')
                                    <div class="form-check d-flex justify-content-center mb-0">
                                        <input
                                            type="checkbox"
                                            id="{{ $paymentSelectorId }}"
                                            class="form-check-input js-payment-row-selector"
                                            value="{{ $rowId }}"
                                            data-row-id="{{ $rowId }}"
                                            data-outstanding-rupiah="{{ $rowOutstanding }}"
                                            data-line-status="{{ $lineStatus }}"
                                            aria-label="Pilih line {{ $row['line_no'] }} untuk pembayaran"
                                        >
                                    </div>
                                @elseif ($canRefund && $rowId !== '')
                                    <div class="form-check d-flex justify-content-center mb-0">
                                        <input
                                            type="checkbox"
                                            id="{{ $refundSelectorId }}"
                                            class="form-check-input js-refund-row-selector"
                                            value="{{ $rowId }}"
                                            data-row-id="{{ $rowId }}"
                                            data-refundable-rupiah="{{ $rowRefundable }}"
                                            data-line-status="{{ $lineStatus }}"
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
                                    {{ $lineStatus !== '' ? $lineStatus : '-' }}
                                </span>
                            </td>
                            <td class="text-end">{{ number_format((int) ($row['subtotal_rupiah'] ?? 0), 0, ',', '.') }}</td>
                            <td class="text-end">{{ number_format((int) ($row['net_paid_rupiah'] ?? 0), 0, ',', '.') }}</td>
                            <td class="text-end">{{ number_format($rowOutstanding, 0, ',', '.') }}</td>
                            <td>
                                <div class="d-flex flex-wrap gap-2">
                                    @if ($canPay && $rowId !== '')
                                        <button
                                            type="button"
                                            class="btn btn-sm btn-primary js-line-action"
                                            data-selector-id="{{ $paymentSelectorId }}"
                                            data-target-panel="#note-payment-form"
                                        >
                                            Bayar
                                        </button>
                                    @endif

                                    @if ($canRefund && $rowId !== '')
                                        <button
                                            type="button"
                                            class="btn btn-sm btn-warning js-line-action"
                                            data-selector-id="{{ $refundSelectorId }}"
                                            data-target-panel="#note-refund-form"
                                        >
                                            Refund
                                        </button>
                                    @endif

                                    @if ($lineStatus === 'refund')
                                        <button
                                            type="button"
                                            class="btn btn-sm btn-outline-secondary js-line-detail-focus"
                                            data-target-row-id="{{ $rowId }}"
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
