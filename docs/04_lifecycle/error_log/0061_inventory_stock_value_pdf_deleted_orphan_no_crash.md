# 0061 - Inventory Stock Value PDF Deleted Orphan No-crash

## Status

Resolved.

## Context

Slice ini menutup rantai `0057` sampai `0061` untuk laporan stok dan nilai persediaan.

`0057` mengunci dataset movement untuk produk soft-deleted dan orphan/missing product.

`0058` mengunci export Excel agar label deleted/orphan terbawa ke sheet `Mutasi Periode`.

`0059` mengunci parity full dataset summary dan summary-only aggregate.

`0060` mengunci HTTP page summary.

`0061` memastikan PDF export route tetap aman ketika ada movement untuk produk soft-deleted dan orphan/missing product.

## Hard Boundary

- Tidak mengubah costing engine.
- Tidak mengubah HPP.
- Tidak mengubah semantic `inventory_value_rupiah`.
- Tidak repair/write production DB.
- Tidak menambah migration.
- Tidak mengubah bucket membership `inventory_movements.source_type`.
- Tidak mengubah production code.
- Tidak memasukkan ledger-only/orphan product ke current snapshot.
- Tidak mengubah PDF layout.

## Problem

PDF export memakai summary-only path dan Dompdf.

Walaupun `0059` sudah membuktikan summary-only aggregate benar, PDF route tetap perlu no-crash proof karena PDF rendering adalah jalur runtime berbeda.

Risiko:

- Controller PDF gagal saat summary-only data berisi deleted/orphan movement.
- Dompdf gagal render akibat data edge-case.
- Response bukan PDF valid.
- Filename/download header drift.

## Source Map

### Controller

- `app/Adapters/In/Http/Controllers/Admin/Reporting/InventoryStockValueReportPdfExportController.php`
  - memakai `GetInventoryStockValueReportDatasetHandler::handleSummaryOnly(...)`
  - build view data via `InventoryStockValueReportPdfViewDataBuilder`
  - render view `admin.reporting.inventory_stock_value.export_pdf`
  - generate PDF via Dompdf

### Summary-only Query

- `app/Adapters/Out/Reporting/InventoryStockValueSummaryDatabaseQuery.php`
  - snapshot aggregate dari active `products`
  - movement aggregate langsung dari `inventory_movements`

### PDF View

- `resources/views/admin/reporting/inventory_stock_value/export_pdf.blade.php`
  - summary-only owner-facing PDF
  - tidak render movement detail table
  - tidak render snapshot detail table

## Test Added

File:

- `tests/Feature/ReportingExports/InventoryStockValueReportPdfExportFeatureTest.php`

Test:

- `test_inventory_stock_value_pdf_export_handles_deleted_and_orphan_movements_without_snapshot_pollution`

Coverage:

- Admin bisa export PDF.
- Data fixture berisi:
  - active product
  - soft-deleted product
  - orphan/missing product movement
- Deleted/orphan movement tidak membuat PDF route crash.
- Response status 200.
- Response `Content-Type` adalah `application/pdf`.
- Download filename tetap benar.
- PDF content diawali `%PDF`.
- PDF content berisi `%%EOF`.

## Implementation

Production code tidak berubah.

Slice ini hanya menambah PDF route regression coverage.

## Proof

Owner reported test PASS:

```bash
php artisan test tests/Feature/ReportingExports/InventoryStockValueReportPdfExportFeatureTest.php --filter=deleted_and_orphan
```

Result:

```text
PASS
```

## Final Chain Status

Inventory stock value deleted/orphan reporting chain is closed:

- `0057` dataset movement visibility: resolved.
- `0058` Excel export visibility: resolved.
- `0059` summary-only parity: resolved.
- `0060` page summary visibility: resolved.
- `0061` PDF no-crash route: resolved.

## Risk Classification

### P0

None found.

### P1

Resolved.

PDF route no-crash untuk deleted/orphan movement sudah terkunci.

### P2

Resolved.

Owner-facing export path sudah punya regression coverage.

### P3

No cleanup/refactor.

