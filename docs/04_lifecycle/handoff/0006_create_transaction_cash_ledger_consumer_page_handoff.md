# Handoff - Create Transaction Cash Ledger Consumer Page Split

## Metadata
- Date: 2026-05-25
- Slice / topic: Create transaction lifecycle cash ledger consumer exposure for cash vs transfer split
- Workflow step: Phase 1F-9 cash ledger consumer characterization and page exposure
- Status: continue in next session
- Progress: 60%

## Target Work Page
Continue lifecycle maturity proof for create transaction cash/transfer reporting after cash ledger reader, handler, summary, period table, and admin page exposure were proven GREEN.

Current next target is export-level cash ledger exposure, starting with PDF view data builder characterization before patching PDF/Excel output.

## References Used
- Previous handoff: docs/04_lifecycle/handoff/0004_refund_due_carry_forward_audit_fk_handoff.md
- Related handoff: docs/04_lifecycle/handoff/0005_create_transaction_lifecycle_cash_transfer_report_handoff.md
- Blueprint: docs/03_blueprints/db/0015_create_edit_transaction_contract_matrix.md
- Blueprint: docs/03_blueprints/db/0016_edit_refund_readiness_analysis.md
- Blueprint: docs/03_blueprints/db/0017_edit_refund_characterization_plan.md
- Blueprint: docs/03_blueprints/finance/0006_note_revision_refund_ledger.md
- ADR: docs/02_architecture/adr/0030_note_revision_payment_settlement_and_cashier_calculator_contract.md
- Blueprint: docs/03_blueprints/audit/0001_transactional_outbox_audit_runtime.md
- Repo snapshot / command output: local operator outputs from 2026-05-25 session

## Locked Facts
- Full make verify before this lifecycle sequence was GREEN:
  - Tests: 2 skipped, 1080 passed (5844 assertions)
  - Duration: 59.90s
- No new make verify was run after the cash ledger consumer/page sequence.
- Audit FK/outbox mismatch for refund_due and refund_paid was already closed before this handoff.
- Global AuditEventWriterPort remains bound to DatabaseAuditOutboxWriterAdapter.
- FK-bound refund_due and refund_paid handlers use canonical DatabaseAuditEventWriterAdapter through contextual binding in InfrastructureServiceProvider.
- Audit FK blocker must not be reopened unless there is new RED proof.
- Create transaction without payment is debt/save-note scenario.
- Transfer payment is real money-in and must be recorded and auditable separately from physical cash.
- Canonical customer payment money-in naming is transfer, not tf.
- Legacy tf input is normalized to transfer in the note payment path.
- Cash ledger reader now reads modern payment_component_allocations money-in.
- Cash ledger reader now exposes payment_method and splits cash vs transfer in reconciliation.
- Cash ledger handler consumer now preserves payment_method through DTO boundary.
- Cash ledger summary builder now exposes cash and transfer money-in split.
- Cash ledger period table builder now exposes cash and transfer money-in split per date.
- Admin cash ledger page now exposes summary cards for Kas Masuk, Tunai Masuk, and Transfer Masuk.

## Scope Used
### SCOPE-IN
- Cash ledger consumer characterization after query reader proof.
- Handler DTO boundary exposure of payment_method.
- Summary builder cash-vs-transfer split.
- Period table builder cash-vs-transfer split.
- Admin page summary exposure of cash-vs-transfer split.
- Existing tests updated where source table expectation was stale after reader source-table change.

### SCOPE-OUT
- Git status, git diff, git add, git commit, git push, branch, PR, merge.
- make verify as first action.
- UpdateTransactionWorkspaceHandler.
- Revision submit and payment submit merge.
- UI/payment forms.
- Expense/payment-out naming.
- PostgreSQL, Go API, dashboard performance, large seeder refactor.
- Migration/backfill for legacy tf rows.
- PDF export patch.
- Excel export patch.
- Dashboard wording.
- Operational profit transfer split proof.
- Store-stock/inventory create lifecycle proof.
- Rollback/idempotency characterization.

