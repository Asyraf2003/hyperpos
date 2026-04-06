<?php

declare(strict_types=1);

namespace Tests\Feature\Inventory;

use App\Application\Inventory\Services\ReverseIssuedInventoryOperation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\Support\SeedsMinimalInventoryProductFixture;
use Tests\TestCase;

final class ReverseIssuedInventoryOperationFeatureTest extends TestCase
{
    use RefreshDatabase;
    use SeedsMinimalInventoryProductFixture;

    public function test_it_reverses_previous_stock_out_movements_for_a_source(): void
    {
        $this->seedInventoryProduct('product-1', 'KB-001', 'Ban Luar', 'Federal', 100, 12000);

        DB::table('product_inventory')->insert([
            'product_id' => 'product-1',
            'qty_on_hand' => 3,
        ]);

        DB::table('product_inventory_costing')->insert([
            'product_id' => 'product-1',
            'avg_cost_rupiah' => 10000,
            'inventory_value_rupiah' => 30000,
        ]);

        DB::table('inventory_movements')->insert([
            'id' => 'mv-1',
            'product_id' => 'product-1',
            'movement_type' => 'stock_out',
            'source_type' => 'work_item_store_stock_line',
            'source_id' => 'line-1',
            'tanggal_mutasi' => '2026-03-15',
            'qty_delta' => -2,
            'unit_cost_rupiah' => 10000,
            'total_cost_rupiah' => 20000,
        ]);

        $service = app(ReverseIssuedInventoryOperation::class);
        $reversed = $service->execute(
            'work_item_store_stock_line',
            'line-1',
            new \DateTimeImmutable('2026-03-16'),
            'work_item_store_stock_line_reversal'
        );

        $this->assertCount(1, $reversed);
        $this->assertDatabaseHas('product_inventory', ['product_id' => 'product-1', 'qty_on_hand' => 5]);
        $this->assertDatabaseHas('product_inventory_costing', [
            'product_id' => 'product-1',
            'avg_cost_rupiah' => 10000,
            'inventory_value_rupiah' => 50000,
        ]);
        $this->assertDatabaseHas('inventory_movements', [
            'product_id' => 'product-1',
            'movement_type' => 'stock_in',
            'source_type' => 'work_item_store_stock_line_reversal',
            'source_id' => 'line-1',
            'qty_delta' => 2,
            'unit_cost_rupiah' => 10000,
            'total_cost_rupiah' => 20000,
        ]);
    }
}
