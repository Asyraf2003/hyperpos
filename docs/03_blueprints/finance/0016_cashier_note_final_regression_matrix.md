# Blueprint 0016 - Cashier Note Final Regression Matrix

Status:
VERIFYING / Final Regression Matrix Created / Awaiting Focused Regression And make verify Proof

Purpose:
Final regression matrix untuk mengunci seluruh Phase 0-6 cashier note consistency workflow sebelum Phase 7 ditutup.

Scope:
- Verification and documentation only.
- No feature change.
- No migration.
- No route/config change.
- No supplier invoice payment proof.
- No Mobile API.
- No Operational Profit formula change.
- No refund policy change.
- No ServicePackageProfitBreakdown behavior change unless a regression proves a bug.

Source map:
- `docs/03_blueprints/finance/0011_cashier_note_consistency_workflow_index.md`
- `docs/03_blueprints/finance/0012_cashier_note_create_line_source_map.md`
- `docs/03_blueprints/finance/0013_cashier_note_edit_revision_payment_consistency.md`
- `docs/03_blueprints/finance/0014_cashier_note_refund_reporting_consistency.md`
- `docs/03_blueprints/finance/0015_service_package_profit_breakdown_source_contract.md`
- `docs/04_lifecycle/error_log/0038_cashier_note_create_edit_refund_reporting_audit_findings.md`

## Regression Matrix

| Phase | Behavior Locked | Main File/Test | Command Test | Status | Boundary Not To Touch |
| --- | --- | --- | --- | --- | --- |
| Phase 0 Docs lock | Workflow docs exist and become the source index for cashier note create/edit/refund/report consistency. | `0011`, `0012`, `0013`, `0014`, `0015`, `0038` | Not required. Docs-only phase. | FIXED | No source patch. |
| Phase 0A Owner Decision V2 Docs lock | Owner decisions are locked: flexible package, template as preset, package-aware correction, full revision fingerprint, external purchase separate domain, combination report basis, component-type refund policy. | `0011`, `0012`, `0013`, `0014`, `0015`, `0038` | Not required. Docs-only phase. | FIXED | Keep decision wording aligned. No source patch. |
| Phase 1 Characterization | Current create/edit/refund/report behavior characterized before hardening. | `CreateTransactionWorkspaceLineTypeCharacterizationTest`, `EditTransactionWorkspaceRevisionPaymentCharacterizationTest`, `RefundReportingOwnerDecisionV2CharacterizationTest` | `php artisan test --filter=CreateTransactionWorkspaceLineTypeCharacterizationTest`<br>`php artisan test --filter=EditTransactionWorkspaceRevisionPaymentCharacterizationTest`<br>`php artisan test --filter=RefundReportingOwnerDecisionV2CharacterizationTest` | FIXED | Characterization only. Do not use Phase 1 to introduce feature behavior. |
| Phase 2 Hardening Guards | Package-aware correction floor guard, template guard, and external purchase simplification gates remain protected. | Create workspace contract tests, template tests, correction guard tests | `php artisan test --filter=CreateTransactionWorkspaceServiceStoreStockFeatureTest`<br>`php artisan test --filter=CreateTransactionWorkspaceServiceExternalPurchaseFeatureTest`<br>`php artisan test --filter=CreateTransactionWorkspaceTemplateContractFeatureTest`<br>`php artisan test --filter=CashierWorkspaceServiceProductTemplateMinimumContractFeatureTest`<br>`php artisan test --filter=CashierWorkspaceServiceProductTemplateAutofillContractFeatureTest`<br>`php artisan test --filter=CorrectPaidServiceWithStoreStockPartServiceFeeOnly` | FIXED | No new template semantics. No external purchase package auto-split opening. |
| Phase 3 Revision Payload Historical Fingerprint | Revision payload keeps historical package financial fingerprint instead of leaking mutable master data. | `NoteRevisionLinePayloadMapperTest`, edit/revision characterization | `php artisan test --filter=NoteRevisionLinePayloadMapperTest`<br>`php artisan test --filter=EditTransactionWorkspaceRevisionPaymentCharacterizationTest` | FIXED | Do not weaken fingerprint fields. Do not change settlement behavior without regression proof. |
| Phase 4 UI Flexible Package | UI and backend contract match for service store-stock package: one service plus many product lines; template is preset; external purchase remains label + total. | Create workspace and package auto-split tests | `php artisan test --filter=CreateTransactionWorkspaceLineTypeCharacterizationTest`<br>`php artisan test --filter=EditTransactionWorkspacePackageAutoSplitCharacterizationTest` | FIXED | No many-service component feature. No Mobile API. No route/config changes. |
| Phase 5 Refund Component-Type Policy | Product/store-stock components are default refundable; service_fee and external_purchase are default blocked; package refund maps to raw components without double counting. | `RefundReportingOwnerDecisionV2CharacterizationTest`, refund feature/unit tests | `php artisan test --filter=RefundReportingOwnerDecisionV2CharacterizationTest`<br>`php artisan test --filter=ClosedNoteFullRefund`<br>`php artisan test --filter=RecordSelectedRowsCustomerRefund`<br>`php artisan test --filter=RecordCustomerRefundFeatureTest`<br>`php artisan test --filter=RecordClosedNoteRefundControllerFeatureTest`<br>`php artisan test --filter=CashierRefundSelectionFirstFeatureTest`<br>`php artisan test --filter=AllocateRefundAcrossComponentsTest` | FIXED | Do not change refund policy. Manual exception remains deferred unless explicitly designed. |
| Phase 6 Report Query / Service Package Profit Breakdown | Dedicated report query reconciles package profit breakdown from historical rows, inventory COGS, and component-aware refunds without changing Operational Profit. | `ServicePackageProfitBreakdownQueryTest`, reporting boundary regressions | `php artisan test tests/Feature/Reporting/ServicePackageProfitBreakdownQueryTest.php`<br>`php artisan test --filter=TransactionSummary`<br>`php artisan test --filter=TransactionCashLedger`<br>`php artisan test --filter=OperationalProfit` | FIXED | Do not change Operational Profit formula. Do not change ServicePackageProfitBreakdown behavior unless regression proves a bug. |
| Phase 7 Final Regression Matrix | Final docs matrix and command index prove Phase 0-6 remain locked under focused regression and `make verify`. | `0011`, `0016`, focused regression log, full verify log | Focused matrix below, then `make verify`. | VERIFYING | Verification/docs only. No feature, migration, route/config, supplier proof, Mobile API, formula, refund policy, or report behavior change. |

