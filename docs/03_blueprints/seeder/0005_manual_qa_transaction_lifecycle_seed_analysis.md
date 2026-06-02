# Manual QA Transaction Lifecycle Seed Analysis

## Metadata

- Date: 2026-06-03
- Scope: manual QA seed planning for transaction lifecycle
- Status: ANALYSIS / BLUEPRINT CANDIDATE
- Implementation status: not implemented
- Target workflow: create -> edit/revision -> payment change -> refund -> report/projection proof

## Placement Decision

This document belongs in `docs/03_blueprints/seeder/`, not `docs/04_lifecycle/error_log/`.

Reason:

- The finding is not a confirmed bug or security issue.
- The output is a readiness evaluation and seed blueprint.
- The next useful artifact is a planned manual QA seed, not an error remediation record.
- `docs/04_lifecycle/error_log/` remains reserved for individual bug/security findings that must be tracked to completion.

## Current Condition

HyperPOS already has create-only transaction seed profiles for small, normal, peak, and stress data. Those profiles are useful for create-only report sanity, but they do not provide a small manual QA dataset for transaction lifecycle mutation.

The missing lifecycle dataset should be small, deterministic, browser-friendly, and able to expose:

- create transaction behavior;
- active edit/revision behavior;
- payment state changes through the payment route;
- selected-row refund behavior;
- operational profit report impact;
- transaction summary and cash ledger report impact;
- note history projection after mutation.

## Source Files Inspected

### Active Documents

- `docs/04_lifecycle/handoff/0015_create_only_seed_system_stabilization_handoff.md`
- `docs/03_blueprints/seeder/0001_create_only_seed_scale_profiles.md`
- `docs/04_lifecycle/handoff/0008_edit_transaction_lifecycle_characterization_handoff.md`
- `docs/04_lifecycle/handoff/0013_create_transaction_workspace_create_path_closure_handoff.md`
- `docs/04_lifecycle/handoff/0014_edit_revision_service_store_stock_package_autosplit_phase3_handoff.md`

### Seed Files

- `mk/seed.mk`
- `database/seeders/CreateOnly/CreateTransactionWeekSeeder.php`
- `database/seeders/CreateOnly/CreateTransactionMonthNormalSeeder.php`
- `database/seeders/CreateOnly/CreateTransactionMonthNormal100MSeeder.php`
- `database/seeders/CreateOnly/CreateTransactionMonthPeak500MSeeder.php`
- `database/seeders/CreateOnly/CreateTransactionMonthStress8BSeeder.php`
- `database/seeders/CreateOnly/Support/CreateTransactionMonthNormalPayloadFactory.php`
- `database/seeders/CreateOnly/Support/CreateTransactionMonthNormalItemFactory.php`
- `database/seeders/CreateOnly/Support/CreateOnlySeedCalendar.php`

### Transaction Routes And Controllers

- `routes/web/note.php`
- `routes/web/admin_reporting.php`
- `app/Adapters/In/Http/Controllers/Note/StoreTransactionWorkspaceController.php`
- `app/Adapters/In/Http/Controllers/Note/StoreNoteRevisionController.php`
- `app/Adapters/In/Http/Controllers/Note/RecordNotePaymentController.php`
- `app/Adapters/In/Http/Controllers/Note/RecordClosedNoteRefundController.php`

### Request Contracts

- `app/Adapters/In/Http/Requests/Note/StoreTransactionWorkspaceRequest.php`
- `app/Adapters/In/Http/Requests/Note/StoreTransactionWorkspaceRules.php`
- `app/Adapters/In/Http/Requests/Note/StoreTransactionWorkspaceValidator.php`
- `app/Adapters/In/Http/Requests/Note/StoreNoteRevisionRequest.php`
- `app/Adapters/In/Http/Requests/Note/UpdateTransactionWorkspaceRules.php`
- `app/Adapters/In/Http/Requests/Note/UpdateTransactionWorkspaceValidator.php`
- `app/Adapters/In/Http/Requests/Note/RecordNotePaymentRequest.php`
- `app/Adapters/In/Http/Requests/Note/RecordClosedNoteRefundRequest.php`

### Application Use Cases And Services

