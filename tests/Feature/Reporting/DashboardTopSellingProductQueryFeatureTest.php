<?php

declare(strict_types=1);

namespace Tests\Feature\Reporting;

use App\Adapters\Out\Reporting\Queries\DashboardTopSellingProductQuery;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

final class DashboardTopSellingProductQueryFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_excludes_fully_reversed_stock_lines_from_top_selling_products(): void
    {
        $this->seedProduct('product-refunded', 'REF-001', 'Refunded Supra', 15000);
        $this->seedProduct('product-kept', 'KEPT-001', 'Kept Vario', 17000);

        $this->seedNote('note-refunded', 'Refunded Customer', '2030-01-07', 30000);
        $this->seedNote('note-kept', 'Kept Customer', '2030-01-08', 17000);

        $this->seedWorkItem('work-refunded', 'note-refunded', 1, 'store_stock_sale_only', 30000);
        $this->seedWorkItem('work-kept', 'note-kept', 1, 'store_stock_sale_only', 17000);

        $this->seedStoreStockLine('line-refunded', 'work-refunded', 'product-refunded', 2, 30000);
        $this->seedStoreStockLine('line-kept', 'work-kept', 'product-kept', 1, 17000);

        $this->seedInventoryMovement('movement-refunded-out', 'product-refunded', 'work_item_store_stock_line', 'line-refunded', '2030-01-07', -2, -20000);
        $this->seedInventoryMovement('movement-refunded-reversal', 'product-refunded', 'work_item_store_stock_line_reversal', 'line-refunded', '2030-01-08', 2, 20000);
        $this->seedInventoryMovement('movement-kept-out', 'product-kept', 'work_item_store_stock_line', 'line-kept', '2030-01-08', -1, -10000);

        $rows = (new DashboardTopSellingProductQuery())->rows('2030-01-01', '2030-01-31', 5);

        $this->assertSame([
            [
                'product_id' => 'product-kept',
                'kode_barang' => 'KEPT-001',
                'nama_barang' => 'Kept Vario',
                'sold_qty' => 1,
                'gross_revenue_rupiah' => 17000,
            ],
        ], $rows);
    }

    public function test_it_nets_partially_reversed_stock_lines_before_ranking_products(): void
    {
        $this->seedProduct('product-partial', 'PART-001', 'Partial Beat', 10000);
        $this->seedProduct('product-kept', 'KEPT-001', 'Kept Vario', 17000);

        $this->seedNote('note-partial', 'Partial Customer', '2030-01-07', 40000);
        $this->seedNote('note-kept', 'Kept Customer', '2030-01-08', 17000);

        $this->seedWorkItem('work-partial', 'note-partial', 1, 'store_stock_sale_only', 40000);
        $this->seedWorkItem('work-kept', 'note-kept', 1, 'store_stock_sale_only', 17000);

        $this->seedStoreStockLine('line-partial', 'work-partial', 'product-partial', 4, 40000);
        $this->seedStoreStockLine('line-kept', 'work-kept', 'product-kept', 1, 17000);

        $this->seedInventoryMovement('movement-partial-out', 'product-partial', 'work_item_store_stock_line', 'line-partial', '2030-01-07', -4, -40000);
        $this->seedInventoryMovement('movement-partial-reversal', 'product-partial', 'work_item_store_stock_line_reversal', 'line-partial', '2030-01-08', 1, 10000);
        $this->seedInventoryMovement('movement-kept-out', 'product-kept', 'work_item_store_stock_line', 'line-kept', '2030-01-08', -1, -10000);

        $rows = (new DashboardTopSellingProductQuery())->rows('2030-01-01', '2030-01-31', 5);

        $this->assertSame([
            [
                'product_id' => 'product-partial',
                'kode_barang' => 'PART-001',
                'nama_barang' => 'Partial Beat',
                'sold_qty' => 3,
                'gross_revenue_rupiah' => 30000,
            ],
            [
                'product_id' => 'product-kept',
                'kode_barang' => 'KEPT-001',
                'nama_barang' => 'Kept Vario',
                'sold_qty' => 1,
                'gross_revenue_rupiah' => 17000,
            ],
        ], $rows);
    }

    private function seedProduct(string $id, string $kodeBarang, string $namaBarang, int $hargaJual): void
    {
        DB::table('products')->insert([
            'id' => $id,
            'kode_barang' => $kodeBarang,
            'nama_barang' => $namaBarang,
            'nama_barang_normalized' => mb_strtolower(trim($namaBarang)),
            'merek' => 'Federal',
            'merek_normalized' => 'federal',
            'ukuran' => 100,
            'harga_jual' => $hargaJual,
            'reorder_point_qty' => null,
            'critical_threshold_qty' => null,
            'deleted_at' => null,
            'deleted_by_actor_id' => null,
            'delete_reason' => null,
        ]);
    }

    private function seedNote(string $id, string $customerName, string $transactionDate, int $totalRupiah): void
    {
        DB::table('notes')->insert([
            'id' => $id,
            'customer_name' => $customerName,
            'transaction_date' => $transactionDate,
            'note_state' => 'closed',
            'total_rupiah' => $totalRupiah,
        ]);
    }

    private function seedWorkItem(
        string $id,
        string $noteId,
        int $lineNo,
        string $transactionType,
        int $subtotalRupiah
    ): void {
        DB::table('work_items')->insert([
            'id' => $id,
            'note_id' => $noteId,
            'line_no' => $lineNo,
            'transaction_type' => $transactionType,
            'status' => 'closed',
            'subtotal_rupiah' => $subtotalRupiah,
        ]);
    }

    private function seedStoreStockLine(
        string $id,
        string $workItemId,
        string $productId,
        int $qty,
        int $lineTotalRupiah
    ): void {
        DB::table('work_item_store_stock_lines')->insert([
            'id' => $id,
            'work_item_id' => $workItemId,
            'product_id' => $productId,
            'qty' => $qty,
            'line_total_rupiah' => $lineTotalRupiah,
        ]);
    }

    private function seedInventoryMovement(
        string $id,
        string $productId,
        string $sourceType,
        string $sourceId,
        string $tanggalMutasi,
        int $qtyDelta,
        int $totalCostRupiah
    ): void {
        DB::table('inventory_movements')->insert([
            'id' => $id,
            'product_id' => $productId,
            'movement_type' => $qtyDelta > 0 ? 'stock_in' : 'stock_out',
            'source_type' => $sourceType,
            'source_id' => $sourceId,
            'tanggal_mutasi' => $tanggalMutasi,
            'qty_delta' => $qtyDelta,
            'unit_cost_rupiah' => 10000,
            'total_cost_rupiah' => $totalCostRupiah,
        ]);
    }
}