## Focused Regression Command Index

Run in order:

```bash
php artisan test --filter=CreateTransactionWorkspaceLineTypeCharacterizationTest
php artisan test --filter=CreateTransactionWorkspaceServiceStoreStockFeatureTest
php artisan test --filter=CreateTransactionWorkspaceServiceExternalPurchaseFeatureTest
php artisan test --filter=CreateTransactionWorkspaceTemplateContractFeatureTest
php artisan test --filter=CashierWorkspaceServiceProductTemplateMinimumContractFeatureTest
php artisan test --filter=CashierWorkspaceServiceProductTemplateAutofillContractFeatureTest
php artisan test --filter=EditTransactionWorkspaceRevisionPaymentCharacterizationTest
php artisan test --filter=EditTransactionWorkspacePackageAutoSplitCharacterizationTest
php artisan test --filter=CorrectPaidServiceWithStoreStockPartServiceFeeOnly
php artisan test --filter=NoteRevisionLinePayloadMapperTest
php artisan test --filter=RefundReportingOwnerDecisionV2CharacterizationTest
php artisan test --filter=ClosedNoteFullRefund
php artisan test --filter=RecordSelectedRowsCustomerRefund
php artisan test --filter=RecordCustomerRefundFeatureTest
php artisan test --filter=RecordClosedNoteRefundControllerFeatureTest
php artisan test --filter=CashierRefundSelectionFirstFeatureTest
php artisan test --filter=AllocateRefundAcrossComponentsTest
php artisan test --filter=TransactionSummary
php artisan test --filter=TransactionCashLedger
php artisan test --filter=OperationalProfit
php artisan test tests/Feature/Reporting/ServicePackageProfitBreakdownQueryTest.php
make verify
```

## Final Closure Rule

Phase 7 may be marked FIXED only if:
- all focused regression commands pass;
- final `make verify` passes;
- no feature/source behavior is changed for Phase 7;
- `0011` links this matrix;
- boundaries remain unchanged.

Last proof:
Pending local focused regression and `make verify`.