## GAP
- Cash ledger PDF export still likely shows only Kas Masuk from total_cash_in_rupiah and does not expose Tunai Masuk / Transfer Masuk.
- Cash ledger Excel export still likely shows only Kas Masuk and cash_in_rupiah columns without transfer split.
- TransactionCashLedgerPdfViewDataBuilder summaryItems still needs characterization.
- TransactionCashLedgerExcelSummarySheetWriter still needs characterization.
- TransactionCashLedgerExcelPeriodSheetWriter still needs characterization.
- TransactionCashLedgerExcelDetailSheetWriter may need payment_method column characterization if selected later.
- resources/views/admin/reporting/transaction_cash_ledger/index.blade.php page summary is GREEN, but detail table does not yet expose payment_method explicitly.
- Dashboard cash-in naming may still collapse total_in_rupiah as cash-in, but dashboard is separate scope.
- No full make verify proof after this sequence.

## Locked Decisions
- Progress is 60%.
- Progress meaning:
  - 20%: full/partial cash create lifecycle baseline proved.
  - 28%: no-payment debt/save-note baseline proved.
  - 35%: full transfer money-in baseline proved.
  - 40%: partial transfer money-in baseline proved.
  - 43%: canonical transfer naming for customer payment money-in proved.
  - 48%: cash ledger reader reads modern component allocation money-in and splits cash vs transfer.
  - 51%: handler DTO boundary exposes payment_method.
  - 54%: summary builder exposes cash-vs-transfer money-in split.
  - 57%: period table builder exposes cash-vs-transfer money-in split per date.
  - 60%: admin cash ledger page exposes cash-vs-transfer summary cards.
- Use transfer as canonical customer payment money-in naming.
- Keep legacy tf normalization where needed.
- Do not patch UI/payment forms or expense naming unless selected as a separate active step.
- Do not patch export before exact export source-map and characterization proof.
- Start export continuation with PDF view data builder characterization because it is easier to inspect than generated spreadsheets.
- Keep one active step per response.
- Do not claim make verify GREEN without running make verify.

## Files Created / Changed
### New files
- tests/Feature/Reporting/TransactionCashLedgerSummaryBuilderFeatureTest.php
- tests/Feature/Reporting/TransactionCashLedgerPeriodTableBuilderFeatureTest.php
- docs/04_lifecycle/handoff/0006_create_transaction_cash_ledger_consumer_page_handoff.md

### Changed files
- app/Application/Reporting/DTO/TransactionCashLedgerPerNoteRow.php
- app/Application/Reporting/Services/TransactionCashLedgerPerNoteBuilder.php
- app/Ports/Out/Reporting/TransactionReportingSourceReaderPort.php
- tests/Feature/Reporting/GetTransactionCashLedgerPerNoteFeatureTest.php
- app/Application/Reporting/Services/TransactionCashLedgerSummaryBuilder.php
- app/Application/Reporting/Services/TransactionCashLedgerPeriodTableBuilder.php
- tests/Feature/Reporting/TransactionCashLedgerPageFeatureTest.php
- resources/views/admin/reporting/transaction_cash_ledger/index.blade.php

## Verification Proof
- command:
  - php -l app/Application/Reporting/DTO/TransactionCashLedgerPerNoteRow.php
  - php -l app/Application/Reporting/Services/TransactionCashLedgerPerNoteBuilder.php
  - php -l app/Ports/Out/Reporting/TransactionReportingSourceReaderPort.php
  - php -l tests/Feature/Reporting/GetTransactionCashLedgerPerNoteFeatureTest.php
  - result:
    - No syntax errors detected in all four files.
  - meaning:
    - Handler DTO boundary patch was syntactically valid.

- command:
  - php artisan test tests/Feature/Reporting/GetTransactionCashLedgerPerNoteFeatureTest.php --filter=exposes_component_allocation_payment_method
  - result:
    - Tests: 1 passed (5 assertions)
    - Duration: 5.91s
  - meaning:
    - Handler consumer rows expose payment_method for component allocation cash and transfer rows.

- command:
  - php artisan test tests/Feature/Reporting/GetTransactionCashLedgerPerNoteFeatureTest.php tests/Feature/Reporting/TransactionCashLedgerReportingQueryFeatureTest.php
  - result:
    - Tests: 6 passed (51 assertions)
    - Duration: 6.19s
  - meaning:
    - Handler DTO boundary and query reader remained GREEN together.

- command:
  - php -l tests/Feature/Reporting/TransactionCashLedgerSummaryBuilderFeatureTest.php
  - result:
    - No syntax errors detected.
  - meaning:
    - Summary characterization test was syntactically valid.

