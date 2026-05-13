# Handoff 0014 - Refund Paid Contract And Migration

## Metadata

- Date: 2026-05-13
- Sequence: 0014
- Scope: refund_paid execution contract from refund_due, ADR 0029, migration foundation, and verification cleanup
- Previous handoff: docs/99_archive/handoff/v2/edit_refund_sniper/0013_refund_due_report_audit_and_refund_paid_gap_handoff.md
- Latest proven commit or push proof: not provided in this session
- Owner workflow: owner handles commit and push manually

## Status

Implementation partial.

Docs contract and migration foundation are completed locally with proof.

refund_paid execution use case is not implemented yet.

UI, report integration for refund_paid, export integration for refund_paid, and cash ledger integration for refund_paid are not implemented yet.

## Session Goal

Continue HyperPOS from the refund_due/admin-operable chain into a locked refund_paid design decision.

The session goal was to analyze source before implementation, decide the refund_paid execution contract, create ADR 0029, create the first migration foundation, and stop before refund_paid application code.

## Facts

Owner provided initial session prompt and required source read order.

The session read and used:

- docs/01_standards/0001_index.md
- docs/01_standards/0002_decision_policy.md
- docs/99_archive/handoff/v2/edit_refund_sniper/README.md
- docs/99_archive/handoff/v2/edit_refund_sniper/SESSION_CONTRACT.md
- docs/99_archive/handoff/v2/edit_refund_sniper/PROMPT_TEMPLATE.md
- docs/99_archive/handoff/v2/edit_refund_sniper/HANDOFF_TEMPLATE.md
- docs/99_archive/handoff/v2/edit_refund_sniper/0013_refund_due_report_audit_and_refund_paid_gap_handoff.md
- docs/02_architecture/adr/0026_note_revision_surplus_disposition.md
- docs/02_architecture/adr/0027_note_revision_surplus_disposition_transaction_contract.md
- docs/02_architecture/adr/0028_mysql_to_postgresql_and_api_migration_readiness.md

Source audit confirmed:

- customer_refunds is payment-note/component refund infrastructure.
- customer_refunds requires customer_payment_id.
- CustomerRefund domain requires customerPaymentId.
- RecordCustomerRefundOperation validates against customer_payment_id and note_id allocated/refunded pair.
- RecordCustomerRefundOperation allocates refund across payment components.
- RecordCustomerRefundTransaction can trigger AutoRefundNoteWhenFullyRefunded.
- RecordCustomerRefundTransaction can trigger AutoReverseRefundedStoreStockInventory.
- Existing refund transaction writes legacy AuditLogPort event customer_refund_recorded.
- refund_due implementation writes canonical audit_events and audit_event_snapshots.
- note_revision_surplus_dispositions is the existing refund_due/source disposition table.
- audit_events and audit_event_snapshots are the canonical audit direction for new finance-sensitive flows.

Owner stated local and repo are the same.

Owner approved design option 2:

    Create a new table note_revision_surplus_refund_payments.

Owner also required:

- migration must support read, create, and future edit/reversal paths under one second
- migration must strongly support future PostgreSQL transition

ADR 0029 was created locally:

    docs/02_architecture/adr/0029_note_revision_surplus_refund_paid_execution.md

ADR proof provided by owner:

    ADR_OK

ADR anchors found:

- note_revision_surplus_refund_payments
- Performance Contract
- PostgreSQL Readiness Contract
- Do not require customer_payment_id

Migration was created locally:

    database/migrations/2026_05_13_000300_create_note_revision_surplus_refund_payments_table.php

Migration proof provided by owner:

    No syntax errors detected in database/migrations/2026_05_13_000300_create_note_revision_surplus_refund_payments_table.php

Migration anchors found:

- note_revision_surplus_refund_payments
- effective_date
- idempotency_key
- nr_surplus_refund_payments_disposition_idem_unique
- restrictOnDelete

Migration execution proof provided by owner:

    php artisan migrate

    2026_05_13_000100_create_note_revision_settlements_table DONE
    2026_05_13_000200_create_note_revision_surplus_dispositions_table DONE
    2026_05_13_000300_create_note_revision_surplus_refund_payments_table DONE

make verify initially failed on PHPStan:

    app/Application/Reporting/Services/TransactionSummaryPerNoteBuilder.php
    nullCoalesce.offset
    refund_due_rupiah always exists and is not nullable

