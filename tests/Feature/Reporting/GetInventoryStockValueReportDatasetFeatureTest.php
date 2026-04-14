<?php

declare(strict_types=1);

namespace Tests\Feature\Reporting;

use App\Application\Reporting\UseCases\GetInventoryStockValueReportDatasetHandler;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

final class GetInventoryStockValueReportDatasetFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_inventory_stock_value_report_dataset_returns_hybrid_snapshot_and_period_movement_exactly(): void
    {
        $this->seedProduct('product-1', 'KB-001', 'Supra', 'Federal', 100, 15000);
        $this->seedProduct('product-2', 'KB-002', 'Vario', 'Federal', 90, 17000);
        $this->seedProduct('product-3', 'KB-003', 'Beat', 'Federal', 80, 16000);
        $this->seedProduct('product-4', 'KB-004', 'Scoopy', 'Federal', 85, 18000);

        DB::table('inventory_movements')->insert([
            [
                'id' => 'm1',
                'product_id' => 'product-1',
                'movement_type' => 'stock_in',
                'source_type' => 'supplier_receipt_line',
                'source_id' => 'sr1',
                'tanggal_mutasi' => '2030-01-07',
                'qty_delta' => 10,
                'unit_cost_rupiah' => 10000,
                'total_cost_rupiah' => 100000,
            ],
            [
                'id' => 'm2',
                'product_id' => 'product-1',
                'movement_type' => 'stock_out',
                'source_type' => 'work_item_store_stock_line',
                'source_id' => 'sto1',
                'tanggal_mutasi' => '2030-01-09',
                'qty_delta' => -4,
                'unit_cost_rupiah' => 10000,
                'total_cost_rupiah' => -40000,
            ],
            [
                'id' => 'm3',
                'product_id' => 'product-2',
                'movement_type' => 'stock_in',
                'source_type' => 'supplier_receipt_line',
                'source_id' => 'sr2',
                'tanggal_mutasi' => '2030-01-09',
                'qty_delta' => 3,
                'unit_cost_rupiah' => 12000,
                'total_cost_rupiah' => 36000,
            ],
            [
                'id' => 'm4',
                'product_id' => 'product-3',
                'movement_type' => 'stock_in',
                'source_type' => 'supplier_receipt_line',
                'source_id' => 'sr3',
                'tanggal_mutasi' => '2030-02-01',
                'qty_delta' => 5,
                'unit_cost_rupiah' => 9000,
                'total_cost_rupiah' => 45000,
            ],
        ]);

        DB::table('product_inventory')->insert([
            ['product_id' => 'product-1', 'qty_on_hand' => 6],
            ['product_id' => 'product-2', 'qty_on_hand' => 3],
            ['product_id' => 'product-3', 'qty_on_hand' => 5],
            ['product_id' => 'product-4', 'qty_on_hand' => 7],
        ]);

        DB::table('product_inventory_costing')->insert([
            ['product_id' => 'product-1', 'avg_cost_rupiah' => 10000, 'inventory_value_rupiah' => 60000],
            ['product_id' => 'product-2', 'avg_cost_rupiah' => 12000, 'inventory_value_rupiah' => 36000],
            ['product_id' => 'product-3', 'avg_cost_rupiah' => 9000, 'inventory_value_rupiah' => 45000],
            ['product_id' => 'product-4', 'avg_cost_rupiah' => 10000, 'inventory_value_rupiah' => 70000],
        ]);

        $result = app(GetInventoryStockValueReportDatasetHandler::class)
            ->handle('2030-01-01', '2030-01-31');

        $this->assertTrue($result->isSuccess());

        $data = $result->data();
        $this->assertIsArray($data);

        $snapshotRows = $data['snapshot_rows'] ?? null;
        $movementRows = $data['movement_rows'] ?? null;
        $summary = $data['summary'] ?? null;

        $this->assertIsArray($snapshotRows);
        $this->assertIsArray($movementRows);
        $this->assertIsArray($summary);

        $this->assertSame([
            [
                'product_id' => 'product-1',
                'kode_barang' => 'KB-001',
                'nama_barang' => 'Supra',
                'merek' => 'Federal',
                'ukuran' => 100,
                'current_qty_on_hand' => 6,
                'current_avg_cost_rupiah' => 10000,
                'current_inventory_value_rupiah' => 60000,
            ],
            [
                'product_id' => 'product-2',
                'kode_barang' => 'KB-002',
                'nama_barang' => 'Vario',
                'merek' => 'Federal',
                'ukuran' => 90,
                'current_qty_on_hand' => 3,
                'current_avg_cost_rupiah' => 12000,
                'current_inventory_value_rupiah' => 36000,
            ],
            [
                'product_id' => 'product-3',
                'kode_barang' => 'KB-003',
                'nama_barang' => 'Beat',
                'merek' => 'Federal',
                'ukuran' => 80,
                'current_qty_on_hand' => 5,
                'current_avg_cost_rupiah' => 9000,
                'current_inventory_value_rupiah' => 45000,
            ],
            [
                'product_id' => 'product-4',
                'kode_barang' => 'KB-004',
                'nama_barang' => 'Scoopy',
                'merek' => 'Federal',
                'ukuran' => 85,
                'current_qty_on_hand' => 7,
                'current_avg_cost_rupiah' => 10000,
                'current_inventory_value_rupiah' => 70000,
            ],
        ], $snapshotRows);

        $this->assertSame([
            [
                'product_id' => 'product-1',
                'qty_in' => 10,
                'qty_out' => 4,
                'net_qty_delta' => 6,
                'total_in_cost_rupiah' => 100000,
                'total_out_cost_rupiah' => 40000,
                'net_cost_delta_rupiah' => 60000,
                'current_qty_on_hand' => 6,
                'current_avg_cost_rupiah' => 10000,
                'current_inventory_value_rupiah' => 60000,
            ],
            [
                'product_id' => 'product-2',
                'qty_in' => 3,
                'qty_out' => 0,
                'net_qty_delta' => 3,
                'total_in_cost_rupiah' => 36000,
                'total_out_cost_rupiah' => 0,
                'net_cost_delta_rupiah' => 36000,
                'current_qty_on_hand' => 3,
                'current_avg_cost_rupiah' => 12000,
                'current_inventory_value_rupiah' => 36000,
            ],
        ], $movementRows);

        $this->assertSame([
            'snapshot_product_rows' => 4,
            'movement_product_rows' => 2,
            'total_qty_on_hand' => 21,
            'total_inventory_value_rupiah' => 211000,
            'period_qty_in' => 13,
            'period_qty_out' => 4,
            'period_net_qty_delta' => 9,
            'period_total_in_cost_rupiah' => 136000,
            'period_total_out_cost_rupiah' => 40000,
            'period_net_cost_delta_rupiah' => 96000,
        ], $summary);

        $this->assertSame(
            $summary['total_qty_on_hand'],
            array_sum(array_column($snapshotRows, 'current_qty_on_hand'))
        );

        $this->assertSame(
            $summary['total_inventory_value_rupiah'],
            array_sum(array_column($snapshotRows, 'current_inventory_value_rupiah'))
        );

        $this->assertSame(
            $summary['period_net_cost_delta_rupiah'],
            array_sum(array_column($movementRows, 'net_cost_delta_rupiah'))
        );
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
