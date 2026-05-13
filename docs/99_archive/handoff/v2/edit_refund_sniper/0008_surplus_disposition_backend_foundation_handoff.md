# Handoff 0008 - Surplus Disposition Backend Foundation

## Metadata

- Date: 2026-05-13
- Sequence: 0008
- Scope: canonical audit writer, surplus disposition migration, and surplus disposition adapter foundation
- Previous handoff: docs/99_archive/handoff/v2/edit_refund_sniper/0007_surplus_disposition_source_audit_handoff.md
- Status: backend foundation partially implemented and locally verified
- Owner workflow: owner handles commit and push manually

## Session Goal

Continue HyperPOS edit/refund sniper chain from surplus disposition source audit.

The session goal was to determine whether refund_due-only surplus disposition could safely write canonical audit_events and audit_event_snapshots, then build the minimum backend foundation before any UI/report/controller work.

## Facts

Required files read at session start:

- docs/01_standards/0001_index.md
- docs/01_standards/0002_decision_policy.md
- docs/99_archive/handoff/v2/edit_refund_sniper/README.md
- docs/99_archive/handoff/v2/edit_refund_sniper/SESSION_CONTRACT.md
- docs/99_archive/handoff/v2/edit_refund_sniper/0007_surplus_disposition_source_audit_handoff.md
- docs/02_architecture/adr/0026_note_revision_surplus_disposition.md
- docs/02_architecture/adr/0027_note_revision_surplus_disposition_transaction_contract.md
- docs/02_architecture/adr/0028_mysql_to_postgresql_and_api_migration_readiness.md

Locked baseline facts from owner:

- Owner always handles commit and push manually.
- Local and repo are identical after push except ignored files.
- Owner statement clean, pushed, latest, or make verify pass is FACT.
- Local command output and owner statement win over GitHub or docs when there is conflict.
- Do not ask for git status/log/diff/diff --check/make verify as ritual.
- Git and make verify are used only when there is a real trigger.

Session source audit facts:

- audit_events and audit_event_snapshots schema already existed.
- AuditLogPort existed but wrote to audit_logs through DatabaseAuditLogAdapter.
- audit_logs remains legacy or generic compatibility.
- audit_events is the canonical audit spine direction.
- Admin audit read side already reads audit_logs and audit_events together.
- Existing product, employee, and procurement code had ad-hoc audit_events writes.
- No canonical generic AuditEventWriterPort existed before this session.
- TransactionManagerPort and DatabaseTransactionManagerAdapter already existed.
- note_revision_settlements already existed as settlement snapshot table.
- NoteRevisionSettlement supports underpaid, paid, and overpaid_pending only.
- note_revision_surplus_dispositions did not exist before this session.

## Completed Work

### 1. Canonical audit writer foundation

New files:

- app/Application/Audit/DTO/AuditEventSnapshotWrite.php
- app/Application/Audit/DTO/AuditEventWrite.php
- app/Ports/Out/AuditEventWriterPort.php
- app/Adapters/Out/Audit/DatabaseAuditEventWriterAdapter.php
- tests/Feature/AuditLog/DatabaseAuditEventWriterAdapterTest.php

Modified file:

- app/Providers/HexagonalServiceProvider.php

Behavior added:

- AuditEventWriterPort writes canonical audit_events.
- DatabaseAuditEventWriterAdapter writes audit_events and optional audit_event_snapshots.
- AuditEventWrite validates required identity fields.
- AuditEventWrite rejects duplicate snapshot_kind.
- Empty metadata is stored as null.
- Snapshots are JSON payloads but not primary finance truth.
- Writer participates in outer DB transaction through same Laravel DB connection.

Proof:

    No syntax errors detected in app/Application/Audit/DTO/AuditEventSnapshotWrite.php
    No syntax errors detected in app/Application/Audit/DTO/AuditEventWrite.php
    No syntax errors detected in app/Ports/Out/AuditEventWriterPort.php
    No syntax errors detected in app/Adapters/Out/Audit/DatabaseAuditEventWriterAdapter.php
    No syntax errors detected in app/Providers/HexagonalServiceProvider.php
    No syntax errors detected in tests/Feature/AuditLog/DatabaseAuditEventWriterAdapterTest.php

    PASS  Tests\Feature\AuditLog\DatabaseAuditEventWriterAdapterTest
    writer persists audit event with before and after snapshots
    writer rejects duplicate snapshot kind before database write
    writer participates in outer database transaction

    Tests: 3 passed, 9 assertions

