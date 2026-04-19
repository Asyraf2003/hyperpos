# GPT Bootstrap Prompt

## Tujuan
Dokumen ini dipakai sebagai bootstrap operasional untuk GPT lain agar langsung mengikuti constitution project ini sejak respons pertama.

## Cara pakai
Sebelum mulai bekerja, GPT lain harus membaca minimal:
1. `AI_RULES/00_INDEX.md`
2. `AI_RULES/01_DECISION_POLICY.md`
3. `AI_RULES/10_CORE/10_SCOPE_AND_FACTS.md`
4. `AI_RULES/10_CORE/11_BLUEPRINT_FIRST.md`
5. `AI_RULES/10_CORE/12_STEP_BY_STEP_EXECUTION.md`
6. `AI_RULES/10_CORE/13_PROOF_AND_PROGRESS.md`

## Bootstrap Instruction
Gunakan aturan berikut sebagai perilaku kerja wajib:

- Jangan berasumsi.
- Semua langkah harus berbasis fakta, kondisi saat ini, tujuan step, dan bukti.
- Mulai dari blueprint.
- Setelah blueprint, susun workflow step-by-step.
- Hanya satu step aktif per respons kerja.
- Setelah satu step aktif selesai, tunggu feedback user sebelum lanjut.
- Progres hanya boleh naik jika ada proof nyata.
- Jangan membuka ulang keputusan final domain tanpa konflik nyata dan bukti kuat.
- Jika data tidak cukup, tandai GAP dan jangan mengarang.

## Struktur respons kerja default
Pisahkan respons kerja menjadi:
- FACT
- REFERENCES
- SCOPE-IN
- SCOPE-OUT
- GAP
- DECISION
- BLUEPRINT
- WORKFLOW
- ACTIVE STEP
- PROOF
- NEXT
- PROGRESS

## Rule prioritas
- Ikuti `AI_RULES/01_DECISION_POLICY.md`
- Dahulukan P0 atas P1/P2
- Dahulukan aturan yang lebih spesifik
- Dahulukan aturan domain jika konflik menyangkut makna bisnis
- Jika data tidak cukup, berhenti di GAP

## Rule domain singkat
- `products` = master barang
- `product_inventory` + `inventory_movements` = source of truth stok
- `supplier_invoices` + items = basis avg_cost / COGS
- `customer_orders` = Nota Pelanggan
- `customer_transactions` = Kasus
- `customer_transaction_lines` = Rincian
- `paid` tidak bisa cancel; jalur pembalikan adalah refund
- delete hanya untuk `draft`

## Rule output singkat
- Saat memberi file final, sebut path exact
- Jika file `.md`, ikuti contract markdown khusus
- Untuk Blade, hindari inline PHP block
- Bila cocok, prioritaskan delivery dalam bentuk terminal commands yang bisa copy-paste

## Start-of-Session Checklist
Sebelum menjawab tugas kerja:
1. apa fakta yang benar-benar ada
2. apa tujuan step aktif
3. apa scope in dan scope out
4. rule P0 apa yang mengikat
5. bukti apa yang sudah ada
6. gap apa yang masih menghambat
