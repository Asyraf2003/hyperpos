<div class="card mt-3">
    <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div>
            <h4 class="card-title mb-1">Rincian Nota</h4>
            <p class="mb-0 text-muted">Daftar baris transaksi yang sudah tercatat pada nota ini.</p>
        </div>

        <div class="text-muted small">
            Total {{ count($note['rows']) }} rincian
        </div>
    </div>

    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-lg mb-0">
                <thead>
                    <tr>
                        <th class="ps-3" style="width: 90px;">Baris</th>
                        <th>Tipe Rincian</th>
                        <th style="width: 160px;">Status</th>
                        <th class="text-end pe-3" style="width: 180px;">Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($note['rows'] as $row)
                        <tr>
                            <td class="ps-3 fw-semibold">{{ $row['line_no'] }}</td>
                            <td>{{ $row['type_label'] }}</td>
                            <td>
                                <span class="badge bg-light text-dark text-uppercase">{{ $row['status'] }}</span>
                            </td>
                            <td class="text-end pe-3 fw-semibold">
                                {{ number_format($row['subtotal_rupiah'], 0, ',', '.') }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center text-muted py-4">
                                Belum ada rincian pada nota ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>

                <tfoot>
                    <tr>
                        <th colspan="3" class="text-end ps-3">Grand Total</th>
                        <th class="text-end pe-3">
                            {{ number_format($note['grand_total_rupiah'], 0, ',', '.') }}
                        </th>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>