- `app/Application/Note/UseCases/CreateTransactionWorkspaceHandler.php`
- `app/Application/Note/UseCases/CreateNoteRevisionHandler.php`
- `app/Application/Note/UseCases/CreateNoteRevisionWorkflow.php`
- `app/Application/Note/UseCases/UpdateTransactionWorkspaceHandler.php`
- `app/Application/Note/Services/ApplyNoteRevisionAsActiveReplacement.php`
- `app/Application/Note/Services/ReverseIssuedInventoryByNoteService.php`
- `app/Application/Note/Services/NoteReplacementPaymentAllocationReconciler.php`
- `app/Application/Note/Services/BuildCreateNoteRevisionSettlement.php`
- `app/Application/Note/Services/BuildNoteRevisionSettlement.php`
- `app/Application/Payment/UseCases/RecordAndAllocateNotePaymentHandler.php`
- `app/Application/Payment/UseCases/RecordCustomerRefundHandler.php`
- `app/Application/Payment/Services/RecordSelectedRowsRefundPlanTransaction.php`
- `app/Application/Payment/Services/RecordSelectedRowsRefundPlanBucketProcessor.php`
- `app/Application/Payment/Services/AllocateRefundAcrossComponents.php`
- `app/Application/Inventory/Services/AutoReverseRefundedStoreStockInventory.php`

### Report And Projection Files

- `app/Adapters/Out/Reporting/Queries/OperationalProfitMetricsQuery.php`
- `app/Adapters/Out/Reporting/Queries/OperationalProfit/CashFlowMetricQuery.php`
- `app/Adapters/Out/Reporting/Queries/OperationalProfit/ProductCostMetricQuery.php`
- `app/Adapters/Out/Reporting/Queries/TransactionSummaryReportingQuery.php`
- `app/Adapters/Out/Reporting/Queries/TransactionCashLedgerReportingQuery.php`
- `app/Adapters/Out/Reporting/Queries/TransactionCashLedgerRefundRowsQuery.php`
- `app/Application/Reporting/UseCases/GetOperationalProfitSummaryHandler.php`
- `app/Application/Reporting/UseCases/GetTransactionReportDatasetHandler.php`
- `app/Application/Reporting/UseCases/GetTransactionSummaryPerNoteHandler.php`
- `app/Application/Reporting/UseCases/GetTransactionCashLedgerPerNoteHandler.php`
- `app/Application/Note/Services/NoteHistoryProjectionService.php`
- `app/Adapters/Out/Note/DatabaseNoteHistoryProjectionSourceReaderAdapter.php`
- `database/migrations/2026_04_19_100100_create_note_history_projection_table.php`

## Locked Facts

- `mk/seed.mk` has create-only transaction targets:
  - `seed-transaction-week`
  - `seed-transaction-month-normal`
  - `seed-transaction-month-normal-100m`
  - `seed-transaction-month-peak-500m`
  - `seed-transaction-month-stress-8b`
- Existing `CreateOnly` transaction seeders use `App\Application\Note\UseCases\CreateTransactionWorkspaceHandler`.
- No `CreateOnly` seeder reference was found for:
  - `CreateNoteRevisionHandler`
  - `RecordSelectedRowsRefundPlanTransaction`
  - `RecordCustomerRefundHandler`
  - `customer_refunds`
  - `refund_component_allocations`
  - `note_revisions`
  - `note_revision_settlements`
- No explicit manual QA transaction lifecycle seed artifact was found by searching names such as:
  - `ManualQA`
  - `ManualQaTransaction`
  - `manual-qa`
  - `manual_qa`
  - `TransactionLifecycleSeeder`
  - `seed-transaction-lifecycle`
- Active create route:
  - `POST /notes/workspace/store`
  - controller: `StoreTransactionWorkspaceController`
  - handler: `CreateTransactionWorkspaceHandler`
- Active edit submit route is revision-based:
  - `PATCH /admin/notes/{noteId}/workspace`
  - `PATCH /cashier/notes/{noteId}/workspace`
  - controller: `StoreNoteRevisionController`
  - request: `StoreNoteRevisionRequest`
  - handler: `CreateNoteRevisionHandler`
- `StoreNoteRevisionRequest` forces `inline_payment.decision = skip`.
- Payment state changes are separate from revision submit:
  - controller: `RecordNotePaymentController`
  - handler: `RecordAndAllocateNotePaymentHandler`
- Refund is selected-row based:
  - controller: `RecordClosedNoteRefundController`
  - request: `RecordClosedNoteRefundRequest`
  - transaction service: `RecordSelectedRowsRefundPlanTransaction`
- Operational profit summary uses:
  - cash-in from `customer_payments`;
  - refund outflow from `customer_refunds` and `note_revision_surplus_refund_payments`;
  - external purchase cost from `work_item_external_purchase_lines`;
  - store-stock COGS and reversal from `inventory_movements`;
  - operating costs from expenses, payroll, and employee debt cash-out.
