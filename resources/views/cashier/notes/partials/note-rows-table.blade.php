<div class="card h-100">
    <div class="card-header">
        <div class="d-flex flex-wrap justify-content-between align-items-start gap-2">
            <div>
                <h4 class="card-title mb-1">Rincian Nota</h4>
                <p class="mb-0 text-muted">
                    Rincian baris membaca struktur nota terbaru dan ringkasan settlement operasional per baris.
                </p>
            </div>

            <span class="badge bg-light text-dark border">
                {{ $note['is_closed'] ? 'Detail Close' : 'Detail Open' }}
            </span>
        </div>

        <p class="mt-2 mb-0 text-muted small">
            Angka per baris di bawah ini dipakai untuk membaca posisi operasional nota saat ini, bukan untuk mengubah histori ledger pembayaran.
        </p>
    </div>

    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped align-middle mb-0">
                <thead>
                    <tr>
                        <th>Baris</th>
                        <th>Tipe</th>
                        <th>Status Item</th>
                        <th class="text-end">Subtotal</th>
                        <th class="text-end">Net Paid</th>
                        <th class="text-end">Sisa</th>
                        <th class="text-end">Settlement</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($note['rows'] as $row)
                        <tr>
                            <td>{{ $row['line_no'] }}</td>
                            <td>{{ $row['type_label'] }}</td>
                            <td>{{ $row['status'] }}</td>
                            <td class="text-end">{{ number_format($row['subtotal_rupiah'], 0, ',', '.') }}</td>
                            <td class="text-end">{{ number_format($row['net_paid_rupiah'], 0, ',', '.') }}</td>
                            <td class="text-end">{{ number_format($row['outstanding_rupiah'], 0, ',', '.') }}</td>
                            <td class="text-end">
                                <span class="badge bg-light text-dark border text-uppercase">
                                    {{ $row['settlement_label'] }}
                                </span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