- command:
  - php artisan test tests/Feature/Reporting/TransactionCashLedgerSummaryBuilderFeatureTest.php --filter=splits_cash_and_transfer_money_in
  - result:
    - Tests: 1 failed (1 assertions)
    - Failure: missing cash_in_rupiah and transfer_in_rupiah.
  - meaning:
    - RED proof confirmed summary builder still collapsed money-in.

- command:
  - php -l app/Application/Reporting/Services/TransactionCashLedgerSummaryBuilder.php
  - result:
    - No syntax errors detected.
  - meaning:
    - Summary builder patch was syntactically valid.

- command:
  - php artisan test tests/Feature/Reporting/TransactionCashLedgerSummaryBuilderFeatureTest.php --filter=splits_cash_and_transfer_money_in
  - result:
    - Tests: 1 passed (1 assertions)
    - Duration: 0.33s
  - meaning:
    - Summary builder now exposes total_cash_in_rupiah, cash_in_rupiah, transfer_in_rupiah, total_cash_out_rupiah, and net_amount_rupiah.

- command:
  - php artisan test tests/Feature/Reporting/TransactionCashLedgerSummaryBuilderFeatureTest.php tests/Feature/Reporting/GetTransactionCashLedgerPerNoteFeatureTest.php tests/Feature/Reporting/TransactionCashLedgerReportingQueryFeatureTest.php
  - result:
    - Tests: 7 passed (52 assertions)
    - Duration: 6.05s
  - meaning:
    - Summary, handler, and query were GREEN together.

- command:
  - php -l tests/Feature/Reporting/TransactionCashLedgerPeriodTableBuilderFeatureTest.php
  - result:
    - No syntax errors detected.
  - meaning:
    - Period table characterization test was syntactically valid.

- command:
  - php artisan test tests/Feature/Reporting/TransactionCashLedgerPeriodTableBuilderFeatureTest.php --filter=splits_cash_and_transfer_money_in_per_date
  - result:
    - Tests: 1 failed (1 assertions)
    - Failure: missing total_in_rupiah and transfer_in_rupiah; cash_in_rupiah still included transfer.
  - meaning:
    - RED proof confirmed period table builder still collapsed money-in.

- command:
  - php -l app/Application/Reporting/Services/TransactionCashLedgerPeriodTableBuilder.php
  - result:
    - No syntax errors detected.
  - meaning:
    - Period table builder patch was syntactically valid.

- command:
  - php artisan test tests/Feature/Reporting/TransactionCashLedgerPeriodTableBuilderFeatureTest.php --filter=splits_cash_and_transfer_money_in_per_date
  - result:
    - Tests: 1 passed (1 assertions)
    - Duration: 0.35s
  - meaning:
    - Period table builder now exposes total_in_rupiah, cash_in_rupiah, transfer_in_rupiah, cash_out_rupiah, and net_amount_rupiah per date.

- command:
  - php artisan test tests/Feature/Reporting/TransactionCashLedgerPeriodTableBuilderFeatureTest.php tests/Feature/Reporting/TransactionCashLedgerSummaryBuilderFeatureTest.php tests/Feature/Reporting/GetTransactionCashLedgerPerNoteFeatureTest.php tests/Feature/Reporting/TransactionCashLedgerReportingQueryFeatureTest.php
  - result:
    - Tests: 8 passed (53 assertions)
    - Duration: 6.10s
  - meaning:
    - Period, summary, handler, and query were GREEN together.

- command:
  - php -l tests/Feature/Reporting/TransactionCashLedgerPageFeatureTest.php
  - result:
    - No syntax errors detected.
  - meaning:
    - Page characterization test patch was syntactically valid.

- command:
  - php artisan test tests/Feature/Reporting/TransactionCashLedgerPageFeatureTest.php --filter=cash_and_transfer_money_in_split
  - result:
    - Tests: 1 failed (4 assertions)
    - Failure: expected response to contain Tunai Masuk.
  - meaning:
    - RED proof confirmed admin page did not expose cash-vs-transfer summary split yet.

