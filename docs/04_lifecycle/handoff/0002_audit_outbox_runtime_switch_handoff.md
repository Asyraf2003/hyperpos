# Audit Outbox Runtime Switch Handoff

## Metadata
- Date: 2026-05-23
- Slice / topic: Audit outbox runtime switch
- Workflow step: handoff after global binding switch test expectation fix proof
- Status: Phase 4 selected regression proven; monitoring remains separate future step
- Progress: audit outbox foundation, processor, pilot binding, global binding, and old-expectation test patch completed/proven

## Target Work Page

Continue audit runtime work under:

- `docs/03_blueprints/audit/0001_transactional_outbox_audit_runtime.md`
- `docs/03_blueprints/audit/0002_audit_write_path_matrix.md`
- `tests/Feature/AuditLog/DatabaseAuditEventWriterAdapterTest.php`
- `tests/Feature/Expense/UpdateExpenseCategoryFeatureTest.php`
- `tests/Feature/Expense/ActivateExpenseCategoryFeatureTest.php`
- `tests/Feature/Expense/DeactivateExpenseCategoryFeatureTest.php`
- `docs/03_blueprints/audit/0002_audit_write_path_matrix.md`

Current next target:

- choose one new explicit step after Phase 4 proof; monitoring remains unimplemented and must not start without a new active step

## References Used

- Standards:
  - `docs/01_standards/0001_index.md`
  - `docs/01_standards/0005_handoff_template.md`
  - `docs/01_standards/core/0011_blueprint_first.md`
  - `docs/01_standards/core/0013_proof_and_progress.md`
  - `docs/01_standards/workflow/0021_active_step_policy.md`
- Handoff README:
  - `docs/04_lifecycle/handoff/README.md`
- Active audit blueprints:
  - `docs/03_blueprints/audit/0001_transactional_outbox_audit_runtime.md`
  - `docs/03_blueprints/audit/0002_audit_write_path_matrix.md`
- Latest operator output:
  - old-expectation tests after global binding switch were patched to staged outbox semantics
  - `php -l` passed for all four touched tests
  - selected audit/expense regression passed with 21 tests and 138 assertions

## Locked Facts

- `UpdateExpenseCategoryHandler` uses canonical `AuditEventWriterPort`.
- `ActivateExpenseCategoryHandler` uses canonical `AuditEventWriterPort`.
- `DeactivateExpenseCategoryHandler` uses canonical `AuditEventWriterPort`.
- `audit_outbox` migration exists and passed syntax proof.
- `DatabaseAuditOutboxWriterAdapter` exists and passed syntax/test proof.
- `audit:outbox:process` command exists and passed syntax/test proof.
- Processor materialization, duplicate-run, and failure handling tests passed.
- Test-only expense category outbox binding pilot passed.
- Broader test-only expense category outbox binding regression passed.
- Global binding switch was attempted in `app/Providers/InfrastructureServiceProvider.php`.
- After global binding switch, actual `AuditEventWriterPort` resolves to `DatabaseAuditOutboxWriterAdapter`.
- After global binding switch, old-expectation tests were patched to staged outbox semantics.
- After the patch, selected audit/expense regression passed with 21 tests and 138 assertions.

## Scope Used

### SCOPE-IN
- Audit outbox migration.
- Outbox writer adapter.
- Audit outbox processor command.
- Test-only outbox binding pilot.
- Global binding switch attempt.
- Focused audit/expense regression.
- Old-expectation test patch for selected audit/expense regression.

### SCOPE-OUT
- Payment/refund/allocation/stock/transaction-heavy flows.
- Monitoring UI.
- Scheduler/queue production operation.
- Payment/procurement/employee finance audit migration.
- `make verify` legacy seeder PHPStan issue.
- Remote write by assistant.

## GAP

- Minimal pending/failed monitoring is not implemented.
- `make verify` still has unrelated PHPStan seeder class errors from product seeder tests.
- Full-suite verification beyond the selected audit/expense regression has not been provided in this handoff.

## Locked Decisions

- Do not rollback global binding unless new proof shows the outbox design is invalid.
- Do not switch to payment/refund/stock/transaction-heavy flows yet.
- Do not add monitoring without opening a new explicit step after Phase 4 proof.
- Direct materializer tests should instantiate or resolve `DatabaseAuditEventWriterAdapter` concrete directly.
- Runtime feature tests should reflect outbox semantics after global binding:
  - audit is staged in `audit_outbox`;
  - `audit_events` remains empty before processor;
  - `audit:outbox:process` materializes canonical audit;
  - outbox row becomes `processed`.

## Files Created / Changed

