# 0060 - Inventory Stock Value Page Summary Deleted Orphan Visibility

## Status

Resolved.

## Context

Slice ini melanjutkan `0057`, `0058`, dan `0059` dengan mengunci HTTP page laporan stok dan nilai persediaan.

`0059` sudah membuktikan full dataset summary sama dengan summary-only aggregate ketika ada movement untuk produk soft-deleted dan orphan/missing product.

`0060` memastikan controller + Blade page benar-benar menerima dan menampilkan summary yang aman.

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

Page report memakai summary-only path.

Tanpa HTTP page test, use case bisa benar tetapi controller/view bisa salah wiring, summary kosong, atau angka owner-facing drift.

Risiko utama:

- `movement_product_rows` tidak menghitung deleted/orphan movement.
- `snapshot_product_rows` ikut tercemar deleted/orphan product.
- page menampilkan angka period movement yang tidak sama dengan summary-only aggregate.
- owner membaca page utama berbeda dari dataset/export.

## Source Map

### Controller

- `app/Adapters/In/Http/Controllers/Admin/Reporting/InventoryStockValueReportPageController.php`
  - memakai `GetInventoryStockValueReportDatasetHandler::handleSummaryOnly(...)`
  - mengirim `summary` ke view `admin.reporting.inventory_stock_value.index`

### View

- `resources/views/admin/reporting/inventory_stock_value/index.blade.php`
  - menampilkan:
    - `Produk Snapshot`
    - `Produk Bermutasi`
    - `Qty Tersedia`
    - `Nilai Persediaan`
    - `Qty Masuk Pembelian`
    - `Selisih Qty Periode`
    - `Selisih Nilai Pokok Periode`
    - diagnostic inventory value fields

### Query

- `app/Adapters/Out/Reporting/InventoryStockValueSummaryDatabaseQuery.php`
  - snapshot aggregate:
    - source utama `products`
    - `whereNull('products.deleted_at')`
    - requires `product_inventory` atau `product_inventory_costing`
  - movement aggregate:
    - source utama `inventory_movements`
    - aggregate langsung dari movement periode

## Test Added

File:

- `tests/Feature/Reporting/InventoryStockValueReportPageSummaryVisibilityFeatureTest.php`

Test:

- `test_inventory_stock_value_page_summary_counts_deleted_and_orphan_movements_without_snapshot_pollution`

Coverage:

- Admin bisa membuka page report.
- View yang dipakai benar: `admin.reporting.inventory_stock_value.index`.
- Summary page:
  - `snapshot_product_rows` tetap 1.
  - `movement_product_rows` tetap 3.
  - `total_qty_on_hand` tetap dari active snapshot.
  - `total_inventory_value_rupiah` tetap dari active snapshot.
  - `period_supply_in_qty` menghitung active + deleted + orphan.
  - `period_net_qty_delta` menghitung active + deleted + orphan.
  - `period_net_cost_delta_rupiah` menghitung active + deleted + orphan.
- Page menampilkan label summary owner-facing:
  - `Produk Snapshot`
  - `Produk Bermutasi`
  - `Qty Masuk Pembelian`
  - `Selisih Nilai Pokok Periode`
- Page menampilkan nilai rupiah utama dan period movement yang benar.

## Implementation

Production code tidak berubah.

Slice ini hanya menambah HTTP page regression coverage.

## Proof

Owner reported test PASS:

```bash
php artisan test tests/Feature/Reporting/InventoryStockValueReportPageSummaryVisibilityFeatureTest.php
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

Page summary sudah terbukti menghitung deleted/orphan movement tanpa mencemari snapshot.

### P2

Resolved.

Owner-facing page summary sudah terkunci terhadap drift dari summary-only path.

### P3

No cleanup/refactor.

