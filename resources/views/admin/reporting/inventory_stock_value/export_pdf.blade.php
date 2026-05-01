<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>{{ $title }}</title>
    <style>
        body {
            font-family: "DejaVu Sans", sans-serif;
            font-size: 10px;
            color: #111827;
        }

        h1 {
            margin: 0 0 4px;
            font-size: 18px;
        }

        h2 {
            margin: 18px 0 8px;
            font-size: 13px;
        }

        .meta {
            margin-bottom: 12px;
            color: #374151;
        }

        .summary-grid {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
        }

        .summary-grid td {
            width: 25%;
            border: 1px solid #d1d5db;
            padding: 6px;
            vertical-align: top;
        }

        .summary-label {
            display: block;
            color: #4b5563;
            font-size: 9px;
        }

        .summary-value {
            display: block;
            margin-top: 3px;
            font-weight: bold;
            font-size: 11px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            page-break-inside: auto;
        }

        th,
        td {
            border: 1px solid #d1d5db;
            padding: 4px;
            vertical-align: top;
        }

        th {
            background: #f3f4f6;
            font-weight: bold;
            text-align: left;
        }

        tr {
            page-break-inside: avoid;
            page-break-after: auto;
        }

        .text-end {
            text-align: right;
        }

        .muted {
            color: #6b7280;
        }
    </style>
</head>
<body>
    <h1>{{ $title }}</h1>
    <div class="meta">
        Rentang movement: {{ $periodLabel }}<br>
        Tanggal referensi: {{ $referenceDateLabel }}<br>
        Dicetak: {{ $generatedAt }}
    </div>

    <h2>Ringkasan Persediaan</h2>
    <table class="summary-grid">
        <tbody>
            @foreach (array_chunk($summaryItems, 4) as $summaryRow)
                <tr>
                    @foreach ($summaryRow as $item)
                        <td>
                            <span class="summary-label">{{ $item['label'] }}</span>
                            <span class="summary-value">{{ $item['value'] }}</span>
                        </td>
                    @endforeach
                    @for ($i = count($summaryRow); $i < 4; $i++)
                        <td></td>
                    @endfor
                </tr>
            @endforeach
        </tbody>
    </table>

    <h2>Mutasi Periode</h2>
    <table>
        <thead>
            <tr>
                <th>Kode</th>
                <th>Nama Barang</th>
                <th class="text-end">Supply In</th>
                <th class="text-end">Sale Out</th>
                <th class="text-end">Refund/Reversal</th>
                <th class="text-end">Koreksi/Revisi</th>
                <th class="text-end">Net Qty</th>
                <th class="text-end">Selisih Nilai</th>
                <th class="text-end">Qty Saat Ini</th>
                <th class="text-end">Nilai Saat Ini</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($movementRows as $row)
                <tr>
                    <td>{{ $row['kode_barang'] }}</td>
                    <td>{{ $row['nama_barang'] }}</td>
                    <td class="text-end">{{ $row['supply_in_qty'] }}</td>
                    <td class="text-end">{{ $row['sale_out_qty'] }}</td>
                    <td class="text-end">{{ $row['refund_reversal_qty'] }}</td>
                    <td class="text-end">{{ $row['revision_correction_qty'] }}</td>
                    <td class="text-end">{{ $row['net_qty_delta'] }}</td>
                    <td class="text-end">{{ $row['net_cost_delta'] }}</td>
                    <td class="text-end">{{ $row['current_qty_on_hand'] }}</td>
                    <td class="text-end">{{ $row['current_inventory_value'] }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="10" class="muted">Tidak ada mutasi pada rentang ini.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <h2>Snapshot Stok Saat Ini</h2>
    <table>
        <thead>
            <tr>
                <th>Kode</th>
                <th>Nama Barang</th>
                <th>Merek</th>
                <th class="text-end">Ukuran</th>
                <th class="text-end">Qty</th>
                <th class="text-end">Avg Cost</th>
                <th class="text-end">Inventory Value</th>
                <th class="text-end">ROP</th>
                <th class="text-end">Critical</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($snapshotRows as $row)
                <tr>
                    <td>{{ $row['kode_barang'] }}</td>
                    <td>{{ $row['nama_barang'] }}</td>
                    <td>{{ $row['merek'] }}</td>
                    <td class="text-end">{{ $row['ukuran'] }}</td>
                    <td class="text-end">{{ $row['current_qty_on_hand'] }}</td>
                    <td class="text-end">{{ $row['current_avg_cost'] }}</td>
                    <td class="text-end">{{ $row['current_inventory_value'] }}</td>
                    <td class="text-end">{{ $row['reorder_point_qty'] }}</td>
                    <td class="text-end">{{ $row['critical_threshold_qty'] }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="9" class="muted">Tidak ada snapshot stok.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