- command:
  - php artisan test tests/Feature/Reporting/TransactionCashLedgerPageFeatureTest.php tests/Feature/Reporting/TransactionCashLedgerPeriodTableBuilderFeatureTest.php tests/Feature/Reporting/TransactionCashLedgerSummaryBuilderFeatureTest.php tests/Feature/Reporting/GetTransactionCashLedgerPerNoteFeatureTest.php tests/Feature/Reporting/TransactionCashLedgerReportingQueryFeatureTest.php
  - result:
    - Tests: 2 failed, 17 passed (122 assertions)
    - Failures:
      - stale page assertion expected customer_payments
      - page did not contain Tunai Masuk
  - meaning:
    - RED page exposure was isolated; existing builders/query stayed GREEN.

- command:
  - php -l tests/Feature/Reporting/TransactionCashLedgerPageFeatureTest.php
  - result:
    - No syntax errors detected.
  - meaning:
    - Page test update remained syntactically valid after Blade/test patch.

- command:
  - php artisan test tests/Feature/Reporting/TransactionCashLedgerPageFeatureTest.php --filter=cash_and_transfer_money_in_split
  - result:
    - Tests: 1 passed (7 assertions)
    - Duration: 5.98s
  - meaning:
    - Admin cash ledger page now exposes Kas Masuk, Tunai Masuk, and Transfer Masuk.

- command:
  - php artisan test tests/Feature/Reporting/TransactionCashLedgerPageFeatureTest.php tests/Feature/Reporting/TransactionCashLedgerPeriodTableBuilderFeatureTest.php tests/Feature/Reporting/TransactionCashLedgerSummaryBuilderFeatureTest.php tests/Feature/Reporting/GetTransactionCashLedgerPerNoteFeatureTest.php tests/Feature/Reporting/TransactionCashLedgerReportingQueryFeatureTest.php
  - result:
    - Tests: 19 passed (131 assertions)
    - Duration: 6.71s
  - meaning:
    - Cash ledger reader, handler, summary, period, and admin page exposure are GREEN together.

## Source Map Notes
- app/Application/Reporting/Exports/TransactionCashLedgerPdfViewDataBuilder.php previously showed summary label Kas Masuk using total_cash_in_rupiah only.
- app/Application/Reporting/Exports/TransactionCashLedgerExcelSummarySheetWriter.php previously showed summary label Kas Masuk using total_cash_in_rupiah only.
- app/Application/Reporting/Exports/TransactionCashLedgerExcelPeriodSheetWriter.php previously wrote cash_in_rupiah only.
- resources/views/admin/reporting/transaction_cash_ledger/index.blade.php now exposes summary split on page, but exports still need proof.
- Dashboard cash totals in AdminDashboardOverviewPayload and related context may still use total_in_rupiah as cash-in wording. Treat dashboard separately.

## Risks / Follow-up Notes
- Do not patch export output blindly. First add characterization test for the smallest export data builder.
- PDF view data builder is recommended as next active step because it returns arrays and is easier to characterize than XLSX binary output.
- Excel summary and period writers likely need later tests after PDF view data builder.
- Existing naming total_cash_in_rupiah is backward-compatible but semantically ambiguous because it now means total money-in, not only physical cash. Do not rename broadly without a separate decision.
- Full make verify has not been run after this sequence.

## Next Step
Phase 1F-9I - Cash ledger PDF export characterization for cash-vs-transfer exposure.

Single active step:
- Add or patch a focused test for TransactionCashLedgerPdfViewDataBuilder.
- Goal: prove whether PDF view data summaryItems expose Tunai Masuk and Transfer Masuk after summary builder now provides cash_in_rupiah and transfer_in_rupiah.
- Do not patch Excel in the same response.
- Do not patch dashboard in the same response.
- Do not ask for make verify as first action.

Recommended files to inspect first:
- app/Application/Reporting/Exports/TransactionCashLedgerPdfViewDataBuilder.php
- tests/Feature/Reporting related to PDF/export if present
- app/Application/Reporting/Exports/TransactionCashLedgerExcelSummarySheetWriter.php
- app/Application/Reporting/Exports/TransactionCashLedgerExcelPeriodSheetWriter.php

Recommended expected proof:
- Syntax test file PASS.
- Targeted PDF view data builder characterization RED if summaryItems only contains Kas Masuk and not Tunai Masuk / Transfer Masuk.
- Then patch only TransactionCashLedgerPdfViewDataBuilder.php in the next active step after RED proof.
