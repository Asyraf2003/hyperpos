<?php

declare(strict_types=1);

namespace Tests\Feature\Reporting;

use App\Application\Reporting\UseCases\GetInventoryStockValueReportDatasetHandler;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

final class InventoryMovementBucketSplitFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_inventory_stock_value_report_splits_period_movement_buckets_by_source_type(): void
    {
        $this->seedProduct('product-1', 'KB-001', 'Dad', 'Ada', 100, 122000);

        DB::table('inventory_movements')->insert([
            [
                'id' => 'movement-supply-in',
                'product_id' => 'product-1',
                'movement_type' => 'stock_in',
                'source_type' => 'supplier_receipt_line',
                'source_id' => 'receipt-line-1',
                'tanggal_mutasi' => '2030-01-15',
                'qty_delta' => 8,
                'unit_cost_rupiah' => 10000,
                'total_cost_rupiah' => 80000,
            ],
            [
                'id' => 'movement-sale-out-1',
                'product_id' => 'product-1',
                'movement_type' => 'stock_out',
                'source_type' => 'work_item_store_stock_line',
                'source_id' => 'stock-line-1',
                'tanggal_mutasi' => '2030-01-15',
                'qty_delta' => -4,
                'unit_cost_rupiah' => 10000,
                'total_cost_rupiah' => -40000,
            ],
            [
                'id' => 'movement-sale-out-2',
                'product_id' => 'product-1',
                'movement_type' => 'stock_out',
                'source_type' => 'work_item_store_stock_line',
                'source_id' => 'stock-line-2',
                'tanggal_mutasi' => '2030-01-15',
                'qty_delta' => -5,
                'unit_cost_rupiah' => 10000,
                'total_cost_rupiah' => -50000,
            ],
            [
                'id' => 'movement-refund-reversal',
                'product_id' => 'product-1',
                'movement_type' => 'stock_in',
                'source_type' => 'work_item_store_stock_line_reversal',
                'source_id' => 'stock-line-1',
                'tanggal_mutasi' => '2030-01-15',
                'qty_delta' => 9,
                'unit_cost_rupiah' => 10000,
                'total_cost_rupiah' => 90000,
            ],
            [
                'id' => 'movement-revision',
                'product_id' => 'product-1',
                'movement_type' => 'stock_in',
                'source_type' => 'transaction_workspace_updated',
                'source_id' => 'revision-1',
                'tanggal_mutasi' => '2030-01-15',
                'qty_delta' => 5,
                'unit_cost_rupiah' => 10000,
                'total_cost_rupiah' => 50000,
            ],
        ]);

        DB::table('product_inventory')->insert([
            'product_id' => 'product-1',
            'qty_on_hand' => 13,
        ]);

        DB::table('product_inventory_costing')->insert([
            'product_id' => 'product-1',
            'avg_cost_rupiah' => 10000,
            'inventory_value_rupiah' => 130000,
        ]);

        $result = app(GetInventoryStockValueReportDatasetHandler::class)
            ->handle('2030-01-01', '2030-01-31');

        $this->assertTrue($result->isSuccess());

        $data = $result->data();
        $this->assertIsArray($data);

        $summary = $data['summary'] ?? null;
        $movementRows = $data['movement_rows'] ?? null;

        $this->assertIsArray($summary);
        $this->assertIsArray($movementRows);
        $this->assertCount(1, $movementRows);

        $this->assertSame(8, $summary['period_supply_in_qty']);
        $this->assertSame(9, $summary['period_sale_out_qty']);
        $this->assertSame(9, $summary['period_refund_reversal_qty']);
        $this->assertSame(5, $summary['period_revision_correction_qty']);
        $this->assertSame(13, $summary['period_net_qty_delta']);

        $this->assertSame(8, $movementRows[0]['supply_in_qty']);
        $this->assertSame(9, $movementRows[0]['sale_out_qty']);
        $this->assertSame(9, $movementRows[0]['refund_reversal_qty']);
        $this->assertSame(5, $movementRows[0]['revision_correction_qty']);
        $this->assertSame(13, $movementRows[0]['net_qty_delta']);
    }

    private function seedProduct(
        string $id,
        ?string $kodeBarang,
        string $namaBarang,
        string $merek,
        ?int $ukuran,
        int $hargaJual,
    ): void {
        DB::table('products')->insert([
            'id' => $id,
            'kode_barang' => $kodeBarang,
            'nama_barang' => $namaBarang,
            'merek' => $merek,
            'ukuran' => $ukuran,
            'harga_jual' => $hargaJual,
        ]);
    }
}
