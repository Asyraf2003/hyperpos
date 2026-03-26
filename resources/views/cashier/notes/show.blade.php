<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $pageTitle }}</title>
    <style>
        :root {
            --bg: #0b1020;
            --panel: #111827;
            --soft: #1f2937;
            --line: #374151;
            --text: #e5e7eb;
            --muted: #9ca3af;
            --primary: #2563eb;
            --success: #16a34a;
            --warning: #f59e0b;
        }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            font-family: ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            background: var(--bg);
            color: var(--text);
        }
        .page {
            max-width: 1200px;
            margin: 0 auto;
            padding: 24px;
            display: grid;
            gap: 16px;
        }
        .panel {
            background: rgba(17, 24, 39, 0.94);
            border: 1px solid rgba(55, 65, 81, 0.8);
            border-radius: 16px;
            padding: 20px;
        }
        .title {
            margin: 0 0 8px;
            font-size: 28px;
            font-weight: 800;
        }
        .subtitle {
            margin: 0;
            color: var(--muted);
            font-size: 14px;
        }
        .grid {
            display: grid;
            gap: 16px;
        }
        .grid-2 { grid-template-columns: 1.2fr 0.8fr; }
        .cards-4 { grid-template-columns: repeat(4, minmax(0, 1fr)); }
        .metric {
            padding: 16px;
            border-radius: 14px;
            border: 1px solid var(--line);
            background: rgba(15, 23, 42, 0.8);
        }
        .metric-label { color: var(--muted); font-size: 13px; }
        .metric-value { margin-top: 8px; font-size: 26px; font-weight: 800; }
        .list {
            display: grid;
            gap: 12px;
        }
        .item {
            border: 1px solid var(--line);
            border-radius: 14px;
            padding: 16px;
            background: rgba(15, 23, 42, 0.78);
        }
        .item-head, .row, .actions, .modal-actions {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
        }
        .item-head { margin-bottom: 10px; }
        .badge {
            display: inline-flex;
            align-items: center;
            padding: 6px 10px;
            border-radius: 999px;
            font-size: 12px;
            border: 1px solid rgba(37, 99, 235, 0.28);
            background: rgba(37, 99, 235, 0.16);
            color: #bfdbfe;
        }
        .status-open {
            background: rgba(245, 158, 11, 0.14);
            border-color: rgba(245, 158, 11, 0.3);
            color: #fde68a;
        }
        .btn {
            border: 0;
            border-radius: 12px;
            padding: 12px 16px;
            cursor: pointer;
            font: inherit;
        }
        .btn-primary {
            background: var(--primary);
            color: white;
        }
        .btn-secondary {
            background: var(--soft);
            color: var(--text);
            border: 1px solid var(--line);
        }
        .btn-success {
            background: var(--success);
            color: white;
        }
        .warning {
            border-left: 4px solid var(--warning);
            padding: 12px 14px;
            background: rgba(245, 158, 11, 0.1);
            border-radius: 10px;
            color: #fde68a;
            font-size: 14px;
        }
        .muted { color: var(--muted); }
        .modal-backdrop {
            position: fixed;
            inset: 0;
            background: rgba(2, 6, 23, 0.68);
            display: none;
            align-items: center;
            justify-content: center;
            padding: 24px;
        }
        .modal {
            width: min(720px, 100%);
            background: #0f172a;
            border: 1px solid var(--line);
            border-radius: 18px;
            padding: 20px;
            display: grid;
            gap: 16px;
        }
        .field {
            display: grid;
            gap: 8px;
            font-size: 14px;
            color: var(--muted);
        }
        input, select {
            width: 100%;
            padding: 12px 14px;
            border-radius: 12px;
            border: 1px solid var(--line);
            background: #0b1220;
            color: var(--text);
            font: inherit;
        }
        .hidden { display: none; }
        @media (max-width: 980px) {
            .grid-2, .cards-4 { grid-template-columns: 1fr; }
            .actions, .modal-actions, .row { flex-direction: column; align-items: stretch; }
        }
    </style>
