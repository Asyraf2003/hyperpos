<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>{{ $title }}</title>
    <style>
        body {
            color: #111827;
            font-family: "DejaVu Sans", sans-serif;
            font-size: 11px;
            line-height: 1.4;
            margin: 24px;
        }

        h1 {
            font-size: 20px;
            margin: 0 0 4px;
            text-transform: uppercase;
        }

        .meta {
            color: #374151;
            margin-bottom: 18px;
        }

        .summary {
            border-collapse: collapse;
            margin-bottom: 18px;
            width: 100%;
        }

        .summary td {
            border: 1px solid #d1d5db;
            padding: 7px 9px;
        }

        .summary td:first-child {
            background: #f3f4f6;
            font-weight: bold;
            width: 42%;
        }

        .number {
            text-align: right;
            white-space: nowrap;
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
                    <td class="number">{{ $item['value'] }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
