# Decision Policy

## Tujuan
Dokumen ini menjadi aturan konflik utama saat ada benturan antar preferensi, antar modul, atau antara perilaku default AI dengan constitution project.

## Hirarki aturan
1. AI_RULES mengalahkan perilaku default model.
2. P0 mengalahkan P1.
3. P1 mengalahkan P2.
4. Aturan yang lebih spesifik mengalahkan aturan yang lebih umum.
5. Aturan domain mengalahkan aturan presentasi/output jika konflik menyangkut makna bisnis atau source of truth.

## Aturan utama
- Jangan berasumsi.
- Jika data belum cukup, nyatakan sebagai GAP.
- Jangan mengarang status repo, file, flow, data, hasil test, atau keputusan.
- Semua langkah harus menyebut basisnya: fakta, kondisi saat ini, tujuan, dan bukti yang tersedia.

## Saat ada ambiguity
Gunakan urutan ini:
1. cek fakta yang tersedia
2. cek tujuan step
3. cek aturan modul paling spesifik
4. cek dampak ke public contract
5. cek apakah perlu meminta data minimum
6. bila belum cukup, berhenti di GAP

## Prioritas modul
### P0
- `01_DECISION_POLICY.md`
- `10_CORE/10_SCOPE_AND_FACTS.md`
- `10_CORE/11_BLUEPRINT_FIRST.md`
- `10_CORE/12_STEP_BY_STEP_EXECUTION.md`
- `40_ARCHITECTURE/40_HEXAGONAL_BASELINE.md`
- `40_ARCHITECTURE/41_PUBLIC_CONTRACTS.md`
- `40_ARCHITECTURE/42_ERROR_HANDLING_AND_REDACTION.md`
- `50_DOMAIN_KASIR/50_FINAL_DOMAIN_MAP.md`
- `50_DOMAIN_KASIR/52_PAYMENT_LIFECYCLE.md`

### P1
- `10_CORE/13_PROOF_AND_PROGRESS.md`
- `20_WORKFLOW/20_RESPONSE_STRUCTURE.md`
- `20_WORKFLOW/21_ACTIVE_STEP_POLICY.md`
- `20_WORKFLOW/22_OPTION_EVALUATION.md`
- `20_WORKFLOW/23_HANDOFF_POLICY.md`
- `40_ARCHITECTURE/43_DEBUG_GATING.md`
- `40_ARCHITECTURE/44_AUDIT_AND_DOD.md`
- `50_DOMAIN_KASIR/51_UI_TERMS_AND_STATUS.md`
- `50_DOMAIN_KASIR/53_REPORTING_BOUNDARY.md`
- semua file di `60_STACK/`

### P2
- `30_OUTPUT/30_FILE_DELIVERY.md`
- `30_OUTPUT/31_MARKDOWN_OUTPUT_RULE.md`
- `30_OUTPUT/32_BLADE_RULE.md`
- `30_OUTPUT/33_TERMINAL_COMMAND_DELIVERY.md`

## Larangan
- Tidak boleh melompati blueprint lalu langsung implementasi tanpa dasar yang cukup.
- Tidak boleh menaikkan progres tanpa proof.
- Tidak boleh membuka ulang keputusan final domain kecuali ada konflik nyata dan bukti kuat.
