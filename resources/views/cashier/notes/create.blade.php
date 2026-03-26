<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $pageTitle }}</title>
    <style>
        :root {
            --bg: #0f172a;
            --panel: #111827;
            --soft: #1f2937;
            --line: #374151;
            --text: #e5e7eb;
            --muted: #9ca3af;
            --primary: #2563eb;
            --danger: #dc2626;
            --success: #16a34a;
        }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            font-family: ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            background: #0b1020;
            color: var(--text);
        }
        .page {
            max-width: 1200px;
            margin: 0 auto;
            padding: 24px;
        }
        .stack { display: grid; gap: 16px; }
        .panel {
            background: rgba(17, 24, 39, 0.92);
            border: 1px solid rgba(55, 65, 81, 0.8);
            border-radius: 16px;
            padding: 20px;
        }
        .title { margin: 0 0 8px; font-size: 28px; font-weight: 700; }
        .subtitle { margin: 0; color: var(--muted); font-size: 14px; }
        .grid {
            display: grid;
            gap: 16px;
        }
        .grid-3 { grid-template-columns: repeat(3, minmax(0, 1fr)); }
        .grid-2 { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        label {
            display: grid;
            gap: 8px;
            font-size: 14px;
            color: var(--muted);
        }
        input, select, textarea, button {
            font: inherit;
        }
        input, select, textarea {
            width: 100%;
            padding: 12px 14px;
            border-radius: 12px;
            border: 1px solid var(--line);
            background: #0b1220;
            color: var(--text);
        }
        .line-card {
            border: 1px solid var(--line);
            border-radius: 14px;
            padding: 16px;
            background: rgba(15, 23, 42, 0.8);
        }
        .line-head, .row-actions, .summary-row, .header-actions {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
        }
        .line-head { margin-bottom: 14px; }
        .line-title {
            font-size: 16px;
            font-weight: 700;
        }
        .btn {
            border: 0;
            border-radius: 12px;
            padding: 12px 16px;
            cursor: pointer;
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
        .btn-danger {
            background: rgba(220, 38, 38, 0.12);
            color: #fecaca;
            border: 1px solid rgba(220, 38, 38, 0.35);
        }
        .summary {
            display: grid;
            gap: 12px;
        }
        .summary-row {
            padding: 12px 0;
            border-bottom: 1px dashed rgba(156, 163, 175, 0.2);
        }
        .summary-row:last-child { border-bottom: 0; }
        .summary-key { color: var(--muted); }
        .summary-value { font-weight: 700; }
        .grand-total {
            font-size: 28px;
            font-weight: 800;
            color: #f8fafc;
        }
        .badge {
            display: inline-flex;
            align-items: center;
            padding: 6px 10px;
            border-radius: 999px;
            font-size: 12px;
            background: rgba(37, 99, 235, 0.16);
            color: #bfdbfe;
            border: 1px solid rgba(37, 99, 235, 0.28);
        }
        .warning {
            border-left: 4px solid #f59e0b;
            padding: 12px 14px;
            background: rgba(245, 158, 11, 0.1);
            border-radius: 10px;
            color: #fde68a;
            font-size: 14px;
        }
        .muted { color: var(--muted); }
        .hidden { display: none; }
        @media (max-width: 960px) {
            .grid-3, .grid-2 { grid-template-columns: 1fr; }
            .header-actions, .row-actions { flex-direction: column; align-items: stretch; }
        }
    </style>
</head>
<body>
    <div class="page stack">
        <section class="panel stack">
            <div class="header-actions">
                <div>
                    <h1 class="title">{{ $pageTitle }}</h1>
                    <p class="subtitle">
                        Shell UI create nota sesuai handoff: 1 nota, baris dinamis produk/servis, grand total otomatis.
                    </p>
                </div>
                <span class="badge">Scaffold aman — belum submit final</span>
            </div>

            <div class="warning">
                Form ini sengaja belum di-wire ke POST final karena kontrak field <strong>CreateNoteRequest</strong> belum dipegang di konteks ini.
                Jadi yang saya bangun dulu adalah UI dan interaksi yang aman, tanpa asumsi payload backend.
            </div>
        </section>

        <section class="panel stack">
            <h2 style="margin:0;">Header Nota</h2>

            <div class="grid grid-3">
                <label>
                    Nama Customer
                    <input type="text" id="customer_name" placeholder="Contoh: Budi / PT Maju Jaya">
                </label>

                <label>
                    Telepon Customer (opsional)
                    <input type="text" id="customer_phone" placeholder="08xxxxxxxxxx">
                </label>

                <label>
                    Tanggal Nota
                    <input type="date" id="note_date" value="{{ $today }}">
                </label>
            </div>
        </section>

        <section class="panel stack">
            <div class="header-actions">
                <div>
                    <h2 style="margin:0 0 6px;">Baris Nota</h2>
                    <p class="subtitle">Tipe final hanya Produk dan Servis.</p>
                </div>
                <button type="button" class="btn btn-primary" id="add-line-btn">Tambah Baris</button>
            </div>

            <div id="line-items" class="stack"></div>

            <div class="row-actions">
                <button type="button" class="btn btn-secondary" id="add-line-bottom-btn">Tambah Baris Lagi</button>
                <button type="button" class="btn btn-secondary" id="reset-lines-btn">Reset Semua Baris</button>
            </div>
        </section>

        <section class="panel stack">
            <h2 style="margin:0;">Ringkasan Nota</h2>

            <div class="summary">
                <div class="summary-row">
                    <div class="summary-key">Jumlah Baris</div>
                    <div class="summary-value" id="summary-line-count">0</div>
                </div>
                <div class="summary-row">
                    <div class="summary-key">Grand Total Nota</div>
                    <div class="grand-total" id="summary-grand-total">Rp0</div>
                </div>
            </div>

            <div class="row-actions">
                <button type="button" class="btn btn-secondary" onclick="window.history.back()">Kembali</button>
                <button type="button" class="btn btn-primary" id="save-note-disabled-btn">Simpan Nota (placeholder)</button>
            </div>
        </section>
    </div>

    <template id="line-item-template">
        <article class="line-card" data-line-card>
            <div class="line-head">
                <div class="line-title">Baris <span data-line-number>1</span></div>
                <button type="button" class="btn btn-danger" data-remove-line>Hapus</button>
            </div>

            <div class="grid grid-2">
                <label>
                    Tipe Baris
                    <select data-line-type>
                        <option value="product">Produk</option>
                        <option value="service">Servis</option>
                    </select>
                </label>

                <label>
                    Qty
                    <input type="number" min="1" step="1" value="1" data-line-qty>
                </label>
            </div>

            <div class="grid grid-2 product-fields">
                <label>
                    Produk
                    <input type="text" data-product-name placeholder="Pilih produk dari master (UI lookup/wiring menyusul)">
                </label>

                <label>
                    Harga Produk
                    <input type="number" min="0" step="1" value="0" data-line-price placeholder="Harga otomatis dari master saat wiring">
                </label>
            </div>

            <div class="grid grid-2 service-fields hidden">
                <label>
                    Nama / Keterangan Servis
                    <input type="text" data-service-name placeholder="Contoh: Ganti oli, servis rem, cek mesin">
                </label>

                <label>
                    Harga Servis
                    <input type="number" min="0" step="1" value="0" data-line-price-service placeholder="Harga manual">
                </label>
            </div>

            <label>
                Catatan Baris
                <textarea rows="2" data-line-note placeholder="Opsional, misalnya pembeli bawa sparepart sendiri"></textarea>
            </label>

            <div class="summary-row" style="margin-top: 8px;">
                <div class="summary-key">Subtotal</div>
                <div class="summary-value" data-line-subtotal>Rp0</div>
            </div>
        </article>
    </template>

    <script>
        const lineItemsContainer = document.getElementById('line-items');
        const lineTemplate = document.getElementById('line-item-template');
        const summaryLineCount = document.getElementById('summary-line-count');
        const summaryGrandTotal = document.getElementById('summary-grand-total');

        function rupiah(value) {
            const safeValue = Number.isFinite(value) ? Math.max(0, value) : 0;
            return 'Rp' + new Intl.NumberFormat('id-ID').format(safeValue);
        }

        function getLineCards() {
            return [...document.querySelectorAll('[data-line-card]')];
        }

        function syncLineNumbers() {
            getLineCards().forEach((card, index) => {
                const el = card.querySelector('[data-line-number]');
                if (el) {
                    el.textContent = String(index + 1);
                }
            });
        }

        function readPrice(card) {
            const type = card.querySelector('[data-line-type]').value;
            if (type === 'service') {
                return Number(card.querySelector('[data-line-price-service]').value || 0);
            }
            return Number(card.querySelector('[data-line-price]').value || 0);
        }

        function updateLineUI(card) {
            const type = card.querySelector('[data-line-type]').value;
            const productFields = card.querySelector('.product-fields');
            const serviceFields = card.querySelector('.service-fields');

            if (type === 'service') {
                productFields.classList.add('hidden');
                serviceFields.classList.remove('hidden');
            } else {
                productFields.classList.remove('hidden');
                serviceFields.classList.add('hidden');
            }
        }

        function updateLineSubtotal(card) {
            const qty = Number(card.querySelector('[data-line-qty]').value || 0);
            const price = readPrice(card);
            const subtotal = Math.max(0, qty) * Math.max(0, price);

            card.dataset.subtotal = String(subtotal);
            card.querySelector('[data-line-subtotal]').textContent = rupiah(subtotal);

            updateSummary();
        }

        function updateSummary() {
            const cards = getLineCards();
            const grandTotal = cards.reduce((sum, card) => sum + Number(card.dataset.subtotal || 0), 0);

            summaryLineCount.textContent = String(cards.length);
            summaryGrandTotal.textContent = rupiah(grandTotal);
        }

        function bindLineCard(card) {
            const typeSelect = card.querySelector('[data-line-type]');
            const qtyInput = card.querySelector('[data-line-qty]');
            const productPriceInput = card.querySelector('[data-line-price]');
            const servicePriceInput = card.querySelector('[data-line-price-service]');
            const removeBtn = card.querySelector('[data-remove-line]');

            typeSelect.addEventListener('change', () => {
                updateLineUI(card);
                updateLineSubtotal(card);
            });

            qtyInput.addEventListener('input', () => updateLineSubtotal(card));
            productPriceInput.addEventListener('input', () => updateLineSubtotal(card));
            servicePriceInput.addEventListener('input', () => updateLineSubtotal(card));

            removeBtn.addEventListener('click', () => {
                card.remove();
                syncLineNumbers();
                updateSummary();
            });

            updateLineUI(card);
            updateLineSubtotal(card);
        }

        function addLine(defaultType = 'product') {
            const fragment = lineTemplate.content.cloneNode(true);
            const card = fragment.querySelector('[data-line-card]');
            card.querySelector('[data-line-type]').value = defaultType;
            lineItemsContainer.appendChild(fragment);
            const insertedCard = getLineCards().at(-1);
            bindLineCard(insertedCard);
            syncLineNumbers();
            updateSummary();
        }

        document.getElementById('add-line-btn').addEventListener('click', () => addLine('product'));
        document.getElementById('add-line-bottom-btn').addEventListener('click', () => addLine('service'));
        document.getElementById('reset-lines-btn').addEventListener('click', () => {
            lineItemsContainer.innerHTML = '';
            updateSummary();
        });

        document.getElementById('save-note-disabled-btn').addEventListener('click', () => {
            alert('UI shell sudah siap. Submit final sengaja belum di-wire agar tidak mengasumsikan payload CreateNoteRequest.');
        });

        addLine('product');
    </script>
</body>
</html>
