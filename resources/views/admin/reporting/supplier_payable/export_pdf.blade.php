<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>{{ $title }}</title>
    <style>
        body {
            color: #111827;
            font-family: "DejaVu Sans", sans-serif;
            font-size: 8px;
            line-height: 1.3;
            margin: 16px;
        }

        h1 {
            font-size: 19px;
            margin: 0 0 4px;
            text-transform: uppercase;
        }

        h2 {
            font-size: 12px;
            margin: 13px 0 6px;
        }

        .meta {
            color: #374151;
            margin-bottom: 10px;
        }

        .summary,
        .detail {
            border-collapse: collapse;
            width: 100%;
        }

        .summary {
            margin-bottom: 9px;
        }

        .summary td,
        .detail th,
        .detail td {
            border: 1px solid #d1d5db;
            padding: 4px 5px;
            vertical-align: top;
        }

        .summary td:first-child,
        .detail th {
            background: #e5e7eb;
            font-weight: bold;
        }

        .summary td:first-child {
            width: 32%;
        }

        .number {
            text-align: right;
            white-space: nowrap;
        }

        .muted {
            color: #6b7280;
        }
    </style>
</head>
<body>
    <h1>{{ $title }}</h1>
    <div class="meta">
        Periode: {{ $periodLabel }}<br>
        Tanggal Referensi: {{ $referenceDateLabel }}<br>
        Dicetak: {{ $generatedAt }}
    </div>

    <table class="summary">
        <tbody>
            @foreach ($summaryItems as $item)
                <tr>
                    <td>{{ $item['label'] }}</td>
                    <td>{{ $item['value'] }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <h2>Rincian Per Tanggal</h2>
    <table class="detail">
        <thead>
            <tr>
                <th>Tanggal</th>
                <th class="number">Invoice</th>
                <th class="number">Total Tagihan</th>
                <th class="number">Dibayar</th>
                <th class="number">Outstanding</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($periodRows as $row)
                <tr>
                    <td>{{ $row['period_label'] }}</td>
                    <td class="number">{{ number_format($row['total_rows'], 0, ',', '.') }}</td>
                    <td class="number">{{ $row['grand_total'] }}</td>
                    <td class="number">{{ $row['total_paid'] }}</td>
                    <td class="number">{{ $row['outstanding'] }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="muted">Tidak ada faktur pada periode ini.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <h2>Rincian Pemasok</h2>
    <table class="detail">
        <thead>
            <tr>
                <th>Supplier</th>
                <th class="number">Invoice</th>
                <th class="number">Total Tagihan</th>
                <th class="number">Dibayar</th>
                <th class="number">Outstanding</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($supplierRows as $row)
                <tr>
                    <td>{{ $row['supplier'] }}</td>
                    <td class="number">{{ number_format($row['total_rows'], 0, ',', '.') }}</td>
                    <td class="number">{{ $row['grand_total'] }}</td>
                    <td class="number">{{ $row['total_paid'] }}</td>
                    <td class="number">{{ $row['outstanding'] }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="muted">Tidak ada pemasok pada periode ini.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <h2>Detail Hutang Pemasok</h2>
    <table class="detail">
        <thead>
            <tr>
                <th>No</th>
                <th>No Faktur</th>
                <th>Supplier</th>
                <th>Tanggal Kirim</th>
                <th>Due Date</th>
                <th>Status</th>
                <th class="number">Total Tagihan</th>
                <th class="number">Dibayar</th>
                <th class="number">Outstanding</th>
                <th class="number">Receipt</th>
                <th class="number">Qty Diterima</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($rows as $row)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $row['invoice_no'] }}</td>
                    <td>{{ $row['supplier'] }}</td>
                    <td>{{ $row['shipment_date'] }}</td>
                    <td>{{ $row['due_date'] }}</td>
                    <td>{{ $row['status'] }}</td>
                    <td class="number">{{ $row['grand_total'] }}</td>
                    <td class="number">{{ $row['total_paid'] }}</td>
                    <td class="number">{{ $row['outstanding'] }}</td>
                    <td class="number">{{ number_format($row['receipt_count'], 0, ',', '.') }}</td>
                    <td class="number">{{ number_format($row['total_received_qty'], 0, ',', '.') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="11" class="muted">Tidak ada faktur pada periode ini.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
