<div class="card h-100">
    <div class="card-header">
        <div class="d-flex flex-wrap justify-content-between align-items-start gap-2">
            <div>
                <h4 class="card-title mb-1">Daftar Line Nota</h4>
                <p class="mb-0 text-muted">
                    Tabel line dipakai untuk membaca posisi kerja dan membuka aksi yang sesuai.
                </p>
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
                        <th>Line</th>
                        <th>Tipe</th>
                        <th>Status Line</th>
                        <th class="text-end">Subtotal</th>
                        <th class="text-end">Sudah Dibayar</th>
                        <th class="text-end">Refund</th>
                        <th class="text-end">Sisa</th>
                        <th style="width: 180px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($note['rows'] as $row)
                        <tr>
                            <td>{{ $row['line_no'] }}</td>
                            <td>{{ $row['type_label'] }}</td>
                            <td>
                                <span class="badge bg-light text-dark border text-uppercase">
                                    {{ (string) ($row['line_status'] ?? '') !== '' ? $row['line_status'] : '-' }}
                                </span>
                            </td>
                            <td class="text-end">{{ number_format((int) ($row['subtotal_rupiah'] ?? 0), 0, ',', '.') }}</td>
                            <td class="text-end">{{ number_format((int) ($row['net_paid_rupiah'] ?? 0), 0, ',', '.') }}</td>
                            <td class="text-end">{{ number_format((int) ($row['refunded_rupiah'] ?? 0), 0, ',', '.') }}</td>
                            <td class="text-end">{{ number_format((int) ($row['outstanding_rupiah'] ?? 0), 0, ',', '.') }}</td>
                            <td>
                                <div class="d-flex flex-wrap gap-2">
                                    @if (($row['can_pay'] ?? false) && ($note['can_show_payment_action'] ?? false))
                                        <button
                                            type="button"
                                            class="btn btn-sm btn-primary js-open-payment-modal"
                                            data-bs-toggle="modal"
                                            data-bs-target="#note-payment-modal"
                                            data-default-row-id="{{ $row['id'] }}"
                                        >
                                            Bayar
                                        </button>
                                    @endif

                                    @if (($row['can_refund'] ?? false) && ($note['can_show_refund_action'] ?? false))
                                        <button
                                            type="button"
                                            class="btn btn-sm btn-warning js-open-refund-modal"
                                            data-bs-toggle="modal"
                                            data-bs-target="#note-refund-modal"
                                            data-default-row-id="{{ $row['id'] }}"
                                        >
                                            Refund
                                        </button>
                                    @endif

                                    <button
                                        type="button"
                                        class="btn btn-sm btn-outline-secondary js-line-detail-focus"
                                        data-target-row-id="{{ $row['id'] }}"
                                    >
                                        Detail
                                    </button>
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
            Klik aksi yang sesuai pada row. Pemilihan line dilakukan di dalam modal agar konteks kerja tetap jelas.
        </div>
    </div>
</div>
