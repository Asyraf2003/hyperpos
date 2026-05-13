# Central Documentation Map

Peta navigasi seluruh dokumentasi sistem Hyperpos.

## Struktur Folder (L1)

### [01_standards](./01_standards/)

Aturan wajib untuk semua sesi kerja AI. Bersifat statis, tidak berubah kecuali ada keputusan eksplisit.

- `core/` — prinsip dasar: scope, blueprint-first, step-by-step, proof
- `workflow/` — response structure, active step policy, handoff policy, session capacity
- `output/` — file delivery, markdown rule, blade rule, terminal delivery
- `architecture/` — hexagonal baseline, public contracts, error handling, debug gating, audit-dod
- `domain/` — final domain map, UI terms, payment lifecycle, reporting boundary
- `stack/` — Laravel rules, Go rules, AWS baseline

### [02_architecture](./02_architecture/)

Permanent decision records.

- `adr/` — ADR files. Naming: `NNNN_snake_title.md`. Sequential, tidak menggunakan tanggal.
  Jika keputusan berubah: buat ADR baru yang supersede, tandai ADR lama sebagai superseded.

### [03_blueprints](./03_blueprints/)

Design blueprints, DoD, dan Workflow per topik. Flat dalam subfolder topik.

- `security/` — ADR-0019 s/d ADR-0023: access boundary, public surface, payment concurrency, seeder safety
- `finance/` — note finance stabilization, finance residual, note revision refund ledger
- `reporting/` — report export, reporting execution workflow
- `seeder/` — legacy-to-clean
- `mobile/` — mobile API
- `error_log_remediation/` — DoD, sequence, workflow, strict closure protocol
- `feature_continuation/` — feature continuation blueprint

Naming file: `NNNN_topic_name.md` (blueprint), `NNNN_topic_name_dod.md` (DoD), `NNNN_topic_name_workflow.md` (Workflow).

### [04_lifecycle](./04_lifecycle/)

Runtime records — ongoing, bukan historical.

- `error_log/` — bug dan security findings. Naming: `NNNN_snake_title.md`
- `handoff/` — session recovery notes sesi aktif. Naming: `YYYY-MM-DD-topic-handoff.md`

### [05_audits](./05_audits/)

Formal audit records. Naming: `NNNN_topic_name.md`.

### [99_archive](./99_archive/)

Semua legacy, superseded, historical. Copy penuh, tidak dimodifikasi.

- `standards/` — old standards (handoff-ai-rules-modular)
- `blueprints/` — blueprint v1, workflow v1
- `dod/` — dod v1
- `handoff/` — semua handoff lama (step-based, ui, v2, kotlin, mobile-api, seeder, error_log, codex-security)

---

## Naming Convention

| Jenis | Format | Contoh |
|---|---|---|
| ADR | `NNNN_snake_title.md` | `0019_note_access_boundary_cashier_date_window_and_transaction_capability_enforcement.md` |
| Blueprint | `NNNN_topic_name.md` | `0003_finance_residual.md` |
| DoD | `NNNN_topic_name_dod.md` | `0004_finance_residual_dod.md` |
| Workflow | `NNNN_topic_name_workflow.md` | `0005_finance_residual_workflow.md` |
| Error log | `NNNN_snake_title.md` | `0009_cashiers_can_rewrite_closed_paid_notes_via_workspace_update.md` |
| Audit record | `NNNN_topic_name.md` | `0002_error_log_solution_and_adr_coverage_summary.md` |
| Handoff aktif | `NNNN_topic_handoff.md` | `0001_scope_handoff.md` |
| Folder | `NN_prefix_snake_case` for L1, `snake_case` for subfolders | `01_standards/`, `error_log/` |

## Log Perubahan Struktur

- **2026-05-13**: Reorganisasi penuh ke standard hexagonal docs. Kebab-case konsisten, topik-based blueprint subfolders, semua legacy ke 99_archive, path references difix, duplikat konten dihilangkan.
- **2026-05-11**: Migrasi awal dari flat-legacy ke hybrid L1.