- `note_history_projection` is a current read model, not historical truth.
- Stress 8B exists as an executable extreme profile but is paused and must not block create/report/edit/refund work.

## Gaps

### Proven Missing

- No explicit manual QA transaction lifecycle seed target exists in `mk/seed.mk`.
- No explicit manual QA transaction lifecycle seeder exists under `database/seeders`.
- No existing create-only seeder performs edit/revision mutation.
- No existing create-only seeder performs selected-row refund mutation.

### Not Yet Proven

- No proof yet that one small dataset can support manual browser QA across create, edit/revision, payment change, refund, reports, and projection.
- No proof yet that report/export remains aligned after a seeded create/edit/refund lifecycle dataset.
- No proof yet that selected-row full and partial refunds in a small seeded dataset produce matching:
  - customer refund rows;
  - refund component allocations;
  - inventory reversal rows;
  - note history projection state;
  - operational profit numbers;
  - transaction cash ledger rows.
- No proof yet that a future manual QA seed can be safely idempotent across reruns without stale mutation side effects.

## Decision

The next implementation direction should be a small manual QA transaction lifecycle seed, not refund hardening.

Reason:

- Refund touches payment allocation, refund allocation, note state, row cancellation, inventory reversal, audit, report math, and projection.
- Existing seed coverage is create-only and does not produce a compact lifecycle dataset for browser inspection.
- Existing feature tests are granular but do not replace a human-facing seed because many fixtures are direct inserts and do not generate a coherent browser QA dataset.
- Manual QA seed gives a stable data surface before adding or hardening refund behavior.

## Blueprint

### Target

Create a deterministic seed profile for manual transaction lifecycle QA.

Candidate seeder:

```text
database/seeders/ManualQa/TransactionLifecycleManualQaSeeder.php
```

Candidate support files:

```text
database/seeders/ManualQa/Support/TransactionLifecycleManualQaPayloadFactory.php
database/seeders/ManualQa/Support/TransactionLifecycleManualQaMutator.php
```

Candidate make targets:

```text
seed-manual-qa-transaction-lifecycle
manual-qa-transaction-lifecycle
```

Recommended target behavior:

```text
manual-qa-transaction-lifecycle:
    seed base users/access/master/inventory
    seed compact lifecycle transaction notes
    optionally pre-apply bounded lifecycle mutations for report anchors
    seed audit baseline
    php artisan projection:rebuild-indexes all
```

### Dataset Size

Target range:

```text
8 to 12 notes
```

Purpose:

- fast enough for browser QA;
- small enough to inspect by customer names;
- broad enough to cover service-only, store-stock, external purchase, package auto split, partial payment, full payment, refund, and report behavior.

### Handler Rules

Use real application paths:

- create must use `CreateTransactionWorkspaceHandler`;
- edit/revision must use `CreateNoteRevisionHandler`;
- payment changes must use `RecordAndAllocateNotePaymentHandler`;
- selected-row refund must use `SelectedNoteRowsRefundPlanResolver` and `RecordSelectedRowsRefundPlanTransaction`;
- projection proof must use `NoteHistoryProjectionService` or `projection:rebuild-indexes all`.

Do not raw-insert these lifecycle records unless a separate proof-only fixture is explicitly scoped:

- `notes`;
- `work_items`;
- `customer_payments`;
- `payment_component_allocations`;
- `customer_refunds`;
- `refund_component_allocations`;
- `note_revisions`;
- `note_revision_settlements`;
- `note_history_projection`;
- `inventory_movements`.

### Dataset Naming

Use customer names that make manual browser search easy:

```text
MQA Lifecycle 001 Service Full Paid
MQA Lifecycle 002 Store Stock Full Paid
MQA Lifecycle 003 External Partial Paid
MQA Lifecycle 004 Package Store Stock
MQA Lifecycle 005 Edit Quantity Candidate
MQA Lifecycle 006 Edit Payment Candidate
MQA Lifecycle 007 Refund Full Candidate
MQA Lifecycle 008 Refund Partial Candidate
MQA Lifecycle 009 Report Anchor After Refund
MQA Lifecycle 010 Projection Anchor
```

### Required Scenarios

