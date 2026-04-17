<div class="card h-100">
    <div class="card-header">
        <div class="d-flex flex-wrap justify-content-between align-items-start gap-2">
            <div>
                <h4 class="card-title mb-1">Daftar Line Nota</h4>
                <p class="mb-0 text-muted">
                    Setiap line menjadi unit kerja utama. Aksi harian dibaca dari status line, bukan dari status nota.
                </p>
            </div>

            <span class="badge bg-light text-dark border">
                {{ $note['line_summary']['summary_label'] ?? 'Belum ada line.' }}
            </span>
        </div>

        <p class="mt-2 mb-0 text-muted small">
            Open dapat diedit dan dibayar. Close siap untuk refund. Refund hanya dibuka untuk melihat detail line.
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
                        <th style="width: 220px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($note['rows'] as $row)
                        @php
                            $canPay = (bool) ($row['can_pay'] ?? false);
                            $rowId = (string) ($row['id'] ?? '');
                            $rowOutstanding = (int) ($row['outstanding_rupiah'] ?? 0);
                        @endphp
                        <tr>
                            <td>
                                @if ($canPay && $rowId !== '')
                                    <div class="form-check d-flex justify-content-center mb-0">
                                        <input
                                            type="checkbox"
                                            class="form-check-input js-payment-row-selector"
                                            value="{{ $rowId }}"
                                            data-row-id="{{ $rowId }}"
                                            data-outstanding-rupiah="{{ $rowOutstanding }}"
                                            data-line-status="{{ $row['line_status'] ?? '' }}"
                                            aria-label="Pilih line {{ $row['line_no'] }} untuk pembayaran"
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
                            <td class="text-end">{{ number_format($rowOutstanding, 0, ',', '.') }}</td>
                            <td>
                                <div class="d-flex flex-wrap gap-2">
                                    @if ($row['can_edit'] ?? false)
                                        <button type="button" class="btn btn-sm btn-outline-primary" disabled>
                                            Edit
                                        </button>
                                    @endif

                                    @if ($row['can_pay'] ?? false)
                                        <button type="button" class="btn btn-sm btn-outline-success" disabled>
                                            Bayar
                                        </button>
                                    @endif

                                    @if ($row['can_refund'] ?? false)
                                        <button type="button" class="btn btn-sm btn-outline-warning" disabled>
                                            Refund
                                        </button>
                                    @endif

                                    @if ($row['can_view_detail'] ?? false)
                                        <button type="button" class="btn btn-sm btn-outline-secondary" disabled>
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
            Centang line Open yang ingin dibayar. Wiring final ke request, controller, dan allocator akan dikunci di langkah berikutnya.
        </div>
    </div>
</div>
