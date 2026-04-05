<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Terjadi Gangguan')</title>
    <style>
        :root {
            color-scheme: light;
            --bg: #f7f7f9;
            --surface: #ffffff;
            --text: #1f2937;
            --muted: #6b7280;
            --border: #e5e7eb;
            --primary: #111827;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            min-height: 100vh;
            font-family: Arial, Helvetica, sans-serif;
            background: var(--bg);
            color: var(--text);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
        }

        .error-shell {
            width: 100%;
            max-width: 640px;
        }

        .error-card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 16px;
            padding: 32px;
            box-shadow: 0 10px 30px rgba(17, 24, 39, 0.06);
        }

        .error-code {
            display: inline-block;
            font-size: 13px;
            line-height: 1;
            padding: 8px 10px;
            border-radius: 999px;
            border: 1px solid var(--border);
            color: var(--muted);
            margin-bottom: 16px;
        }

        h1 {
            margin: 0 0 12px;
            font-size: 28px;
            line-height: 1.2;
        }

        p {
            margin: 0;
            color: var(--muted);
            line-height: 1.6;
        }

        .error-actions {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
            margin-top: 24px;
        }

        .btn {
            appearance: none;
            border: 0;
            text-decoration: none;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 44px;
            padding: 0 16px;
            border-radius: 10px;
            font-weight: 600;
        }

        .btn-primary {
            background: var(--primary);
            color: #ffffff;
        }

        .btn-secondary {
            background: #f3f4f6;
            color: var(--text);
            border: 1px solid var(--border);
        }

        .error-note {
            margin-top: 18px;
            font-size: 14px;
            color: var(--muted);
        }
    </style>
</head>
<body>
    <main class="error-shell">
        <section class="error-card">
            <div class="error-code">Error {{ $statusCode ?? '500' }}</div>
            <h1>@yield('heading')</h1>
            <p>@yield('message')</p>

            <div class="error-actions">
                @yield('actions')
            </div>

            @hasSection('note')
                <div class="error-note">
                    @yield('note')
                </div>
            @endif
        </section>
    </main>
</body>
</html>
