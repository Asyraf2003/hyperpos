<?php

declare(strict_types=1);

namespace Tests\Feature\Reporting;

use App\Application\Reporting\UseCases\GetInventoryMovementSummaryHandler;
use App\Application\Shared\DTO\Result;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

final class GetInventoryMovementSummaryFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_get_inventory_movement_summary_handler_returns_product_level_rows_and_passes_reconciliation(): void
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
                'tanggal_mutasi' => '2026-03-15',
                'qty_delta' => 10,
                'unit_cost_rupiah' => 10000,
                'total_cost_rupiah' => 100000,
            ],
            [
                'id' => 'm2',
                'product_id' => 'product-1',
                'movement_type' => 'stock_out',
                'source_type' => 'note',
                'source_id' => 'n1',
                'tanggal_mutasi' => '2026-03-16',
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
                'tanggal_mutasi' => '2026-03-16',
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
                'tanggal_mutasi' => '2026-03-18',
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

        $result = app(GetInventoryMovementSummaryHandler::class)
            ->handle('2026-03-15', '2026-03-16');

        $this->assertInstanceOf(Result::class, $result);
        $this->assertTrue($result->isSuccess());

        $data = $result->data();

        $this->assertIsArray($data);
        $this->assertArrayHasKey('rows', $data);

        $this->assertSame([
            [
                'product_id' => 'product-1',
                'kode_barang' => 'KB-001',
                'nama_barang' => 'Supra',
                'supply_in_qty' => 10,
                'sale_out_qty' => 4,
                'refund_reversal_qty' => 0,
                'revision_correction_qty' => 0,
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
                'kode_barang' => 'KB-002',
                'nama_barang' => 'Vario',
                'supply_in_qty' => 3,
                'sale_out_qty' => 0,
                'refund_reversal_qty' => 0,
                'revision_correction_qty' => 0,
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
        ], $data['rows']);
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
