# 0059 - Inventory Stock Value Summary-only Deleted Orphan Parity

## Status

Resolved.

## Context

Slice ini melanjutkan `0057` dan `0058` dengan mengunci parity antara full dataset summary dan summary-only aggregate pada laporan stok dan nilai persediaan.

`0057` mengunci dataset movement untuk produk soft-deleted dan orphan/missing product.

`0058` mengunci export Excel agar label deleted/orphan dari dataset movement terbawa ke sheet `Mutasi Periode`.

`0059` memastikan path summary-only yang dipakai halaman dan PDF tetap menghasilkan angka yang sama dengan full dataset summary ketika ada deleted/orphan movement.

## Hard Boundary

- Tidak mengubah costing engine.
- Tidak mengubah HPP.
- Tidak mengubah semantic `inventory_value_rupiah`.
- Tidak repair/write production DB.
- Tidak menambah migration.
- Tidak mengubah bucket membership `inventory_movements.source_type`.
- Tidak mengubah production code.
- Tidak memasukkan ledger-only/orphan product ke current snapshot.

## Problem

Full dataset path dan summary-only path memakai jalur berbeda:

Full dataset:

- `GetInventoryStockValueReportDatasetHandler::handle(...)`
- membaca `movement_rows`
- membaca `snapshot_rows`
- membangun summary dari rows

Summary-only:

- `GetInventoryStockValueReportDatasetHandler::handleSummaryOnly(...)`
- langsung membaca aggregate dari `InventoryStockValueSummaryDatabaseQuery`

Halaman dan PDF memakai summary-only path. Tanpa test parity, dataset bisa benar tetapi halaman/PDF summary bisa drift.

## Source Map

### Full Dataset Path

- `app/Application/Reporting/UseCases/GetInventoryStockValueReportDatasetHandler.php`
  - `handle(...)`
  - memanggil movement summary handler
  - membaca current snapshot rows
  - membangun summary dari `InventoryStockValueReportSummaryBuilder`

- `app/Application/Reporting/Services/InventoryStockValueReportSummaryBuilder.php`
  - summary dari `snapshot_rows` dan `movement_rows`

### Summary-only Path

- `app/Application/Reporting/UseCases/GetInventoryStockValueReportDatasetHandler.php`
  - `handleSummaryOnly(...)`
  - memanggil `getInventoryStockValueSummary(...)`

- `app/Adapters/Out/Reporting/InventoryStockValueSummaryDatabaseQuery.php`
  - snapshot aggregate:
    - source utama `products`
    - `whereNull('products.deleted_at')`
    - requires `product_inventory` atau `product_inventory_costing`
  - movement aggregate:
    - source utama `inventory_movements`
    - aggregate langsung dari movement ledger periode

### UI/PDF Consumers

- `app/Adapters/In/Http/Controllers/Admin/Reporting/InventoryStockValueReportPageController.php`
  - memakai `handleSummaryOnly(...)`

- `app/Adapters/In/Http/Controllers/Admin/Reporting/InventoryStockValueReportPdfExportController.php`
  - memakai `handleSummaryOnly(...)`

## Test Added

File:

- `tests/Feature/Reporting/InventoryDeletedProductMovementReportVisibilityFeatureTest.php`

Test:

- `test_summary_only_path_matches_full_dataset_summary_when_deleted_and_orphan_movements_exist`

Coverage:

- Active product masuk full dataset snapshot.
- Soft-deleted product tidak masuk snapshot.
- Orphan/missing product tidak masuk snapshot.
- Movement period menghitung active + soft-deleted + orphan.
- Full dataset summary sama persis dengan summary-only aggregate.
- `snapshot_product_rows` tetap 1.
- `movement_product_rows` tetap 3.
- `total_qty_on_hand` tetap dari active snapshot.
- `total_inventory_value_rupiah` tetap dari active snapshot.
- Period supply/net qty dan period net cost tetap menghitung semua movement periode.

## Implementation

Production code tidak berubah.

Slice ini hanya menambah regression coverage untuk parity full dataset vs summary-only.

## Proof

Owner reported targeted tests PASS:

```bash
php artisan test \
  tests/Feature/Reporting/InventoryDeletedProductMovementReportVisibilityFeatureTest.php \
  tests/Feature/ReportingExports/InventoryStockValueReportExcelExportFeatureTest.php \
  tests/Feature/Reporting/GetInventoryStockValueReportDatasetFeatureTest.php
```

Result:

```text
PASS
```

## Risk Classification

### P0

None found.

### P1

Resolved.

Summary-only aggregate untuk page/PDF sudah terbukti parity dengan full dataset pada case deleted/orphan movement.

### P2

Resolved.

Drift risk antara full dataset dan summary-only path sudah dikunci test.

### P3

No cleanup/refactor.

