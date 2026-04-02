<div class="card mt-3">
    <div class="card-body table-responsive">
        <table class="table table-striped mb-0">
            <thead>
                <tr>
                    <th>Baris</th>
                    <th>Tipe</th>
                    <th>Status Item</th>
                    <th>Status Bayar</th>
                    <th class="text-end">Subtotal</th>
                    <th class="text-end">Dialokasikan</th>
                    <th class="text-end">Sisa</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($note['rows'] as $row)
                    <tr>
                        <td>{{ $row['line_no'] }}</td>
                        <td>{{ $row['type_label'] }}</td>
                        <td>{{ $row['status'] }}</td>
                        <td>{{ $row['settlement_label'] }}</td>
                        <td class="text-end">{{ number_format($row['subtotal_rupiah'], 0, ',', '.') }}</td>
                        <td class="text-end">{{ number_format($row['net_paid_rupiah'], 0, ',', '.') }}</td>
                        <td class="text-end">{{ number_format($row['outstanding_rupiah'], 0, ',', '.') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