### 2. Surplus disposition migration foundation

New files:

- database/migrations/2026_05_13_000200_create_note_revision_surplus_dispositions_table.php
- tests/Feature/Database/NoteRevisionSurplusDispositionMigrationTest.php

Table created:

- note_revision_surplus_dispositions

Columns:

- id
- note_revision_settlement_id
- note_root_id
- note_revision_id
- disposition_type
- amount_rupiah
- before_pending_rupiah
- after_pending_rupiah
- status
- occurred_at
- created_at
- updated_at
- audit_event_id

Indexes and constraints:

- unique audit_event_id
- index note_revision_settlement_id
- index note_root_id
- index note_root_id plus status
- index note_revision_settlement_id plus status
- index note_root_id plus occurred_at
- FK note_revision_settlement_id to note_revision_settlements.id restrict on delete
- FK note_revision_id to note_revisions.id restrict on delete
- FK note_root_id to notes.id restrict on delete
- FK audit_event_id to audit_events.id restrict on delete

Proof:

    No syntax errors detected in database/migrations/2026_05_13_000200_create_note_revision_surplus_dispositions_table.php
    No syntax errors detected in tests/Feature/Database/NoteRevisionSurplusDispositionMigrationTest.php

    PASS  Tests\Feature\Database\NoteRevisionSurplusDispositionMigrationTest
    note revision surplus dispositions table exists with expected columns
    note revision surplus dispositions indexes and foreign keys exist

    Tests: 2 passed, 32 assertions

### 3. Surplus disposition adapter foundation

New files:

- app/Application/Note/DTO/NoteRevisionSurplusDisposition.php
- app/Application/Note/DTO/NoteRevisionSurplusPending.php
- app/Ports/Out/Note/NoteRevisionSurplusDispositionReaderPort.php
- app/Ports/Out/Note/NoteRevisionSurplusDispositionWriterPort.php
- app/Adapters/Out/Note/DatabaseNoteRevisionSurplusDispositionAdapter.php
- tests/Feature/Note/DatabaseNoteRevisionSurplusDispositionAdapterTest.php

Modified file:

- app/Providers/HexagonalServiceProvider.php

Behavior added:

- NoteRevisionSurplusDisposition supports refund_due only.
- NoteRevisionSurplusDisposition supports active status only.
- Disposition amount must be positive.
- before_pending_rupiah and after_pending_rupiah must be non-negative.
- after_pending_rupiah must equal before_pending_rupiah minus amount_rupiah.
- NoteRevisionSurplusPending computes unresolved pending as surplus minus active disposition amount.
- Reader ignores non-overpaid_pending settlements.
- Writer persists refund_due disposition rows.

Proof:

    No syntax errors detected in app/Application/Note/DTO/NoteRevisionSurplusDisposition.php
    No syntax errors detected in app/Application/Note/DTO/NoteRevisionSurplusPending.php
    No syntax errors detected in app/Ports/Out/Note/NoteRevisionSurplusDispositionReaderPort.php
    No syntax errors detected in app/Ports/Out/Note/NoteRevisionSurplusDispositionWriterPort.php
    No syntax errors detected in app/Adapters/Out/Note/DatabaseNoteRevisionSurplusDispositionAdapter.php
    No syntax errors detected in app/Providers/HexagonalServiceProvider.php
    No syntax errors detected in tests/Feature/Note/DatabaseNoteRevisionSurplusDispositionAdapterTest.php

    PASS  Tests\Feature\Note\DatabaseNoteRevisionSurplusDispositionAdapterTest
    writer persists refund due surplus disposition
    reader returns unresolved pending after active disposition
    reader ignores non overpaid pending settlement

    Tests: 3 passed, 10 assertions

## Decisions

Decision source: ADR 0027 and current source proof.

- refund_due-only remains the first surplus disposition implementation target.
- customer_credit remains blocked until customer identity is locked.
- customer_balance_entries remains out of scope.
- refund_paid execution remains out of scope.
- audit_events and audit_event_snapshots are canonical for new finance-sensitive audit.
- audit_logs remains legacy or compatibility storage.
- note_revision_settlements remains settlement snapshot, not lifecycle ledger.
- note_revision_surplus_dispositions is the explicit transaction table for surplus disposition decisions.
- Do not create UI or reports before backend truth and use case are tested.

