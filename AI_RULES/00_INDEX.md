# AI_RULES Index

## Tujuan
AI_RULES adalah constitution modular untuk memastikan GPT/AI assistant bekerja konsisten dengan aturan project, preferensi user, batasan arsitektur, dan kontrak domain yang sudah dikunci.

## Urutan baca wajib
1. `01_DECISION_POLICY.md`
2. `10_CORE/`
3. `20_WORKFLOW/`
4. `40_ARCHITECTURE/`
5. `50_DOMAIN_KASIR/`
6. `60_STACK/`
7. `30_OUTPUT/`
8. `99_CHANGELOG.md`

## Prinsip pakai
- Jangan berasumsi.
- Semua keputusan harus berbasis fakta, bukti, kondisi saat ini, dan tujuan step.
- Mulai dari blueprint.
- Eksekusi step-by-step.
- Satu step aktif per respons kerja.
- Tunggu feedback user sebelum melanjutkan.
- Progres hanya naik jika ada bukti.

## Level prioritas
- P0 = constitution inti, tidak boleh dilanggar tanpa keputusan eksplisit
- P1 = workflow dan architecture enforcement
- P2 = format output dan delivery preference

## Daftar modul
- `01_DECISION_POLICY.md`
- `10_CORE/`
  - `10_SCOPE_AND_FACTS.md`
  - `11_BLUEPRINT_FIRST.md`
  - `12_STEP_BY_STEP_EXECUTION.md`
  - `13_PROOF_AND_PROGRESS.md`
- `20_WORKFLOW/`
  - `20_RESPONSE_STRUCTURE.md`
  - `21_ACTIVE_STEP_POLICY.md`
  - `22_OPTION_EVALUATION.md`
  - `23_HANDOFF_POLICY.md`
- `30_OUTPUT/`
  - `30_FILE_DELIVERY.md`
  - `31_MARKDOWN_OUTPUT_RULE.md`
  - `32_BLADE_RULE.md`
  - `33_TERMINAL_COMMAND_DELIVERY.md`
- `40_ARCHITECTURE/`
  - `40_HEXAGONAL_BASELINE.md`
  - `41_PUBLIC_CONTRACTS.md`
  - `42_ERROR_HANDLING_AND_REDACTION.md`
  - `43_DEBUG_GATING.md`
  - `44_AUDIT_AND_DOD.md`
- `50_DOMAIN_KASIR/`
  - `50_FINAL_DOMAIN_MAP.md`
  - `51_UI_TERMS_AND_STATUS.md`
  - `52_PAYMENT_LIFECYCLE.md`
  - `53_REPORTING_BOUNDARY.md`
- `60_STACK/`
  - `60_LARAVEL_RULES.md`
  - `61_GO_RULES.md`
  - `62_AWS_BASELINE.md`
- `99_CHANGELOG.md`

## Catatan penggunaan untuk GPT lain
Saat ada konflik aturan:
1. Baca `01_DECISION_POLICY.md`
2. Dahulukan P0 atas P1/P2
3. Dahulukan aturan yang lebih spesifik
4. Dahulukan aturan domain jika konflik menyangkut makna bisnis
5. Jika data tidak cukup, tandai sebagai GAP dan jangan mengarang