| # | Scenario | Seed state | Mutation path | Proof target |
|---:|---|---|---|---|
| 1 | Create service-only full paid | Service-only note with full cash or transfer payment | `CreateTransactionWorkspaceHandler` | closed/settled note, payment allocation, projection outstanding 0 |
| 2 | Create store-stock full paid | Store-stock or service-store-stock note with enough inventory | `CreateTransactionWorkspaceHandler` | stock out, COGS, payment allocation, projection close |
| 3 | Create external-purchase partial paid | Service with external purchase and partial payment | `CreateTransactionWorkspaceHandler` | outstanding remains, external cost appears, projection open |
| 4 | Create package/store-stock | Package auto split with multiple store-stock products | `CreateTransactionWorkspaceHandler` | package total, parts total, service residual, stock out |
| 5 | Edit quantity/price/service fields | Editable note with store-stock/package line | `CreateNoteRevisionHandler` | new revision, old stock reversal, replacement stock issue, projection sync |
| 6 | Edit payment state | Partial or unpaid note | `RecordAndAllocateNotePaymentHandler` | payment row, component allocations, outstanding changes |
| 7 | Refund full | Closed paid service/store-stock/external note | selected-row refund transaction | refund rows, note finalization when total becomes 0 |
| 8 | Refund partial | Closed paid multi-row note | selected-row refund transaction | only selected row canceled/refunded, other row remains active |
| 9 | Report operational cash/profit after create/edit/refund | Period contains all above notes | report handlers/pages | cash-in, refunded, COGS reversal, external cost netting |
| 10 | Note history projection after mutation | Same notes after mutations | projection sync/rebuild | line open/close/refund counts and outstanding/net paid align |

## Proof Checklist For Future Implementation

### Static Proof

Run after patching the seeder:

```text
php -l database/seeders/ManualQa/TransactionLifecycleManualQaSeeder.php
php -l database/seeders/ManualQa/Support/TransactionLifecycleManualQaPayloadFactory.php
```

Run after wiring make target:

```text
rg -n "seed-manual-qa-transaction-lifecycle|manual-qa-transaction-lifecycle" mk/seed.mk
```

### Runtime Proof

Run only after the implementation step is intentionally opened:

```text
php artisan migrate:fresh --seed
make manual-qa-transaction-lifecycle
```

Expected proof categories:

- planned notes count;
- created vs replayed count;
- edited/revised count if pre-mutated anchors exist;
- payment mutation count if pre-mutated anchors exist;
- refund count if pre-mutated anchors exist;
- projection rebuild count;
- operational profit summary row;
- transaction summary reconciliation;
- transaction cash ledger reconciliation;
- inventory reversal count for store-stock refund/edit;
- note history projection count and selected row values.

### Manual Browser Proof

Minimum manual QA pages:

- cashier note history;
- admin note history;
- note detail;
- workspace create;
- workspace edit;
- payment form;
- refund form;
- operational profit report;
- transaction report;
- transaction cash ledger report.

## Risk Register

### Domain Risk

Active edit behavior is revision-based. A seed that calls `UpdateTransactionWorkspaceHandler` would test a route-unproven path and produce misleading confidence.

### Report Risk

Operational profit, transaction summary, and cash ledger read different source records. A mutation can be correct in one report and wrong in another.

### Inventory Risk

Edit/revision and refund can both create inventory reversal records, but through different source types:

- edit/revision reversal: `transaction_workspace_updated`;
- refund reversal: `work_item_store_stock_line_reversal`.

### Payment Allocation Risk

Revision captures existing allocated payment amounts, deletes component allocations, and rebuilds allocations against replacement components. This can change outstanding/surplus semantics.

### Refund Allocation Risk

Selected-row refunds must allocate only to selected current rows and must reject stale, open, unpaid, canceled, or already refunded rows.

### Projection Risk

`note_history_projection` is a current read model. It can become stale unless every mutation syncs it or the manual QA target rebuilds projections.

### Idempotency Risk

Create seed payloads can use idempotency keys. Edit/payment/refund mutations need separate rerun strategy because they may not all be naturally replay-safe.

## Recommended Implementation Order

1. Patch only the blueprint-to-code skeleton for manual QA lifecycle seed.
2. Add payload factory for create-only candidate notes.
3. Add optional mutator only after create candidate proof is green.
4. Wire one make target.
5. Run syntax proof.
6. Run make target grep proof.
7. Run fresh database seed proof.
8. Run row-count and report sanity proof.
9. Run browser manual QA checklist.
10. Update handoff after proof.

## Next Active Step Candidate

Patch the smallest seeder skeleton and make target only:

```text
database/seeders/ManualQa/TransactionLifecycleManualQaSeeder.php
mk/seed.mk
```

Do not implement refund hardening in the same step.

Do not start Stress 8B closure, 10B, Go API, PostgreSQL, employee finance, payroll, or soft delete work in the same step.

