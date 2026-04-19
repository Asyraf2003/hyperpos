<?php

declare(strict_types=1);

namespace Tests\Feature\Inventory;

use App\Application\Inventory\UseCases\RebuildInventoryCostingProjectionHandler;
use App\Application\Shared\DTO\Result;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\Support\SeedsMinimalInventoryProductFixture;
use Tests\TestCase;

final class RebuildInventoryCostingProjectionFeatureTest extends TestCase
{
    use RefreshDatabase;
    use SeedsMinimalInventoryProductFixture;

    public function test_rebuild_inventory_costing_projection_handler_replaces_stale_projection_from_official_ledger(): void
    {
        $this->seedInventoryProduct('product-1', 'KB-001', 'Ban Luar', 'Federal', 100, 12000);
        $this->seedInventoryProduct('product-2', 'KB-002', 'Ban Dalam', 'Federal', 90, 15000);
        $this->seedInventoryProduct('product-legacy', 'KB-LEG', 'Produk Legacy', 'Legacy', 80, 9000);

        $this->seedInventoryMovement(
            'movement-1',
            'product-1',
            'stock_in',
            'supplier_receipt_line',
            'receipt-line-1',
            '2026-03-13',
            5,
            10000
        );

        $this->seedInventoryMovement(
            'movement-2',
            'product-1',
            'stock_in',
            'supplier_receipt_line',
            'receipt-line-2',
            '2026-03-14',
            5,
            12000
        );

        $this->seedInventoryMovement(
            'movement-3',
            'product-2',
            'stock_in',
            'supplier_receipt_line',
            'receipt-line-3',
            '2026-03-13',
            3,
            15000
        );

        DB::table('product_inventory_costing')->insert([
            [
                'product_id' => 'product-1',
                'avg_cost_rupiah' => 99999,
                'inventory_value_rupiah' => 99999,
            ],
            [
                'product_id' => 'product-legacy',
                'avg_cost_rupiah' => 5000,
                'inventory_value_rupiah' => 5000,
            ],
        ]);

        $handler = app(RebuildInventoryCostingProjectionHandler::class);

        $result = $handler->handle();

        $this->assertInstanceOf(Result::class, $result);

        $this->assertDatabaseHas('product_inventory_costing', [
            'product_id' => 'product-1',
            'avg_cost_rupiah' => 11000,
            'inventory_value_rupiah' => 110000,
        ]);

        $this->assertDatabaseHas('product_inventory_costing', [
            'product_id' => 'product-2',
            'avg_cost_rupiah' => 15000,
            'inventory_value_rupiah' => 45000,
        ]);

        $this->assertDatabaseMissing('product_inventory_costing', [
            'product_id' => 'product-legacy',
        ]);

        $this->assertDatabaseCount('product_inventory_costing', 2);
    }

    public function test_rebuild_inventory_costing_projection_handler_clears_stale_projection_when_ledger_is_empty(): void
    {
        $this->seedInventoryProduct('product-1', 'KB-001', 'Ban Luar', 'Federal', 100, 12000);
        $this->seedInventoryProduct('product-2', 'KB-002', 'Ban Dalam', 'Federal', 90, 15000);

        DB::table('product_inventory_costing')->insert([
            [
                'product_id' => 'product-1',
                'avg_cost_rupiah' => 10000,
                'inventory_value_rupiah' => 100000,
            ],
            [
                'product_id' => 'product-2',
                'avg_cost_rupiah' => 15000,
                'inventory_value_rupiah' => 45000,
            ],
        ]);

        $handler = app(RebuildInventoryCostingProjectionHandler::class);

        $result = $handler->handle();

        $this->assertInstanceOf(Result::class, $result);
        $this->assertDatabaseCount('inventory_movements', 0);
        $this->assertDatabaseCount('product_inventory_costing', 0);
    }
}
