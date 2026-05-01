<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>{{ $title }}</title>
    <style>
        body {
            color: #111827;
            font-family: "DejaVu Sans", sans-serif;
            font-size: 10px;
            line-height: 1.35;
            margin: 20px;
        }

        h1 {
            font-size: 20px;
            margin: 0 0 4px;
            text-transform: uppercase;
        }

        h2 {
            font-size: 13px;
            margin: 16px 0 7px;
        }

        .meta {
            color: #374151;
            margin-bottom: 14px;
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
                <th class="number">Nominal</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($periodRows as $row)
                <tr>
                    <td>{{ $row['period_label'] }}</td>
                    <td class="number">{{ number_format($row['total_rows'], 0, ',', '.') }}</td>
                    <td class="number">{{ $row['total_amount'] }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="3" class="muted">Tidak ada payroll pada periode ini.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <h2>Rincian Mode</h2>
    <table class="detail">
        <thead>
            <tr>
                <th>Mode</th>
                <th class="number">Data</th>
                <th class="number">Nominal</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($modeRows as $row)
                <tr>
                    <td>{{ $row['mode_label'] }}</td>
                    <td class="number">{{ number_format($row['total_rows'], 0, ',', '.') }}</td>
                    <td class="number">{{ $row['total_amount'] }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="3" class="muted">Tidak ada mode payroll pada periode ini.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <h2>Detail Pencairan Gaji</h2>
    <table class="detail">
        <thead>
            <tr>
                <th>No</th>
                <th>Tanggal</th>
                <th>Karyawan</th>
                <th>Mode</th>
                <th>Catatan</th>
                <th>Nominal</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($rows as $row)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $row['date'] }}</td>
                    <td>{{ $row['employee_name'] }}</td>
                    <td>{{ $row['mode_label'] }}</td>
                    <td>{{ $row['notes'] }}</td>
                    <td class="number">{{ $row['amount'] }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="muted">Tidak ada payroll pada periode ini.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