Fix applied:

    app/Application/Reporting/Services/TransactionSummaryPerNoteBuilder.php

Behavior:

- replaced redundant $row['refund_due_rupiah'] ?? 0 with $row['refund_due_rupiah']

make verify then failed on stale tests and export fixture expectations related to refund_due visibility.

Test expectation fixes were applied to:

- tests/Feature/Reporting/GetTransactionSummaryPerNoteFeatureTest.php
- tests/Feature/Reporting/TransactionSummaryPerNoteHardeningFeatureTest.php
- tests/Feature/ReportingExports/TransactionReportExcelExportFeatureTest.php
- tests/Feature/ReportingExports/TransactionReportPdfExportFeatureTest.php

Latest pasted proof:

    Tests: 1000 passed (5348 assertions)
    Duration: 53.28s

This proves the test phase is green.

If this output was from make verify and the command returned to shell without make error, then make verify is green in owner local environment.

## Gaps

No commit or push proof was provided in this session.

No full git status, log, or diff was requested because owner handles commit and push manually and the session contract rejects routine git ceremony.

refund_paid application code is not implemented.

The following are still not implemented:

- refund_paid DTO/domain result objects
- refund_paid ports
- refund_paid database adapter
- refund_paid use case
- source refund_due row lock implementation
- idempotency behavior implementation
- canonical audit event factory for refund_paid
- targeted tests for refund_paid execution
- report/cash ledger/export integration for refund_paid
- UI action for refund_paid
- audit timeline read model for refund_paid event
- reversal/cancel behavior

No browser/manual QA proof exists for refund_paid.

No PostgreSQL implementation exists and it remains out of scope.

No Go API implementation exists and it remains out of scope.

## Assumptions

No implementation assumption accepted.

Owner statement that local and repo are the same was accepted as FACT.

## Decisions

Decision 1:

refund_paid from refund_due uses a new table:

    note_revision_surplus_refund_payments

Source:

- owner approval
- ADR 0029

Decision 2:

refund_paid from refund_due must not use customer_refunds for the first slice.

Source:

- source audit
- owner approval
- ADR 0029

Decision 3:

refund_paid from refund_due must not require customer_payment_id.

Source:

- source audit found no valid single customer_payment_id in note_revision_settlements
- ADR 0029

Decision 4:

refund_paid from refund_due must not create refund_component_allocations.

Source:

- source audit
- ADR 0029

Decision 5:

refund_paid from refund_due must not trigger note refunded lifecycle.

Source:

- source audit
- ADR 0029

Decision 6:

refund_paid from refund_due must not trigger inventory reversal.

Source:

- source audit
- ADR 0029

Decision 7:

refund_paid from refund_due must write canonical audit_events and audit_event_snapshots.

Source:

- ADR 0027
- ADR 0028
- ADR 0029

Decision 8:

migration must be PostgreSQL-ready and indexed for sub-one-second read/create/future edit-reversal paths.

Source:

- owner requirement
- ADR 0028
- ADR 0029

## Active Slice

Selected active slice:

    refund_paid backend foundation from refund_due

Current completed portion:

- ADR contract
- migration foundation
- verification cleanup for stale refund_due report/export tests

Scope in for next backend slice:

- DTO/result object
- ports
- database adapter
- use case
- transaction boundary
- source disposition lock
- idempotency
- canonical audit event
- targeted tests

Scope out:

- customer_credit
- customer_balance_entries
- PostgreSQL implementation
- Go API implementation
- UI action
- report/export integration
- cash ledger integration
- reversal/cancel implementation
- mutation of customer_refunds
- mutation of existing customer refund flow
- component refund allocation
- note refunded lifecycle
- inventory reversal

Files to touch next:

- likely new DTO under app/Application/Note/DTO or specific refund_paid namespace
- likely new use case under app/Application/Note/UseCases
- likely new ports under app/Ports/Out/Note
- likely new adapter under app/Adapters/Out/Note
- likely provider binding update in app/Providers/HexagonalServiceProvider.php
- targeted tests under tests/Feature/Note or tests/Unit/Application/Note

Files not to touch next:

