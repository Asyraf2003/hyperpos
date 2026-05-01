<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>{{ $title }}</title>
    <style>
        body {
            color: #111827;
            font-family: "DejaVu Sans", sans-serif;
            font-size: 9px;
            line-height: 1.35;
            margin: 18px;
        }

        h1 {
            font-size: 20px;
            margin: 0 0 4px;
            text-transform: uppercase;
        }

        h2 {
            font-size: 13px;
            margin: 14px 0 7px;
        }

        .meta {
            color: #374151;
            margin-bottom: 12px;
        }

        .summary,
        .detail {
            border-collapse: collapse;
            width: 100%;
        }

        .summary {
            margin-bottom: 10px;
        }

        .summary td,
        .detail th,
        .detail td {
            border: 1px solid #d1d5db;
            padding: 5px 6px;
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
                <th class="number">Data</th>
                <th class="number">Total</th>
                <th class="number">Dibayar</th>
                <th class="number">Sisa</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($periodRows as $row)
                <tr>
                    <td>{{ $row['period_label'] }}</td>
                    <td class="number">{{ number_format($row['total_rows'], 0, ',', '.') }}</td>
                    <td class="number">{{ $row['total_debt'] }}</td>
                    <td class="number">{{ $row['total_paid_amount'] }}</td>
                    <td class="number">{{ $row['total_remaining_balance'] }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="muted">Tidak ada hutang karyawan pada periode ini.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <h2>Rincian Status</h2>
    <table class="detail">
        <thead>
            <tr>
                <th>Status</th>
                <th class="number">Data</th>
                <th class="number">Total</th>
                <th class="number">Dibayar</th>
                <th class="number">Sisa</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($statusRows as $row)
                <tr>
                    <td>{{ $row['status'] }}</td>
                    <td class="number">{{ number_format($row['total_rows'], 0, ',', '.') }}</td>
                    <td class="number">{{ $row['total_debt'] }}</td>
                    <td class="number">{{ $row['total_paid_amount'] }}</td>
                    <td class="number">{{ $row['total_remaining_balance'] }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="muted">Tidak ada status hutang pada periode ini.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <h2>Detail Hutang</h2>
    <table class="detail">
        <thead>
            <tr>
                <th>No</th>
                <th>Tanggal Catat</th>
                <th>Referensi Hutang</th>
                <th>Employee ID</th>
                <th>Status</th>
                <th class="number">Total</th>
                <th class="number">Dibayar</th>
                <th class="number">Sisa</th>
                <th>Catatan</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($rows as $row)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $row['recorded_at'] }}</td>
                    <td>{{ $row['debt_id'] }}</td>
                    <td>{{ $row['employee_id'] }}</td>
                    <td>{{ $row['status'] }}</td>
                    <td class="number">{{ $row['total_debt'] }}</td>
                    <td class="number">{{ $row['total_paid_amount'] }}</td>
                    <td class="number">{{ $row['remaining_balance'] }}</td>
                    <td>{{ $row['notes'] }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="9" class="muted">Tidak ada hutang karyawan pada periode ini.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
