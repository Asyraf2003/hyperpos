# DB Handoff Archive

## Purpose

Folder ini adalah entrypoint ringan untuk sesi HyperPOS DB hardening dan migration research.

Tujuannya:

- menjaga sesi berikutnya mulai dari proof terakhir
- mencegah re-audit ulang slice yang sudah verified
- memisahkan DB hardening, migration research, dan live transition boundary
- menjaga local command output sebagai source of truth utama

Folder ini tidak mengganti ADR, blueprint, workflow, atau DoD.

## Latest Handoff

Baca file terbaru ini setelah rules/ADR/blueprint DB:

- docs/99_archive/handoff/db/0002_mysql_postgresql_aligned_migration_research_handoff.md

## Previous Handoff

- docs/99_archive/handoff/db/0001_db_hardening_notes_payment_refund_handoff.md

## Source Priority

Gunakan urutan ini:

1. Local command output dari user
2. Current source lokal
3. database/migrations/README.md
4. docs/03_blueprints/db/0003_db_hardening_workflow.md
5. docs/03_blueprints/db/0004_db_audit_matrix.md
6. docs/02_architecture/adr/0028_mysql_to_postgresql_and_api_migration_readiness.md
7. Handoff terbaru di folder ini
8. Handoff lama atau archive
9. Memory atau asumsi

Jika docs dan source konflik, source lokal menang sampai docs diperbarui.

Jika source lokal dan command output konflik dengan remote GitHub, command output lokal menang.

## Current DB Research Position

Live system masih memakai MySQL.

Repo ini sedang dipakai sebagai research/target-schema track agar struktur MySQL makin matang dan PostgreSQL-aligned sebelum future live transition.

Editing historical migrations di research repo tidak otomatis mengubah live MySQL database yang sudah pernah migrated.

Live transition nanti harus memakai explicit forward migration, SQL transform, atau export/import mapping.

## Latest Proven Safe State

- unsigned cleanup verified
- after() layout helper cleanup verified
- fresh MySQL testing migration passed
- database feature tests passed 26 / 241
- make verify passed 1063 / 5769

## Next Active Target

Slice 3:

- classify employee migration change()
- do not patch before source audit

## Markdown Safety Rule

Untuk handoff, prompt sesi berikutnya, dan file Markdown yang dikirim via chat:

- Jangan gunakan triple backtick.
- Jangan gunakan nested fenced block.
- Jangan gunakan fence Markdown di dalam heredoc.
- Jika isi file perlu command, tulis sebagai indented block biasa.
- Sebelum final, grep fence di folder handoff harus kosong.
