# 0053 - Inventory Rounding Residual Report Presentation

## Status

PATCHED - UI page, Excel export, PDF summary builder, and regression tests PASS.

## Context

This slice follows:

- `0051_inventory_costing_rebuild_same_day_stock_out_skip.md`
- `0052_inventory_average_cost_rounding_residual_visibility.md`

Slice `0052` added dataset-level diagnostic fields for average-cost rounding residual and ledger diff.

However, report presentation was still incomplete:

- report page did not show residual or ledger diff
- Excel summary did not show residual or ledger diff
- Excel snapshot did not show per-product residual or ledger diff
- PDF builder did not include diagnostic summary items

## Problem

The system already separated:

```text
ledger mismatch:
projection_value - movement_ledger_value

rounding residual:
inventory_value_rupiah - (avg_cost_rupiah * qty_on_hand)
```

But operators still saw only:

```text
Nilai Persediaan
```

without seeing whether a visible difference came from:

- real ledger mismatch
- expected integer average-cost rounding residual

This was critical because residual values such as:

```text
prod-year-001: 3
prod-year-006: 23
total residual: 26
```

could be misread as a costing bug.

## Failing Test First

Added presentation visibility tests:

```text
test_inventory_stock_value_page_shows_rounding_residual_and_ledger_diff_diagnostics
test_inventory_stock_value_excel_export_shows_rounding_residual_diagnostics
test_inventory_stock_value_pdf_view_shows_rounding_residual_diagnostic_summary
```

Initial proof:

```text
FAIL  InventoryStockValueReportPageFeatureTest
- Expected HTML to contain: Nilai Berdasar Avg x Qty

FAIL  InventoryStockValueReportExcelExportFeatureTest
- Failed asserting that summary cells contain: Nilai Berdasar Avg x Qty

PASS  InventoryStockValueReportPdfExportFeatureTest
- PDF view test passed because summaryItems were injected directly
```

## Patch Summary

### Page Summary Query

Updated:

- `app/Adapters/Out/Reporting/InventoryStockValueSummaryDatabaseQuery.php`

Added summary-only diagnostics:

```text
total_inventory_value_by_average_rupiah
total_rounding_residual_rupiah
total_ledger_qty_diff
total_ledger_value_diff_rupiah
```

Important fix:

Do not include ledger-only products as current snapshot rows.

The movement ledger subquery is used only for diagnostic diff calculation.

Snapshot inclusion remains limited to products that have current inventory and/or current costing rows.

### Page UI

Updated:

- `resources/views/admin/reporting/inventory_stock_value/index.blade.php`

Added diagnostic cards:

```text
Nilai Berdasar Avg x Qty
Residual Pembulatan HPP
Selisih Qty Ledger
Selisih Nilai Ledger
```

### Excel Summary

Updated:

- `app/Application/Reporting/Exports/InventoryStockValueReportExcelSummarySheetWriter.php`

Added diagnostic section:

```text
Diagnostik Internal
Nilai Berdasar Avg x Qty
Residual Pembulatan HPP
Selisih Qty Ledger
Selisih Nilai Ledger
```

### Excel Snapshot

Updated:

- `app/Application/Reporting/Exports/InventoryStockValueReportExcelSnapshotSheetWriter.php`

Added per-product diagnostic columns:

```text
Nilai Avg x Qty
Residual Pembulatan HPP
Qty Ledger
Nilai Ledger
Selisih Qty Ledger
Selisih Nilai Ledger
```

Existing columns A-J remain stable.

### PDF Summary Builder

Updated:

- `app/Application/Reporting/Exports/InventoryStockValueReportPdfViewDataBuilder.php`

Added diagnostic summary items:

```text
Nilai Berdasar Avg x Qty
Residual Pembulatan HPP
Selisih Qty Ledger
Selisih Nilai Ledger
```

## Regression Fix During Patch

After the initial presentation patch, Excel export test failed:

```text
Failed asserting that 5 is identical to 4.
```

Cause:

```text
ledger-only product was included as snapshot row
```

Fix:

- Keep movement ledger join for diagnostic diff calculation.
- Remove ledger-only inclusion from snapshot filter.

Snapshot inclusion remains based on current inventory and/or current costing rows.

Files affected:

- `app/Adapters/Out/Reporting/InventoryCurrentSnapshotDatabaseQuery.php`
- `app/Adapters/Out/Reporting/InventoryStockValueSummaryDatabaseQuery.php`

## Files Changed

Expected files changed in this slice:

- `app/Adapters/Out/Reporting/InventoryCurrentSnapshotDatabaseQuery.php`
- `app/Adapters/Out/Reporting/InventoryStockValueSummaryDatabaseQuery.php`
- `resources/views/admin/reporting/inventory_stock_value/index.blade.php`
- `app/Application/Reporting/Exports/InventoryStockValueReportExcelSummarySheetWriter.php`
- `app/Application/Reporting/Exports/InventoryStockValueReportExcelSnapshotSheetWriter.php`
- `app/Application/Reporting/Exports/InventoryStockValueReportPdfViewDataBuilder.php`
- `tests/Feature/Reporting/InventoryStockValueReportPageFeatureTest.php`
- `tests/Feature/ReportingExports/InventoryStockValueReportExcelExportFeatureTest.php`
- `tests/Feature/ReportingExports/InventoryStockValueReportPdfExportFeatureTest.php`
- `docs/04_lifecycle/error_log/0053_inventory_rounding_residual_report_presentation.md`

## Acceptance Proof

Owner reported final test result:

```text
PASS all targeted tests
```

Required proof command:

```bash
php artisan test \
  tests/Feature/Reporting/InventoryStockValueReportPageFeatureTest.php \
  tests/Feature/ReportingExports/InventoryStockValueReportExcelExportFeatureTest.php \
  tests/Feature/ReportingExports/InventoryStockValueReportPdfExportFeatureTest.php \
  tests/Feature/Reporting/GetInventoryStockValueReportDatasetFeatureTest.php
```

Expected:

```text
PASS
```

Observed after final fix:

```text
20 passed
```

## Final Decision

This is now presentation-complete for critical visibility:

- dataset exposes residual and ledger diff
- page shows residual and ledger diff
- Excel summary shows residual and ledger diff
- Excel snapshot shows per-product residual and ledger diff
- PDF summary builder includes residual and ledger diff

Main inventory value semantics are unchanged.

Costing engine is unchanged.

HPP is unchanged.

## Next Recommended Slice

Optional next slice:

```text
0054 - Inventory Rounding Residual Owner Wording and Report Copy Polish
```

Possible scope:

- improve wording for non-technical owner
- add tooltip/explanation text
- keep internal diagnostic labels in Excel
- avoid changing calculations