Decision source: ADR 0028.

- New table uses string ids.
- New table uses string status, not MySQL enum.
- New table uses integer rupiah, not float/decimal.
- New table uses explicit occurred_at.
- JSON is not primary finance truth.
- Financial history uses restrict-on-delete FKs.
- PostgreSQL implementation remains out of scope.
- Go API implementation remains out of scope.

## Current State

Backend foundation now has:

- canonical audit writer foundation
- surplus disposition migration
- surplus disposition DTOs
- surplus disposition reader/writer ports
- surplus disposition DB adapter
- focused proof for each foundation slice

Backend foundation does not yet have:

- use case orchestration
- transaction boundary around read pending, audit write, and disposition write
- admin actor validation
- reason validation in use case
- amount validation against unresolved pending in use case
- audit event snapshot payload generated by use case
- integration proof that disposition and audit commit/rollback together

## Files Changed In Session

New:

- app/Application/Audit/DTO/AuditEventSnapshotWrite.php
- app/Application/Audit/DTO/AuditEventWrite.php
- app/Ports/Out/AuditEventWriterPort.php
- app/Adapters/Out/Audit/DatabaseAuditEventWriterAdapter.php
- tests/Feature/AuditLog/DatabaseAuditEventWriterAdapterTest.php
- database/migrations/2026_05_13_000200_create_note_revision_surplus_dispositions_table.php
- tests/Feature/Database/NoteRevisionSurplusDispositionMigrationTest.php
- app/Application/Note/DTO/NoteRevisionSurplusDisposition.php
- app/Application/Note/DTO/NoteRevisionSurplusPending.php
- app/Ports/Out/Note/NoteRevisionSurplusDispositionReaderPort.php
- app/Ports/Out/Note/NoteRevisionSurplusDispositionWriterPort.php
- app/Adapters/Out/Note/DatabaseNoteRevisionSurplusDispositionAdapter.php
- tests/Feature/Note/DatabaseNoteRevisionSurplusDispositionAdapterTest.php

Modified:

- app/Providers/HexagonalServiceProvider.php

Not touched:

- routes/*
- resources/*
- app/Adapters/In/Http/*
- app/Adapters/Out/Reporting/*
- app/Ports/Out/AuditLogPort.php
- app/Adapters/Out/Audit/DatabaseAuditLogAdapter.php
- existing note revision mutation use cases

## Tests Run

Targeted audit writer:

    php artisan test tests/Feature/AuditLog/DatabaseAuditEventWriterAdapterTest.php

Result:

    PASS
    Tests: 3 passed, 9 assertions

Targeted surplus disposition migration:

    php artisan test tests/Feature/Database/NoteRevisionSurplusDispositionMigrationTest.php

Result:

    PASS
    Tests: 2 passed, 32 assertions

Targeted surplus disposition adapter:

    php artisan test tests/Feature/Note/DatabaseNoteRevisionSurplusDispositionAdapterTest.php

Result:

    PASS
    Tests: 3 passed, 10 assertions

Not run:

- focused Note suite
- focused AuditLog suite
- make verify
- browser/manual QA

Reason:

This session is still backend foundation and not final safe-state closure. Owner does not require make verify ritual unless triggered.

## Residual Gaps

Blocking next backend slice:

- CreateNoteRevisionSurplusRefundDue use case is missing.
- Use case transaction orchestration is missing.
- Use case audit payload contract is missing.
- Use case test for rollback is missing.

Not blocking refund_due-only:

- customer identity contract, because customer_credit is out of scope.
- customer_balance_entries, because customer_credit and credit_used are out of scope.
- refund_paid execution, because refund_due is not refund_paid.
- UI and reports, because backend truth is not fully done yet.
- PostgreSQL implementation.
- Go API implementation.

## Next Active Step

Goal:

Build use case CreateNoteRevisionSurplusRefundDue.

Scope in:

- application use case only
- input DTO or command object
- result DTO
- admin actor role validation
- non-empty reason validation
- amount validation against unresolved pending
- generate disposition id and audit event id
- create canonical audit event
- create before and after audit snapshots
- create note_revision_surplus_dispositions row
- wrap all writes in TransactionManagerPort
- rollback if audit writer or disposition writer fails

Suggested files:

- app/Application/Note/UseCases/CreateNoteRevisionSurplusRefundDueHandler.php
- app/Application/Note/UseCases/CreateNoteRevisionSurplusRefundDueCommand.php
- app/Application/Note/UseCases/CreateNoteRevisionSurplusRefundDueResult.php
- tests/Feature/Note/CreateNoteRevisionSurplusRefundDueHandlerTest.php

Existing ports to use:

- App\Ports\Out\Note\NoteRevisionSurplusDispositionReaderPort
- App\Ports\Out\Note\NoteRevisionSurplusDispositionWriterPort
- App\Ports\Out\AuditEventWriterPort
- App\Ports\Out\TransactionManagerPort
- App\Ports\Out\UuidPort
- App\Ports\Out\ClockPort

Scope out:

- controller
- route
- Blade
- report query
- refund_paid
- customer_credit
- customer_balance_entries
- PostgreSQL
- Go API

Expected use case proof:

- rejects non-admin actor
- rejects empty reason
- rejects missing/invalid pending settlement
- rejects amount greater than unresolved pending
- writes audit_events and audit_event_snapshots
- writes note_revision_surplus_dispositions
- updates unresolved pending after write
- rolls back audit event and disposition row when second write fails

## Session Context Health

82 percent.

Reason:

This session added several backend foundation files, changed the active next step, and accumulated enough proof that the next session should start from this handoff instead of reconstructing the chain from memory.

## Next Session Opening Prompt

    Kita lanjut HyperPOS dari edit/refund sniper handoff 0008.

    Baca berurutan:
    docs/01_standards/0001_index.md
    docs/01_standards/0002_decision_policy.md
    docs/99_archive/handoff/v2/edit_refund_sniper/README.md
    docs/99_archive/handoff/v2/edit_refund_sniper/SESSION_CONTRACT.md
    docs/99_archive/handoff/v2/edit_refund_sniper/0008_surplus_disposition_backend_foundation_handoff.md
    docs/02_architecture/adr/0026_note_revision_surplus_disposition.md
    docs/02_architecture/adr/0027_note_revision_surplus_disposition_transaction_contract.md
    docs/02_architecture/adr/0028_mysql_to_postgresql_and_api_migration_readiness.md

    Baseline FACT:
    - Saya selalu push setiap aksi.
    - Local dan repo identik setelah push kecuali ignored files.
    - Kalau saya menyatakan clean, pushed, latest, atau make verify pass, itu FACT.
    - Local command output dan owner statement menang atas GitHub/docs kalau ada konflik.
    - Jangan minta git status/log/diff/diff --check/make verify sebagai ritual.
    - Git dan make verify hanya dipakai kalau ada trigger nyata.

    Latest completed:
    - Canonical audit writer foundation exists and targeted proof passed 3 tests / 9 assertions.
    - note_revision_surplus_dispositions migration exists and targeted proof passed 2 tests / 32 assertions.
    - NoteRevisionSurplusDisposition DTO, NoteRevisionSurplusPending DTO, reader/writer ports, DB adapter, and binding exist.
    - Surplus disposition adapter targeted proof passed 3 tests / 10 assertions.
    - refund_due-only remains the active target.
    - customer_credit remains blocked until customer identity is locked.
    - customer_balance_entries is out of scope.
    - refund_paid execution is out of scope.
    - PostgreSQL implementation is out of scope.
    - Go API implementation is out of scope.

    Current active target:
    Build use case CreateNoteRevisionSurplusRefundDue.

    Required scope:
    - Do not start from UI.
    - Do not start from report query.
    - Do not create controller or route yet.
    - Do not use audit_logs JSON as final finance audit truth.
    - Use AuditEventWriterPort.
    - Use NoteRevisionSurplusDispositionReaderPort.
    - Use NoteRevisionSurplusDispositionWriterPort.
    - Use TransactionManagerPort.
    - Prove audit event and disposition row commit or rollback together.

    Required response shape:
    FACT
    GAP
    ASSUMPTION
    DECISION
    ACTIVE STEP
    FILES TO TOUCH
    FILES NOT TO TOUCH
    COMMAND
    EXPECTED PROOF
    NEXT