- app/Application/Payment/Services/RecordCustomerRefundOperation.php
- app/Application/Payment/Services/RecordCustomerRefundTransaction.php
- app/Core/Payment/CustomerRefund/CustomerRefund.php
- database/migrations/2026_03_15_000100_create_customer_refunds_table.php
- refund_component_allocations flow
- inventory reversal services
- note refunded lifecycle services
- customer_credit
- customer_balance_entries
- PostgreSQL code
- Go API code
- UI/report/export until backend use case is green

DB impact:

- new table note_revision_surplus_refund_payments exists locally through migration
- no customer_refunds schema change
- no existing refund table mutation

UI impact:

- none yet

Report impact:

- none yet for refund_paid
- stale refund_due report/export tests were aligned to existing refund_due contract

API impact:

- none yet

Audit impact:

- ADR 0029 locks future audit event note_revision_surplus_refund_paid_recorded
- implementation is still pending

## Source Audit Summary

database/migrations/2026_03_15_000100_create_customer_refunds_table.php

- current behavior: customer_refunds has id, customer_payment_id, note_id, amount_rupiah, refunded_at, reason
- risk: not source-linked to surplus disposition and requires payment id
- scope: not to modify for refund_paid first slice

app/Core/Payment/CustomerRefund/CustomerRefund.php

- current behavior: CustomerRefund requires customerPaymentId, noteId, positive amount, refundedAt, reason
- risk: cannot represent surplus refund_paid without fake customer_payment_id
- scope: not to modify for refund_paid first slice

app/Application/Payment/Services/RecordCustomerRefundOperation.php

- current behavior: validates refund against allocated/refunded customer_payment_id + note_id pair and allocates refund across components
- risk: wrong for surplus refund_paid
- scope: not to reuse or modify for refund_paid first slice

app/Application/Payment/Services/RecordCustomerRefundTransaction.php

- current behavior: executes refund operation, inventory reversal, note refunded lifecycle, legacy audit log, projection sync
- risk: dangerous side effects for surplus refund_paid
- scope: not to reuse or modify for refund_paid first slice

app/Application/Payment/Services/AllocateRefundAcrossComponents.php

- current behavior: requires refundable payment component allocations
- risk: surplus refund_paid is not component refund
- scope: not to use for refund_paid first slice

app/Application/Note/UseCases/CreateNoteRevisionSurplusRefundDueHandler.php

- current behavior: transaction-bound refund_due handler with guard, disposition factory, canonical audit event writer, and writer
- risk: pattern should inform refund_paid handler but not be blindly copied
- scope: reference pattern

app/Application/Note/UseCases/CreateNoteRevisionSurplusRefundDueAuditEventFactory.php

- current behavior: writes event note_revision_surplus_refund_due_created with before/after pending snapshots
- risk: refund_paid needs separate event name and snapshots
- scope: reference pattern

app/Adapters/Out/Note/DatabaseNoteRevisionSurplusDispositionAdapter.php

- current behavior: writes refund_due dispositions and reads pending by settlement/root
- risk: no for-update lock and no refund_paid payment read/write yet
- scope: next slice likely needs additional reader/adapter for source disposition lock and payment totals

database/migrations/2026_05_13_000200_create_note_revision_surplus_dispositions_table.php

- current behavior: source refund_due table with source ids, amount, before/after pending, status, occurred_at, audit_event_id, indexes, restrict FKs
- risk: source row must be locked for refund_paid concurrency
- scope: no schema change planned now

database/migrations/2026_05_13_000300_create_note_revision_surplus_refund_payments_table.php

- current behavior: new refund_paid execution ledger migration
- proof: syntax pass and migrated
- scope: completed foundation

database/migrations/2026_04_06_230100_create_audit_events_and_snapshots_tables.php

- current behavior: canonical audit_events and audit_event_snapshots tables
- risk: no idempotency uniqueness here; idempotency belongs in refund payment table
- scope: no schema change planned

## Files Changed

Files changed in this session based on commands/proof:

- docs/02_architecture/adr/0029_note_revision_surplus_refund_paid_execution.md
- database/migrations/2026_05_13_000300_create_note_revision_surplus_refund_payments_table.php
- app/Application/Reporting/Services/TransactionSummaryPerNoteBuilder.php
- tests/Feature/Reporting/GetTransactionSummaryPerNoteFeatureTest.php
- tests/Feature/Reporting/TransactionSummaryPerNoteHardeningFeatureTest.php
- tests/Feature/ReportingExports/TransactionReportExcelExportFeatureTest.php
- tests/Feature/ReportingExports/TransactionReportPdfExportFeatureTest.php