</head>
<body>
    <div class="page">
        <section class="panel">
            <h1 class="title">{{ $pageTitle }}</h1>
            <p class="subtitle">Prototype detail/payment note untuk note id: <strong>{{ $noteId }}</strong></p>
        </section>

        <section class="panel">
            <div class="warning">
                Halaman ini adalah shell UI non-asumsi. Ia sudah mengikuti kontrak handoff:
                create dulu, payment sesudah create, pilih semua/sebagian, modal kalkulator, cash/tf, kembalian hanya cash.
                Tetapi data read-model final dan submit payment final belum di-wire karena kontrak field/reader belum dibuka di konteks ini.
            </div>
        </section>

        <section class="panel grid cards-4">
            <div class="metric">
                <div class="metric-label">Grand Total Nota</div>
                <div class="metric-value" id="grand-total">Rp0</div>
            </div>
            <div class="metric">
                <div class="metric-label">Total Dipilih untuk Dibayar Sekarang</div>
                <div class="metric-value" id="pay-now-total">Rp0</div>
            </div>
            <div class="metric">
                <div class="metric-label">Total Sudah Dibayar</div>
                <div class="metric-value">Rp0</div>
            </div>
            <div class="metric">
                <div class="metric-label">Sisa Tagihan</div>
                <div class="metric-value">Rp0</div>
            </div>
        </section>

        <section class="grid grid-2">
            <div class="panel">
                <div class="actions" style="margin-bottom: 16px;">
                    <div>
                        <h2 style="margin:0 0 6px;">Baris Nota</h2>
                        <p class="subtitle">Checklist hanya menentukan yang dibayar pada payment event saat ini.</p>
                    </div>
                    <div class="actions">
                        <button type="button" class="btn btn-secondary" id="check-all-btn">Checklist Semua</button>
                        <button type="button" class="btn btn-secondary" id="clear-all-btn">Clear Semua</button>
                    </div>
                </div>

                <div class="list" id="work-item-list">
                    <article class="item" data-item data-subtotal="0">
                        <div class="item-head">
                            <div>
                                <strong>Belum ada data real yang di-wire</strong>
                                <div class="muted" style="margin-top: 6px;">Begitu reader detail note dibuka, item real masuk di sini.</div>
                            </div>
                            <span class="badge status-open">open</span>
                        </div>

                        <div class="row">
                            <label style="display:flex; align-items:center; gap:10px;">
                                <input type="checkbox" data-pay-check>
                                <span>Pilih untuk dibayar sekarang</span>
                            </label>
                            <strong data-item-subtotal>Rp0</strong>
                        </div>
                    </article>
                </div>
            </div>

            <div class="panel">
                <h2 style="margin:0 0 12px;">Aksi Nota</h2>
                <div class="list">
                    <button type="button" class="btn btn-success" id="open-payment-btn">Bayar Sekarang</button>
                    <button type="button" class="btn btn-secondary">Skip / Hanya Buka Nota</button>
                    <button type="button" class="btn btn-secondary">Edit Nota (policy akan di-wire dari backend)</button>
                </div>

                <hr style="border-color: rgba(156, 163, 175, 0.18); margin: 18px 0;">

                <div class="list">
                    <div class="row">
                        <span class="muted">Payment Status</span>
                        <span class="badge">unpaid</span>
                    </div>
                    <div class="row">
                        <span class="muted">Workspace / Item Status</span>
                        <span class="badge status-open">open</span>
                    </div>
                    <div class="row">
                        <span class="muted">Editability</span>
                        <span class="badge">policy-driven</span>
                    </div>
                </div>
            </div>
        </section>
    </div>

    <div class="modal-backdrop" id="payment-modal-backdrop">
        <div class="modal">
            <div>
                <h2 style="margin:0 0 6px;">Modal Payment</h2>
                <p class="subtitle">Gaya kalkulator kasir: pilih metode, input uang, lihat kembalian.</p>
            </div>

            <div class="field">
                Total yang Dibayar Sekarang
                <input type="text" id="modal-pay-total" value="Rp0" readonly>
            </div>

            <div class="field">
                Metode Payment
                <select id="payment-method">
                    <option value="cash">cash</option>
                    <option value="tf">tf</option>
                </select>
            </div>

            <div class="field">
                Uang Masuk Sekarang
                <input type="number" id="money-in" min="0" step="1" value="0">
            </div>

            <div class="field" id="change-wrapper">
                Kembalian
                <input type="text" id="change-out" value="Rp0" readonly>
            </div>

            <div class="modal-actions">
                <button type="button" class="btn btn-secondary" id="close-payment-btn">Tutup</button>
                <button type="button" class="btn btn-primary" id="save-payment-btn">Simpan Payment (placeholder)</button>
            </div>
        </div>
    </div>

    <script>
        function rupiah(value) {
            const safeValue = Number.isFinite(value) ? Math.max(0, value) : 0;
            return 'Rp' + new Intl.NumberFormat('id-ID').format(safeValue);
        }

        function getItems() {
            return [...document.querySelectorAll('[data-item]')];
        }

        function selectedTotal() {
            return getItems().reduce((sum, item) => {
                const checked = item.querySelector('[data-pay-check]').checked;
                if (!checked) {
                    return sum;
                }
                return sum + Number(item.dataset.subtotal || 0);
            }, 0);
        }

        function refreshTotals() {
            const grandTotal = getItems().reduce((sum, item) => sum + Number(item.dataset.subtotal || 0), 0);
            const payNow = selectedTotal();

            document.getElementById('grand-total').textContent = rupiah(grandTotal);
            document.getElementById('pay-now-total').textContent = rupiah(payNow);
            document.getElementById('modal-pay-total').value = rupiah(payNow);

            refreshChange();
        }

        function refreshChange() {
            const method = document.getElementById('payment-method').value;
            const payNow = selectedTotal();
            const moneyIn = Number(document.getElementById('money-in').value || 0);
            const changeWrapper = document.getElementById('change-wrapper');
            const changeOut = document.getElementById('change-out');

            if (method === 'tf') {
                changeWrapper.classList.add('hidden');
                changeOut.value = rupiah(0);
                return;
            }

            changeWrapper.classList.remove('hidden');
            changeOut.value = rupiah(Math.max(0, moneyIn - payNow));
        }

        document.getElementById('check-all-btn').addEventListener('click', () => {
            getItems().forEach((item) => {
                item.querySelector('[data-pay-check]').checked = true;
            });
            refreshTotals();
        });

        document.getElementById('clear-all-btn').addEventListener('click', () => {
            getItems().forEach((item) => {
                item.querySelector('[data-pay-check]').checked = false;
            });
            refreshTotals();
        });

        getItems().forEach((item) => {
            item.querySelector('[data-pay-check]').addEventListener('change', refreshTotals);
        });

        document.getElementById('payment-method').addEventListener('change', refreshChange);
        document.getElementById('money-in').addEventListener('input', refreshChange);

        const backdrop = document.getElementById('payment-modal-backdrop');

        document.getElementById('open-payment-btn').addEventListener('click', () => {
            refreshTotals();
            backdrop.style.display = 'flex';
        });

        document.getElementById('close-payment-btn').addEventListener('click', () => {
            backdrop.style.display = 'none';
        });

        document.getElementById('save-payment-btn').addEventListener('click', () => {
            alert('UI modal payment sudah siap. Submit final sengaja belum di-wire agar tidak mengasumsikan kontrak payment request.');
        });

        refreshTotals();
    </script>
</body>
</html>
