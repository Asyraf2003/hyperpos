# Handoff 0002 - Revision Settlement Foundation

## Metadata

- Date: 2026-05-13
- Sequence: 0002
- Scope: revision settlement foundation
- Previous handoff: docs/99_archive/handoff/v2/edit_refund_sniper/0001_verify_baseline_and_next_session_handoff.md
- Latest proven commit or push proof: Owner reported make verify clean and git/local updated latest after Phase 1A and Phase 1B. Exact full make verify output was not pasted in this chat, but this is accepted as session proof under the locked sniper baseline policy.

## Status

implementation verified

This handoff records the first DB-backed revision settlement foundation.

This is not the final downward-surplus commit feature.

## Session Goal

Build the first serious backend foundation for note revision settlement.

The goal was to stop treating downward revision surplus as hidden money or UI-only text, while still avoiding a risky jump into refund/customer-credit/reporting behavior.

## Facts

- Owner confirmed baseline policy:
  - local and repo are kept identical by owner workflow except ignored files
  - owner pushes after each action
  - git status/log/diff must not be requested as ritual
  - make verify is run at end-of-session or final safe-state proof, not after every small patch
  - targeted and focused tests are sufficient for intermediate implementation proof
- Owner confirmed final domain direction for surplus:
  - surplus is pending undecided money state
  - surplus is not revenue
  - surplus is not automatic refund
  - surplus is not automatic customer credit
  - surplus must remain flexible and hexagonal for future owner decision
- Source audit found:
  - note revision existed as snapshot foundation
  - note_revision_settlements did not exist in app/database/tests before this slice
  - customer_balance_entries did not exist and was deferred
  - CreateNoteRevisionWorkflow locked root note with getByIdForUpdate
  - CreateNoteRevisionWorkflow applied replacement before revision commit
  - CreateNoteRevisionCommitter previously persisted revision, current pointer, and audit only
  - NoteReplacementPaymentAllocationReconciler captured net payment replay and current downward overpaid path rejected through allocator rollback
- Phase 1A created isolated settlement foundation.
- Phase 1B integrated settlement persistence for successful revision commits only.
- Downward surplus commit remains out of scope and still relies on existing reject-and-rollback containment from error log 0005.

## Gaps

- Downward surplus revision commit is not implemented.
- Customer balance lifecycle is not implemented.
- Surplus decision workflow is not implemented.
- Surplus is not integrated into reporting, by design.
- No final decision exists yet for whether pending surplus becomes:
  - refund due
  - customer credit
  - owner-retained balance
  - another explicit lifecycle state
- Exact full make verify terminal output was not pasted into this chat, but owner reported make verify clean and git updated latest. This is accepted under the session baseline policy.

## Assumptions

No implementation assumption accepted.

## Baseline Proof Policy Correction

The following is FACT for future sessions, not GAP and not ASSUMPTION:

- Owner keeps local and repo identical after each action, except ignored files.
- Owner manually handles commit and push.
- Owner runs make verify at end-of-session or final safe-state proof.
- If owner states clean, pushed, latest, or make verify clean, that statement is accepted as baseline proof.
- Do not ask for git status, git log, git diff, or make verify as session-entry ritual.
- Do not write "no local proof" as a GAP when owner already provided the baseline statement.
- Do not write "I assume local and repo are identical" as an ASSUMPTION. Write it as FACT under this policy.
- Use GitHub main/current repo source as current source when owner says latest/pushed, unless newer local command output contradicts it.
- If newer local command output contradicts GitHub or docs, local command output wins.

Valid GAP examples for future sessions:

- a specific source file for the active slice has not been read
- a RED test has not been created yet
- a domain decision has not been locked
- a consumer behavior has not been proven
- source and docs conflict
- test output shows a new failure

Invalid GAP examples for future sessions:

- baseline make verify proof missing
- baseline git status missing
- local and repo identity unproven
- GitHub source is only an assumption
- owner push workflow needs re-verification as ceremony

Operational fact:

- Owner-reported clean make verify and latest git update are accepted as baseline proof for handoff continuity.
- This must not be repeated as a GAP or ASSUMPTION in future sessions.

Critical assumptions explicitly rejected:

- Do not assume overpaid_pending can be reported as revenue.
- Do not assume overpaid_pending is customer credit.
- Do not assume overpaid_pending is refund paid.
- Do not assume downward surplus revision can commit safely just because settlement storage now exists.

## Decisions

- Decision source: owner statement.
  - Surplus is pending undecided money state.
  - It must be DB-backed and flexible.
  - It must not enter reports as revenue or final money disposition.

- Decision source: finance blueprint 0006 and owner refinement.
  - First DB-backed concept is note_revision_settlements.
  - customer_balance_entries is deferred until owner decides the surplus disposition workflow.

- Decision source: source audit.
  - Do not patch generic readers.
  - Do not alter payment replay in this slice.
  - Do not touch UI, report, refund, inventory, or customer balance in this slice.

- Decision source: PostgreSQL portability requirement from owner.
  - Migration uses bigInteger for money, not unsigned-only semantics.
  - Migration uses string settlement_status, not database enum.
  - Migration stores first-class settlement columns, not JSON financial truth.
  - Migration adds read-path indexes.

## Active Slice

Selected active slice:

Revision Settlement Foundation.

Scope in:

- note_revision_settlements table
- revision settlement DTO
- settlement classification builder
- settlement writer and reader ports
- settlement database adapter
- service to build settlement during create-note-revision flow
- successful revision commit persistence
- targeted and focused tests

Scope out:

- downward surplus revision commit
- customer_balance_entries
- refund due workflow
- customer credit workflow
- owner-retained surplus workflow
- reporting mode changes
- UI changes
- API changes
- inventory effect changes
- payment replay semantics changes

Files to touch during completed slice:

- database/migrations/2026_05_13_000100_create_note_revision_settlements_table.php
- app/Application/Note/DTO/NoteRevisionSettlement.php
- app/Application/Note/Services/BuildNoteRevisionSettlement.php
- app/Application/Note/Services/BuildCreateNoteRevisionSettlement.php
- app/Ports/Out/Note/NoteRevisionSettlementWriterPort.php
- app/Ports/Out/Note/NoteRevisionSettlementReaderPort.php
- app/Adapters/Out/Note/DatabaseNoteRevisionSettlementAdapter.php
- app/Providers/HexagonalServiceProvider.php
- tests/Unit/Application/Note/Services/BuildNoteRevisionSettlementTest.php
- tests/Unit/Application/Note/Services/BuildCreateNoteRevisionSettlementTest.php
- tests/Feature/Note/DatabaseNoteRevisionSettlementAdapterTest.php
- tests/Feature/Note/CashierNoteRevisionSubmitFeatureTest.php

Files not touched by design:

- app/Application/Note/Services/NoteReplacementPaymentAllocationReconciler.php
- app/Application/Payment/Services/AllocatePaymentAcrossComponents.php
- app/Application/Note/Services/ApplyNoteRevisionAsActiveReplacement.php
- app/Application/Inventory/*
- app/Adapters/Out/Reporting/*
- resources/views/*
- public/assets/static/js/*
- customer balance files

DB impact:

- Additive table: note_revision_settlements.
- No destructive schema change.
- No FK added in first slice.
- Indexes added:
  - unique note_revision_id
  - note_root_id
  - settlement_status
  - note_root_id plus settlement_status
  - note_root_id plus created_at

UI impact:

- None.

Report impact:

- None.
- Surplus is explicitly not report revenue in this slice.

API impact:

- None.

Audit impact:

- Existing revision audit remains.
- Settlement row is persisted inside the same revision transaction flow for successful revisions.
- Dedicated settlement audit enrichment remains future work.

## Source Audit Summary

- path: app/Application/Note/UseCases/CreateNoteRevisionWorkflow.php
  - relevant method: execute
  - current behavior after patch: builds replacement, creates revision id and createdAt, builds revision settlement, applies active replacement, creates revision object, commits revision plus settlement
  - risk: downward surplus still fails in applier/replay path before commit
  - scope: in scope for successful revision settlement persistence

- path: app/Application/Note/UseCases/CreateNoteRevisionCommitter.php
  - relevant method: commit
  - current behavior after patch: creates revision, creates optional settlement, updates current revision pointer, records audit
  - risk: settlement writer failure rolls back through existing transaction boundary
  - scope: in scope

- path: app/Application/Note/Services/BuildCreateNoteRevisionSettlement.php
  - relevant method: build
  - current behavior: uses component payment/refund totals if component settlement exists, otherwise falls back to legacy payment/refund totals
  - risk: source semantics must not be changed casually because component-vs-legacy distinction protects existing mixed history
  - scope: in scope

- path: app/Application/Note/Services/BuildNoteRevisionSettlement.php
  - relevant method: build
  - current behavior: calculates net paid, outstanding, surplus, and status
  - risk: overpaid_pending is classification only, not final money disposition
  - scope: in scope

- path: app/Application/Note/Services/NoteReplacementPaymentAllocationReconciler.php
  - relevant method: captureAllocatedAmounts, rebuild
  - current behavior: captures payment replay net of refunds and rebuilds allocations using full amount; downward overpaid revision still rejects and rolls back
  - risk: changing this file is Phase 1C or later, not Phase 1B
  - scope: out of scope for this session

- path: tests/Feature/Note/CashierNoteRevisionSubmitFeatureTest.php
  - relevant test: workspace update route creates new revision instead of overwriting root identity
  - current behavior after patch: asserts note_revision_settlements row is created for successful revision
  - risk: cashier path tested; broader admin path should be covered by wider suite or later focused test if needed
  - scope: in scope

## Files Changed

Implementation files:

- database/migrations/2026_05_13_000100_create_note_revision_settlements_table.php
- app/Application/Note/DTO/NoteRevisionSettlement.php
- app/Application/Note/Services/BuildNoteRevisionSettlement.php
- app/Application/Note/Services/BuildCreateNoteRevisionSettlement.php
- app/Ports/Out/Note/NoteRevisionSettlementWriterPort.php
- app/Ports/Out/Note/NoteRevisionSettlementReaderPort.php
- app/Adapters/Out/Note/DatabaseNoteRevisionSettlementAdapter.php
- app/Providers/HexagonalServiceProvider.php

Test files:

- tests/Unit/Application/Note/Services/BuildNoteRevisionSettlementTest.php
- tests/Unit/Application/Note/Services/BuildCreateNoteRevisionSettlementTest.php
- tests/Feature/Note/DatabaseNoteRevisionSettlementAdapterTest.php
- tests/Feature/Note/CashierNoteRevisionSubmitFeatureTest.php

Docs created by this handoff step:

- docs/99_archive/handoff/v2/edit_refund_sniper/0002_revision_settlement_foundation_handoff.md

README update required:

- docs/99_archive/handoff/v2/edit_refund_sniper/README.md latest handoff pointer must point to this file.

## Tests And Proof

Phase 1A syntax proof:

- database/migrations/2026_05_13_000100_create_note_revision_settlements_table.php: no syntax errors
- app/Application/Note/DTO/NoteRevisionSettlement.php: no syntax errors
- app/Application/Note/Services/BuildNoteRevisionSettlement.php: no syntax errors
- app/Ports/Out/Note/NoteRevisionSettlementWriterPort.php: no syntax errors
- app/Ports/Out/Note/NoteRevisionSettlementReaderPort.php: no syntax errors
- app/Adapters/Out/Note/DatabaseNoteRevisionSettlementAdapter.php: no syntax errors
- app/Providers/HexagonalServiceProvider.php: no syntax errors

Phase 1A targeted proof:

    php artisan test tests/Unit/Application/Note/Services/BuildNoteRevisionSettlementTest.php

Result:

    5 passed, 15 assertions

Phase 1A adapter proof:

    php artisan test tests/Feature/Note/DatabaseNoteRevisionSettlementAdapterTest.php

Result:

    3 passed, 7 assertions

Phase 1A focused proof with existing #005 containment:

    php artisan test tests/Unit/Application/Note/Services/BuildNoteRevisionSettlementTest.php tests/Feature/Note/DatabaseNoteRevisionSettlementAdapterTest.php tests/Feature/Note/NoteReplacementOverpaidAllocationReplayFeatureTest.php

Result:

    9 passed, 30 assertions

Line-count proof:

    app/Application/Note/DTO/NoteRevisionSettlement.php: 94
    app/Application/Note/Services/BuildNoteRevisionSettlement.php: 60
    app/Ports/Out/Note/NoteRevisionSettlementWriterPort.php: 12
    app/Ports/Out/Note/NoteRevisionSettlementReaderPort.php: 17
    app/Adapters/Out/Note/DatabaseNoteRevisionSettlementAdapter.php: 73

Phase 1B syntax proof:

- app/Application/Note/Services/BuildCreateNoteRevisionSettlement.php: no syntax errors
- app/Application/Note/UseCases/CreateNoteRevisionWorkflow.php: no syntax errors
- app/Application/Note/UseCases/CreateNoteRevisionCommitter.php: no syntax errors
- app/Providers/HexagonalServiceProvider.php: no syntax errors
- tests/Feature/Note/CashierNoteRevisionSubmitFeatureTest.php: no syntax errors

Phase 1B line-count proof:

    app/Application/Note/Services/BuildCreateNoteRevisionSettlement.php: 60
    app/Application/Note/UseCases/CreateNoteRevisionWorkflow.php: 88
    app/Application/Note/UseCases/CreateNoteRevisionCommitter.php: 54

Phase 1B targeted revision proof:

    php artisan test tests/Feature/Note/CashierNoteRevisionSubmitFeatureTest.php

Result:

    2 passed, 15 assertions

Phase 1B focused proof:

    php artisan test tests/Unit/Application/Note/Services/BuildNoteRevisionSettlementTest.php tests/Feature/Note/DatabaseNoteRevisionSettlementAdapterTest.php tests/Feature/Note/CashierNoteRevisionSubmitFeatureTest.php tests/Feature/Note/NoteReplacementOverpaidAllocationReplayFeatureTest.php

Result:

    11 passed, 45 assertions

Test-only hardening failure:

- BuildCreateNoteRevisionSettlementTest initially failed because anonymous PaymentAllocationReaderPort stub lacked getTotalAllocatedAmountByCustomerPaymentIdAndNoteId.
- This was a test stub contract issue, not a production bug.
- Fixed by implementing the missing stub method.

Final focused proof after test-only hardening:

    php artisan test tests/Unit/Application/Note/Services/BuildCreateNoteRevisionSettlementTest.php tests/Unit/Application/Note/Services/BuildNoteRevisionSettlementTest.php tests/Feature/Note/DatabaseNoteRevisionSettlementAdapterTest.php tests/Feature/Note/CashierNoteRevisionSubmitFeatureTest.php tests/Feature/Note/NoteReplacementOverpaidAllocationReplayFeatureTest.php

Result:

    14 passed, 61 assertions

Final session verification:

- Owner reported make verify clean without gaps.
- Owner reported git/local updated latest.
- Exact full make verify output was not pasted in this chat.
- Accepted per locked session baseline policy.

Docs proof required after writing this handoff:

    python - <<'PY'
    from pathlib import Path
    tokens = [chr(96) * 3, chr(126) * 3]
    for path in Path('docs/99_archive/handoff/v2/edit_refund_sniper').glob('*.md'):
        text = path.read_text()
        for token in tokens:
            if token in text:
                print(path)
                break
    PY

Expected:

    no output

## Residual Risks

Blocks next step:

- None for continuing planning from this handoff.

Does not block next step:

- Downward surplus revision still rejects and rolls back.
- This is expected containment until Phase 1C.

Needs owner decision:

- What exact operation turns overpaid_pending into refund due, customer credit, or owner-retained balance.
- Whether customer-facing balance requires stronger customer identity than name/phone.
- How pending surplus should be displayed operationally without entering revenue reports.

Future improvement:

- Dedicated audit payload enrichment for settlement id.
- Admin-path focused assertion if not already covered by broader suite.
- Full Phase 1C design for downward surplus commit.
- Future customer_balance_entries once disposition decision is locked.
- Future report mode display for pending money state if owner wants it visible outside note lifecycle.

## Next Active Step

Goal:

Design Phase 1C, not implement blindly.

Phase 1C candidate:

Allow downward paid revision to commit by preserving current payable allocations safely and storing excess as overpaid_pending surplus.

Required source audit before Phase 1C:

    sed -n '1,220p' app/Application/Note/Services/NoteReplacementPaymentAllocationReconciler.php
    sed -n '1,220p' app/Application/Payment/Services/AllocatePaymentAcrossComponents.php
    sed -n '1,220p' app/Application/Note/Services/NoteHistoryProjectionService.php
    sed -n '1,240p' app/Adapters/Out/Note/DatabaseNoteHistoryProjectionSourceReaderAdapter.php
    sed -n '1,220p' app/Application/Note/Services/NoteOperationalStatusResolver.php
    sed -n '1,220p' app/Application/Note/Services/NoteOutstandingPaymentAmountResolver.php

Expected proof:

- Identify exactly why current downward surplus path rejects.
- Identify whether current projection can represent overpaid_pending without marking unpaid.
- Identify whether payment replay can safely allocate only revised payable amount while settlement snapshot preserves surplus.
- Identify tests for RED before patch.

Stop condition:

- Stop if implementing surplus commit requires reporting semantics, customer balance lifecycle, or UI money decision before owner decision.
- Stop if payment replay would hide surplus instead of preserving it in settlement.
- Stop if projection would treat surplus as revenue or unpaid debt.

## Next Session Opening Prompt

Use this prompt in the next session:

    Kita lanjut HyperPOS dari edit/refund sniper handoff.

    Baca berurutan:
    docs/01_standards/0001_index.md
    docs/01_standards/0002_decision_policy.md
    docs/99_archive/handoff/v2/edit_refund_sniper/README.md
    docs/99_archive/handoff/v2/edit_refund_sniper/SESSION_CONTRACT.md
    docs/99_archive/handoff/v2/edit_refund_sniper/0002_revision_settlement_foundation_handoff.md

    Baseline policy:
    - Saya selalu push setiap aksi.
    - Local dan repo dianggap identik kecuali ignored files jika saya sudah bilang clean/pushed.
    - Jangan minta git status/log/diff sebagai ritual.
    - make verify dijalankan di akhir sesi/final safe-state proof, bukan tiap micro-step.
    - Targeted/focused tests cukup untuk proof intermediate.
    - Minta git check hanya untuk dirty state, changed-file inventory kritikal, source/docs conflict, test failure aneh, atau final closure tanpa push proof.

    Latest completed slice:
    - Revision Settlement Foundation Phase 1A and 1B.
    - note_revision_settlements table exists.
    - NoteRevisionSettlement DTO exists.
    - BuildNoteRevisionSettlement exists.
    - BuildCreateNoteRevisionSettlement exists.
    - writer/reader ports and DB adapter exist.
    - successful revision commit now persists note_revision_settlements.
    - Phase 1B focused proof: 14 passed, 61 assertions.
    - Owner reported make verify clean and git updated latest.

    Locked domain decision:
    - Downward revision surplus is pending undecided money state.
    - It is not revenue.
    - It is not automatic refund.
    - It is not automatic customer credit.
    - It must remain flexible and DB-backed.
    - customer_balance_entries is deferred until surplus disposition is decided.

    Current active target:
    - Phase 1C source audit for allowing downward surplus revision commit.
    - Do not implement before source audit and decision lock.

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

    Start by auditing:
    app/Application/Note/Services/NoteReplacementPaymentAllocationReconciler.php
    app/Application/Payment/Services/AllocatePaymentAcrossComponents.php
    app/Application/Note/Services/NoteHistoryProjectionService.php
    app/Adapters/Out/Note/DatabaseNoteHistoryProjectionSourceReaderAdapter.php
    app/Application/Note/Services/NoteOperationalStatusResolver.php
    app/Application/Note/Services/NoteOutstandingPaymentAmountResolver.php

    Stop at blueprint/source audit before implementation.

## README Update Required

Yes.

New latest handoff filename:

    0002_revision_settlement_foundation_handoff.md

README latest handoff pointer must be updated from:

    docs/99_archive/handoff/v2/edit_refund_sniper/0001_verify_baseline_and_next_session_handoff.md

to:

    docs/99_archive/handoff/v2/edit_refund_sniper/0002_revision_settlement_foundation_handoff.md

## Session Context Health

82 percent.

Reason:

This session added new DB-backed settlement foundation, owner domain decisions, source audit conclusions, targeted/focused proof, and session proof policy updates.

Next large implementation should start from this handoff.