### New files
- `database/migrations/2026_05_23_010000_create_audit_outbox_table.php`
- `app/Adapters/Out/Audit/DatabaseAuditOutboxWriterAdapter.php`
- `app/Application/Audit/Support/AuditOutboxStatus.php`
- `app/Application/Audit/Services/AuditOutboxEventHydrator.php`
- `app/Application/Audit/Services/AuditOutboxFailureRecorder.php`
- `app/Application/Audit/UseCases/ProcessAuditOutboxHandler.php`
- `routes/console_audit_outbox.php`
- `tests/Feature/AuditLog/Support/AuditOutboxTestEventFactory.php`
- `tests/Feature/AuditLog/DatabaseAuditOutboxWriterAdapterTest.php`
- `tests/Feature/AuditLog/ProcessAuditOutboxMaterializationCommandTest.php`
- `tests/Feature/AuditLog/ProcessAuditOutboxDuplicateRunCommandTest.php`
- `tests/Feature/AuditLog/ProcessAuditOutboxFailureCommandTest.php`
- `tests/Feature/AuditLog/AuditOutboxExpenseCategoryPilotTest.php`
- `tests/Feature/AuditLog/AuditOutboxExpenseCategoryRuntimeRegressionTest.php`
- `tests/Feature/AuditLog/AuditOutboxRuntimeBindingTest.php`
- `docs/04_lifecycle/handoff/0002_audit_outbox_runtime_switch_handoff.md`

### Changed files
- `app/Providers/InfrastructureServiceProvider.php`
- `routes/console.php`
- `docs/04_lifecycle/handoff/README.md`
- `docs/03_blueprints/audit/0001_transactional_outbox_audit_runtime.md`

## Verification Proof

- command:
  - `php -l database/migrations/2026_05_23_010000_create_audit_outbox_table.php`
  - result: no syntax errors detected
  - meaning: audit outbox migration syntax is valid

- command:
  - `php artisan test tests/Feature/AuditLog/DatabaseAuditOutboxWriterAdapterTest.php tests/Feature/AuditLog/DatabaseAuditEventWriterAdapterTest.php`
  - result: 6 passed, 31 assertions
  - meaning: outbox writer stages events and direct writer regression still passed before global binding switch

- command:
  - `php artisan test tests/Feature/AuditLog/ProcessAuditOutboxMaterializationCommandTest.php tests/Feature/AuditLog/ProcessAuditOutboxDuplicateRunCommandTest.php tests/Feature/AuditLog/ProcessAuditOutboxFailureCommandTest.php tests/Feature/AuditLog/DatabaseAuditOutboxWriterAdapterTest.php tests/Feature/AuditLog/DatabaseAuditEventWriterAdapterTest.php`
  - result: 9 passed, 46 assertions
  - meaning: processor materialization, duplicate-run, failure handling, outbox writer, and direct writer proof passed after file-size refactor

- command:
  - selected outbox pilot regression with `AuditOutboxExpenseCategoryPilotTest`
  - result: 12 passed, 79 assertions
  - meaning: update expense category could stage audit in outbox and materialize through processor in test-only binding

- command:
  - selected broader outbox regression with `AuditOutboxExpenseCategoryRuntimeRegressionTest`
  - result: 15 passed, 112 assertions
  - meaning: update/activate/deactivate expense category flows could stage and materialize through outbox binding in test-only scope

- command:
  - selected regression after global binding switch
  - result: 17 passed, 4 failed, 105 assertions
  - meaning: global binding switch worked for new outbox tests, but old tests still expected direct canonical audit writes

- command:
  - `php -l tests/Feature/AuditLog/DatabaseAuditEventWriterAdapterTest.php`
  - `php -l tests/Feature/Expense/UpdateExpenseCategoryFeatureTest.php`
  - `php -l tests/Feature/Expense/ActivateExpenseCategoryFeatureTest.php`
  - `php -l tests/Feature/Expense/DeactivateExpenseCategoryFeatureTest.php`
  - result: no syntax errors detected for all four touched tests
  - meaning: old-expectation test patch has valid PHP syntax

- command:
  - `wc -l tests/Feature/AuditLog/DatabaseAuditEventWriterAdapterTest.php tests/Feature/Expense/UpdateExpenseCategoryFeatureTest.php tests/Feature/Expense/ActivateExpenseCategoryFeatureTest.php tests/Feature/Expense/DeactivateExpenseCategoryFeatureTest.php`
  - result: 98, 96, 80, and 80 lines
  - meaning: all touched tests satisfy the <= 100 lines rule

- command:
  - selected audit/expense regression after old-expectation test patch
  - result: 21 passed, 138 assertions
  - meaning: Phase 4 global outbox binding is proven for the selected audit/expense regression scope

## Risks / Follow-up Notes

- Phase 4 selected regression is proven, but this does not prove full application regression.
- Minimal pending/failed audit outbox monitoring remains unimplemented.
- `make verify` failure from product seeder PHPStan is unrelated to audit outbox runtime and should be handled in a separate step.
- Payment/refund/allocation/stock/transaction-heavy flows remain excluded.
- Further runtime audit expansion must start from a new explicit active step with source and test proof.

## Next Step

Choose one new active step only:

- close/update docs and handoff after current proof;
- implement minimal pending/failed audit outbox monitoring;
- remediate unrelated `make verify` product seeder PHPStan issue.

Do not start payment/refund/allocation/stock/transaction-heavy audit migration from this handoff.
