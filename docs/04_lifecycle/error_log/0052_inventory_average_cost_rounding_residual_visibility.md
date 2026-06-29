# 0052 - Inventory Average Cost Rounding Residual Visibility

## Status

PATCHED - dataset-level diagnostic visibility proof PASS.

## Context

This slice follows:

- `0051_inventory_costing_rebuild_same_day_stock_out_skip.md`

The previous confirmed engine bug was fixed by changing `InventoryCostingProjectionBuilder` to ledger aggregation:

```text
qty   = SUM(qty_delta)
value = SUM(total_cost_rupiah)
avg   = intdiv(value, qty)
```

Post-rebuild proof showed:

```text
total_qty_diff          = 0
total_value_diff        = 0
total_rounding_residual = 26
```

Remaining residual rows:

```text
prod-year-001: 10650 - (1183 * 9)  = 3
prod-year-006: 34493 - (1149 * 30) = 23
total residual = 26
```

## Problem

The remaining value difference is not a costing engine mismatch.

It is true integer average-cost rounding residual:

```text
rounding_residual = inventory_value_rupiah - (avg_cost_rupiah * qty_on_hand)
```

Before this slice, the inventory stock value dataset exposed:

```text
current_qty_on_hand
current_avg_cost_rupiah
current_inventory_value_rupiah
```

It did not expose:

```text
value derived from average cost
rounding residual
movement ledger qty/value
ledger diff between projection and movement ledger
```

This made it easy to misread rounding residual as projection mismatch.

## Scope

This slice only adds internal/reporting diagnostic visibility.

Hard boundaries:

- Do not change costing engine.
- Do not change HPP.
- Do not change main inventory value semantics.
- Do not aggressively alter UI/export presentation yet.
- Separate ledger mismatch from integer rounding residual.

## Failing Test First

Added regression/visibility test:

```text
test_inventory_stock_value_report_dataset_exposes_rounding_residual_separately_from_ledger_mismatch
```

Initial failure:

```text
Undefined array key "current_inventory_value_by_average_rupiah"
```

This confirmed the dataset did not expose residual diagnostic fields.

## Patch Summary

Added dataset fields to inventory current snapshot reporting:

```text
current_inventory_value_by_average_rupiah
current_rounding_residual_rupiah
ledger_qty_on_hand
ledger_inventory_value_rupiah
ledger_qty_diff
ledger_value_diff_rupiah
```

Added summary totals:

```text
total_inventory_value_by_average_rupiah
total_rounding_residual_rupiah
total_ledger_qty_diff
total_ledger_value_diff_rupiah
```

## Files Changed

Expected changed files for this slice:

- `app/Adapters/Out/Reporting/InventoryCurrentSnapshotDatabaseQuery.php`
- `app/Adapters/Out/Reporting/InventoryCurrentSnapshotRowMapper.php`
- `app/Application/Reporting/Services/InventoryStockValueReportSummaryBuilder.php`
- `tests/Feature/Reporting/GetInventoryStockValueReportDatasetFeatureTest.php`
- `docs/04_lifecycle/error_log/0052_inventory_average_cost_rounding_residual_visibility.md`

## Acceptance Proof

Owner reported:

```text
GetInventoryStockValueReportDatasetFeatureTest PASS
all relevant tests PASS
```

Required local proof command:

```bash
php artisan test --filter=GetInventoryStockValueReportDatasetFeatureTest
```

Expected:

```text
PASS
```

## Final Status

Dataset now separates:

```text
ledger mismatch:
projection_value - movement_ledger_value

rounding residual:
inventory_value_rupiah - (avg_cost_rupiah * qty_on_hand)
```

So `total_ledger_value_diff_rupiah = 0` means projection correctness is clean.

`total_rounding_residual_rupiah = 26` means integer average-cost storage has expected residual visibility.

## Next Slice

Recommended next slice:

```text
0053 - Inventory Rounding Residual Export/UI Presentation
```

Possible scope:

- show residual in Excel diagnostic/internal columns
- optionally show summary diagnostic only in internal/admin view
- do not replace main inventory value
- keep public-facing wording simple

