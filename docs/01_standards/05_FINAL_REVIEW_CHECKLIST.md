# Final Review Checklist

## Tujuan
Checklist ini dipakai untuk memeriksa apakah paket AI_RULES masih utuh, terbaca, dan konsisten untuk dipakai GPT lain.

## Checklist Struktur
- `00_INDEX.md` ada
- `01_DECISION_POLICY.md` ada
- `02_GPT_BOOTSTRAP_PROMPT.md` ada
- `03_SESSION_START_PROTOCOL.md` ada
- `04_HANDOFF_TEMPLATE.md` ada
- seluruh folder `10_CORE`, `20_WORKFLOW`, `30_OUTPUT`, `40_ARCHITECTURE`, `50_DOMAIN_KASIR`, `60_STACK` ada
- `99_CHANGELOG.md` ada

## Checklist Isi Minimum
- `00_INDEX.md` memuat mandatory read order
- `01_DECISION_POLICY.md` memuat rule hierarchy dan GAP rule
- `10_CORE/11_BLUEPRINT_FIRST.md` memuat implementation gate
- `10_CORE/12_STEP_BY_STEP_EXECUTION.md` memuat one active step rule
- `10_CORE/13_PROOF_AND_PROGRESS.md` memuat progress hanya naik jika ada proof
- `50_DOMAIN_KASIR/50_FINAL_DOMAIN_MAP.md` memuat final domain map
- `50_DOMAIN_KASIR/52_PAYMENT_LIFECYCLE.md` memuat rule refund vs cancel
- `30_OUTPUT/31_MARKDOWN_OUTPUT_RULE.md` memuat contract markdown
- `02_GPT_BOOTSTRAP_PROMPT.md` memuat start-of-session checklist
- `04_HANDOFF_TEMPLATE.md` memuat section proof dan next step

## Checklist Operasional
- GPT lain bisa membaca `00_INDEX.md` lalu tahu urutan baca
- GPT lain bisa memakai `02_GPT_BOOTSTRAP_PROMPT.md` sebagai bootstrap
- GPT lain bisa membuka sesi dengan `03_SESSION_START_PROTOCOL.md`
- GPT lain bisa menutup slice dengan `04_HANDOFF_TEMPLATE.md`

## Rule Penutupan
Jika semua checklist di atas terpenuhi, paket AI_RULES dapat dianggap siap pakai secara operasional.
