# 0058 - Inventory Deleted Product Excel Export Visibility

## Status

Resolved.

## Context

Slice ini melanjutkan `0057` dengan mengunci visibility label produk soft-deleted dan orphan/missing product pada export Excel laporan stok dan nilai persediaan.

`0057` sudah memastikan dataset movement report aman:

- active product memakai nama normal
- soft-deleted product memakai label `[Produk terhapus] {nama_barang}`
- orphan/missing product memakai label `[Produk tidak ditemukan: {product_id}]`
- current snapshot tetap exclude deleted/orphan/ledger-only product

`0058` memastikan label tersebut benar-benar terbawa ke Excel sheet `Mutasi Periode`.

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

Sebelum slice ini, `0057` sudah mengunci dataset, tetapi belum ada proof export Excel untuk label deleted/orphan product.

Risiko:

- Dataset benar, tetapi Excel bisa saja menampilkan label mentah, kosong, atau tidak membawa movement orphan/deleted.
- Snapshot Excel bisa saja tercemar deleted/orphan jika export path memakai dataset berbeda.
- Owner bisa salah membaca movement historis jika label di Excel tidak sejalan dengan dataset.

## Source Map

### Dataset

- `app/Application/Reporting/UseCases/GetInventoryStockValueReportDatasetHandler.php`
  - Menghasilkan:
    - `snapshot_rows`
    - `movement_rows`
    - `summary`

### Query

- `app/Adapters/Out/Reporting/InventoryMovementSummaryDatabaseQuery.php`
  - Source utama: `inventory_movements`
  - Join product: `leftJoin('products')`
  - Label fallback:
    - active: nama normal
    - soft-deleted: `[Produk terhapus] {nama_barang}`
    - orphan/missing: `[Produk tidak ditemukan: {product_id}]`

### Excel Export

- `app/Adapters/In/Http/Controllers/Admin/Reporting/InventoryStockValueReportExcelExportController.php`
  - Memanggil full dataset via `handle(...)`.

- `app/Application/Reporting/Exports/InventoryStockValueReportExcelWorkbookBuilder.php`
  - Membuat sheet:
    - `Ringkasan`
    - `Snapshot Stok`
    - `Mutasi Periode`

- `app/Application/Reporting/Exports/InventoryStockValueReportExcelMovementSheetWriter.php`
  - Menulis `movement_rows` ke sheet `Mutasi Periode`.

## Test Added

File:

- `tests/Feature/ReportingExports/InventoryStockValueReportExcelExportFeatureTest.php`

Test:

- `test_inventory_stock_value_excel_export_keeps_deleted_and_orphan_movement_labels_without_snapshot_pollution`

Coverage:

- Excel sheet `Snapshot Stok` hanya memuat active product.
- Soft-deleted product tidak masuk snapshot.
- Orphan product tidak masuk snapshot.
- Excel sheet `Mutasi Periode` tetap memuat active, soft-deleted, dan orphan product movement.
- Soft-deleted movement label:
  - `[Produk terhapus] Excel Deleted Part`
- Orphan movement label:
  - `[Produk tidak ditemukan: excel-product-orphan]`
- Orphan `kode_barang` terbaca `null` saat workbook dibaca ulang oleh PhpSpreadsheet.
- Qty dan nilai movement period tetap benar.

## Implementation

Production code tidak berubah.

Slice ini hanya menambah regression coverage untuk export Excel.

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

Excel export visibility untuk deleted/orphan product sudah terkunci.

### P2

Resolved.

Excel owner-facing label sekarang terbukti konsisten dengan dataset movement.

### P3

No cleanup/refactor.