This handoff command also changes:

- docs/99_archive/handoff/v2/edit_refund_sniper/0014_refund_paid_contract_and_migration_handoff.md
- docs/99_archive/handoff/v2/edit_refund_sniper/README.md

## Tests And Proof

ADR proof:

    ADR_OK

ADR grep anchors:

- note_revision_surplus_refund_payments
- Performance Contract
- PostgreSQL Readiness Contract
- Do not require customer_payment_id

Migration syntax proof:

    No syntax errors detected in database/migrations/2026_05_13_000300_create_note_revision_surplus_refund_payments_table.php

Migration grep anchors:

- note_revision_surplus_refund_payments
- effective_date
- idempotency_key
- nr_surplus_refund_payments_disposition_idem_unique
- restrictOnDelete

Migration execution proof:

    php artisan migrate

    2026_05_13_000100_create_note_revision_settlements_table DONE
    2026_05_13_000200_create_note_revision_surplus_dispositions_table DONE
    2026_05_13_000300_create_note_revision_surplus_refund_payments_table DONE

PHPStan blocker found and fixed:

    app/Application/Reporting/Services/TransactionSummaryPerNoteBuilder.php
    nullCoalesce.offset
    refund_due_rupiah always exists and is not nullable

Test proof after cleanup:

    Tests: 1000 passed (5348 assertions)
    Duration: 53.28s

If this was the final phase of make verify and the command returned to shell without make error, local make verify is green.

Exact full make verify output was not fully pasted in this handoff.

## Residual Risks

Blocks next step:

- none for starting backend DTO/ports/adapter/use case planning

Does not block next step:

- no commit/push proof in chat
- no browser/manual QA
- no UI/report/export integration for refund_paid
- no cash ledger integration for refund_paid

Needs owner decision later:

- exact UI wording for refund_paid action
- exact report/export labels for surplus_refund_paid
- reversal/cancel policy if needed beyond schema readiness
- whether refund_due display should show original due, remaining due, or both after partial refund_paid

Future improvement:

- add performance assertions or query-count checks for refund_paid timeline/report once read models exist
- add future API command contract after application use case stabilizes

## Next Active Step

Goal:

Implement backend foundation for refund_paid execution from refund_due without UI/report/export yet.

Recommended next first implementation sub-step:

Create DTO/result objects and ports for note_revision_surplus_refund_payments plus adapter skeleton.

Stop condition:

Stop before controller/UI/report/export.

Expected proof:

- php -l for new files
- targeted unit/feature test for adapter create/read/idempotency lookup if built
- no customer_refunds mutation
- no refund_component_allocations mutation
- no note refunded lifecycle mutation
- no inventory reversal mutation

## Next Session Opening Prompt

Kita lanjut HyperPOS refund_paid backend foundation dari handoff 0014.

Baca berurutan:

1. docs/01_standards/0001_index.md
2. docs/01_standards/0002_decision_policy.md
3. docs/99_archive/handoff/v2/edit_refund_sniper/README.md
4. docs/99_archive/handoff/v2/edit_refund_sniper/SESSION_CONTRACT.md
5. docs/99_archive/handoff/v2/edit_refund_sniper/0014_refund_paid_contract_and_migration_handoff.md
6. docs/02_architecture/adr/0029_note_revision_surplus_refund_paid_execution.md
7. database/migrations/2026_05_13_000300_create_note_revision_surplus_refund_payments_table.php

Current locked decision:

refund_paid from refund_due uses note_revision_surplus_refund_payments.

Do not use customer_refunds.
Do not require customer_payment_id.
Do not create refund_component_allocations.
Do not trigger note refunded lifecycle.
Do not trigger inventory reversal.
Do not implement customer_credit.
Do not implement customer_balance_entries.
Do not implement PostgreSQL.
Do not implement Go API.
Do not start from UI/report/export.

Owner handles commit and push manually.

Active step:

Design and implement backend DTO/ports/adapter/use case foundation for refund_paid execution from refund_due, one step at a time, with targeted proof.

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

## README Update Required

Yes.

New latest handoff filename:

    0014_refund_paid_contract_and_migration_handoff.md

## Session Context Health

82 percent.

Handoff required before continuing large implementation.

Reason:

- new ADR decision
- new migration
- verification cleanup
- several touched tests
- refund_paid backend remains pending and finance-sensitive
