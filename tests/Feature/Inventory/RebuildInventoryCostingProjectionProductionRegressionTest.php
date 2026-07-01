<?php

declare(strict_types=1);

namespace Tests\Feature\Inventory;

use App\Application\Inventory\UseCases\RebuildInventoryCostingProjectionHandler;
use App\Application\Shared\DTO\Result;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\Support\SeedsMinimalInventoryProductFixture;
use Tests\TestCase;

final class RebuildInventoryCostingProjectionProductionRegressionTest extends TestCase
{
    use RefreshDatabase;
    use SeedsMinimalInventoryProductFixture;

    public function test_rebuild_costing_projection_replaces_single_stale_supplier_revision_projection_with_ledger_total(): void
    {
        $this->seedInventoryProduct(
            'product-production-regression',
            'KB-REG',
            'Produk Regression',
            'Regression',
            100,
            120000
        );

        DB::table('product_inventory_costing')->insert([
            'product_id' => 'product-production-regression',
            'avg_cost_rupiah' => 121061,
            'inventory_value_rupiah' => 1210613,
        ]);

        DB::table('inventory_movements')->insert([
            [
                'id' => 'movement-revision-old-line-out',
                'product_id' => 'product-production-regression',
                'movement_type' => 'stock_out',
                'source_type' => 'supplier_invoice_revision_delta_line',
                'source_id' => 'supplier-line-old',
                'tanggal_mutasi' => '2026-05-14',
                'qty_delta' => -5,
                'unit_cost_rupiah' => 114750,
                'total_cost_rupiah' => -573750,
            ],
            [
                'id' => 'movement-revision-old-line-in',
                'product_id' => 'product-production-regression',
                'movement_type' => 'stock_in',
                'source_type' => 'supplier_invoice_revision_delta_line',
                'source_id' => 'supplier-line-old',
                'tanggal_mutasi' => '2026-05-14',
                'qty_delta' => 5,
                'unit_cost_rupiah' => 114750,
                'total_cost_rupiah' => 573750,
            ],
            [
                'id' => 'movement-revision-new-line-in',
                'product_id' => 'product-production-regression',
                'movement_type' => 'stock_in',
                'source_type' => 'supplier_invoice_revision_delta_line',
                'source_id' => 'supplier-line-new',
                'tanggal_mutasi' => '2026-05-14',
                'qty_delta' => 5,
                'unit_cost_rupiah' => 114750,
                'total_cost_rupiah' => 573750,
            ],
            [
                'id' => 'movement-revision-cost-revaluation',
                'product_id' => 'product-production-regression',
                'movement_type' => 'cost_revaluation',
                'source_type' => 'supplier_invoice_cost_revaluation',
                'source_id' => 'supplier-line-new',
                'tanggal_mutasi' => '2026-05-14',
                'qty_delta' => 0,
                'unit_cost_rupiah' => 0,
                'total_cost_rupiah' => 63113,
            ],
        ]);

        $result = app(RebuildInventoryCostingProjectionHandler::class)->handle();

        $this->assertInstanceOf(Result::class, $result);

        $this->assertDatabaseHas('product_inventory_costing', [
            'product_id' => 'product-production-regression',
            'avg_cost_rupiah' => 127372,
            'inventory_value_rupiah' => 636863,
        ]);

        $this->assertDatabaseCount('product_inventory_costing', 1);

        $ledgerDiff = DB::table('product_inventory_costing as pic')
            ->joinSub(
                DB::table('inventory_movements')
                    ->selectRaw('product_id, SUM(qty_delta) AS ledger_qty, SUM(total_cost_rupiah) AS ledger_value')
                    ->groupBy('product_id'),
                'ledger',
                'ledger.product_id',
                '=',
                'pic.product_id'
            )
            ->where('pic.product_id', 'product-production-regression')
            ->selectRaw('pic.inventory_value_rupiah - ledger.ledger_value AS value_diff')
            ->value('value_diff');

        $this->assertSame(0, (int) $ledgerDiff);
    }
}
