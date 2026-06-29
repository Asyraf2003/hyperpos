# 0056 - Inventory Movement Source Type Registry Guard

## Status

PATCHED - targeted registry/reporting proof PASS.

## Context

The inventory/reporting analysis found repeated `inventory_movements.source_type` literals in report aggregate SQL.

Those literals define owner-facing movement buckets:

- supply in
- sale out
- refund/reversal
- revision/correction

The risk was not a current numeric mismatch, but discipline: if one query changed a source-type list and another did not, dashboard/report/export numbers could drift.

Hard boundaries:

- Do not change costing engine.
- Do not change HPP.
- Do not change `inventory_value_rupiah` semantics.
- Do not change movement bucket semantics in this slice.
- Do not migrate or repair production data.

## Problem

The same reporting bucket source-type lists were repeated in:

- inventory stock value summary query
- inventory movement summary query
- inventory movement reconciliation query
- dashboard inventory movement summary query

Examples:

```text
supplier_receipt_line
work_item_store_stock_line
note
customer_transaction_line
work_item_store_stock_line_reversal
```

This made future bucket changes easy to apply partially.

## Patch Summary

Added:

- `app/Application/Inventory/Support/InventoryMovementSourceTypes.php`

Updated:

- `app/Adapters/Out/Reporting/InventoryStockValueSummaryDatabaseQuery.php`
- `app/Adapters/Out/Reporting/InventoryMovementSummaryDatabaseQuery.php`
- `app/Adapters/Out/Reporting/InventoryMovementReconciliationDatabaseQuery.php`
- `app/Adapters/Out/Reporting/Queries/DashboardInventory/DashboardInventoryMovementSummaryQuery.php`

Change:

```text
Centralize reporting bucket source-type lists and deterministic SQL literal lists.
```

The helper exposes:

- `saleOutSourceTypes()`
- `classifiedForReportingSourceTypes()`
- `supplierReceiptLineSql()`
- `storeStockLineReversalSql()`
- `saleOutSqlList()`
- `classifiedForReportingSqlList()`

No bucket membership changed.

## Regression Test

Added:

- `tests/Unit/Application/Inventory/Support/InventoryMovementSourceTypesTest.php`

Covered:

- reporting bucket source-type lists remain stable
- SQL lists are deterministic and quoted

Existing reporting tests were rerun to prove no behavior drift.

## Proof

Before patch, new registry test failed:

```text
Class "App\Application\Inventory\Support\InventoryMovementSourceTypes" not found
```

After patch:

```text
php artisan test tests/Unit/Application/Inventory/Support/InventoryMovementSourceTypesTest.php tests/Feature/Reporting/GetInventoryMovementSummaryFeatureTest.php tests/Feature/Reporting/InventoryMovementBucketSplitFeatureTest.php tests/Feature/Reporting/InventoryMovementSummaryHardeningFeatureTest.php tests/Feature/Reporting/GetInventoryStockValueReportDatasetFeatureTest.php tests/Feature/Reporting/InventoryStockValueReportPageFeatureTest.php

Tests: 16 passed (131 assertions)
```

Dashboard/reconciliation smoke:

```text
php artisan test tests/Feature/Reporting/TransactionReportingReconciliationFeatureTest.php tests/Feature/Admin/AdminDashboardPageFeatureTest.php

Tests: 6 passed (104 assertions)
```

Raw-string guard check:

```text
rg -n "source_type = 'supplier_receipt_line'|source_type IN \\('work_item_store_stock_line', 'note', 'customer_transaction_line'\\)|source_type = 'work_item_store_stock_line_reversal'|source_type NOT IN \\('supplier_receipt_line', 'work_item_store_stock_line', 'note', 'customer_transaction_line', 'work_item_store_stock_line_reversal'\\)" app/Adapters/Out/Reporting

No matches.
```

## Decision

Patch accepted as a reporting-discipline guard.

This does not make unknown `source_type` invalid. Unknown or less common source types still fall into revision/correction reporting unless explicitly classified in a future slice.

## Next Slice Candidate

`0057_inventory_deleted_product_movement_report_visibility`

Possible scope:

- verify movement reports preserve deleted/orphan product visibility
- ensure current snapshot still excludes ledger-only products
- add tests around product name fallback and snapshot exclusion
