<?php

declare(strict_types=1);

namespace Tests\Feature\Reporting;

use App\Application\Reporting\UseCases\GetInventoryMovementSummaryHandler;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

final class InventoryMovementSummaryHardeningFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_inventory_movement_summary_period_parity_matches_expected_totals(): void
    {
        $this->seedProduct('product-1', 'KB-001', 'Supra', 'Federal', 100, 15000);
        $this->seedProduct('product-2', 'KB-002', 'Vario', 'Federal', 90, 17000);
        $this->seedProduct('product-3', 'KB-003', 'Beat', 'Federal', 80, 16000);

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
        ]);

        DB::table('product_inventory_costing')->insert([
            ['product_id' => 'product-1', 'avg_cost_rupiah' => 10000, 'inventory_value_rupiah' => 60000],
            ['product_id' => 'product-2', 'avg_cost_rupiah' => 12000, 'inventory_value_rupiah' => 36000],
            ['product_id' => 'product-3', 'avg_cost_rupiah' => 9000, 'inventory_value_rupiah' => 45000],
        ]);

        $daily = $this->summaryTotals('2030-01-07', '2030-01-07');
        $weekly = $this->summaryTotals('2030-01-07', '2030-01-13');
        $monthly = $this->summaryTotals('2030-01-01', '2030-01-31');
        $custom = $this->summaryTotals('2030-01-01', '2030-01-31');

        $this->assertSame([
            'total_rows' => 1,
            'qty_in' => 10,
            'qty_out' => 0,
            'net_qty_delta' => 10,
            'total_in_cost_rupiah' => 100000,
            'total_out_cost_rupiah' => 0,
            'net_cost_delta_rupiah' => 100000,
            'current_qty_on_hand' => 6,
            'current_inventory_value_rupiah' => 60000,
        ], $daily);

        $this->assertSame([
            'total_rows' => 2,
            'qty_in' => 13,
            'qty_out' => 4,
            'net_qty_delta' => 9,
            'total_in_cost_rupiah' => 136000,
            'total_out_cost_rupiah' => 40000,
            'net_cost_delta_rupiah' => 96000,
            'current_qty_on_hand' => 9,
            'current_inventory_value_rupiah' => 96000,
        ], $weekly);

        $this->assertSame([
            'total_rows' => 2,
            'qty_in' => 13,
            'qty_out' => 4,
            'net_qty_delta' => 9,
            'total_in_cost_rupiah' => 136000,
            'total_out_cost_rupiah' => 40000,
            'net_cost_delta_rupiah' => 96000,
            'current_qty_on_hand' => 9,
            'current_inventory_value_rupiah' => 96000,
        ], $monthly);

        $this->assertSame($monthly, $custom);
    }

    public function test_inventory_movement_summary_uses_current_projection_snapshot_not_recomputed_period_balance(): void
    {
        $this->seedProduct('product-1', 'KB-001', 'Supra', 'Federal', 100, 15000);

        DB::table('inventory_movements')->insert([
            [
                'id' => 'm1',
                'product_id' => 'product-1',
                'movement_type' => 'stock_in',
                'source_type' => 'supplier_receipt_line',
                'source_id' => 'sr1',
                'tanggal_mutasi' => '2030-02-10',
                'qty_delta' => 10,
                'unit_cost_rupiah' => 10000,
                'total_cost_rupiah' => 100000,
            ],
        ]);

        DB::table('product_inventory')->insert([
            ['product_id' => 'product-1', 'qty_on_hand' => 99],
        ]);

        DB::table('product_inventory_costing')->insert([
            ['product_id' => 'product-1', 'avg_cost_rupiah' => 12345, 'inventory_value_rupiah' => 1222155],
        ]);

        $result = app(GetInventoryMovementSummaryHandler::class)
            ->handle('2030-02-10', '2030-02-10');

        $this->assertTrue($result->isSuccess());

        $data = $result->data();
        $this->assertIsArray($data);

        $rows = $data['rows'] ?? [];
        $this->assertIsArray($rows);
        $this->assertCount(1, $rows);

        $this->assertSame([
            'product_id' => 'product-1',
            'kode_barang' => 'KB-001',
            'nama_barang' => 'Supra',
            'supply_in_qty' => 10,
            'sale_out_qty' => 0,
            'refund_reversal_qty' => 0,
            'revision_correction_qty' => 0,
            'qty_in' => 10,
            'qty_out' => 0,
            'net_qty_delta' => 10,
            'total_in_cost_rupiah' => 100000,
            'total_out_cost_rupiah' => 0,
            'net_cost_delta_rupiah' => 100000,
            'current_qty_on_hand' => 99,
            'current_avg_cost_rupiah' => 12345,
            'current_inventory_value_rupiah' => 1222155,
        ], $rows[0]);
    }

    private function summaryTotals(string $from, string $to): array
    {
        $result = app(GetInventoryMovementSummaryHandler::class)->handle($from, $to);

        $this->assertTrue($result->isSuccess());

        $data = $result->data();
        $this->assertIsArray($data);

        $rows = $data['rows'] ?? [];
        $this->assertIsArray($rows);

        return [
            'total_rows' => count($rows),
            'qty_in' => array_sum(array_column($rows, 'qty_in')),
            'qty_out' => array_sum(array_column($rows, 'qty_out')),
            'net_qty_delta' => array_sum(array_column($rows, 'net_qty_delta')),
            'total_in_cost_rupiah' => array_sum(array_column($rows, 'total_in_cost_rupiah')),
            'total_out_cost_rupiah' => array_sum(array_column($rows, 'total_out_cost_rupiah')),
            'net_cost_delta_rupiah' => array_sum(array_column($rows, 'net_cost_delta_rupiah')),
            'current_qty_on_hand' => array_sum(array_column($rows, 'current_qty_on_hand')),
            'current_inventory_value_rupiah' => array_sum(array_column($rows, 'current_inventory_value_rupiah')),
        ];
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
