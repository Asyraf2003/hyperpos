<div class="card mt-3">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-start gap-3 mb-3">
            <div>
                <div class="fw-semibold">Rincian Nota</div>
                <div class="text-muted small">
                    Status pembayaran note dilihat dari ringkasan pembayaran di bagian atas, bukan per baris rincian.
                </div>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-striped mb-0">
                <thead>
                    <tr>
                        <th>Baris</th>
                        <th>Tipe</th>
                        <th>Status Item</th>
                        <th class="text-end">Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($note['rows'] as $row)
                        <tr>
                            <td>{{ $row['line_no'] }}</td>
                            <td>{{ $row['type_label'] }}</td>
                            <td>{{ $row['status'] }}</td>
                            <td class="text-end">{{ number_format($row['subtotal_rupiah'], 0, ',', '.') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
