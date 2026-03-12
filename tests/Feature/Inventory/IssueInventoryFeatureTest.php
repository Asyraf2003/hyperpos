<?php

declare(strict_types=1);

namespace Tests\Feature\Inventory;

use App\Application\Inventory\UseCases\IssueInventoryHandler;
use App\Application\Shared\DTO\Result;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

final class IssueInventoryFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_issue_inventory_handler_creates_stock_out_movement_and_updates_projections(): void
    {
        DB::table('product_inventory')->insert([
            'product_id' => 'product-1',
            'qty_on_hand' => 7,
        ]);

        DB::table('product_inventory_costing')->insert([
            'product_id' => 'product-1',
            'avg_cost_rupiah' => 10000,
            'inventory_value_rupiah' => 70000,
        ]);

        $handler = app(IssueInventoryHandler::class);

        $result = $handler->handle(
            'product-1',
            3,
            '2026-03-15',
            'stock_adjustment',
            'adjustment-1',
        );

        $this->assertInstanceOf(Result::class, $result);
        $this->assertTrue($result->isSuccess());

        $this->assertDatabaseHas('inventory_movements', [
            'product_id' => 'product-1',
            'movement_type' => 'stock_out',
            'source_type' => 'stock_adjustment',
            'source_id' => 'adjustment-1',
            'tanggal_mutasi' => '2026-03-15',
            'qty_delta' => -3,
            'unit_cost_rupiah' => 10000,
            'total_cost_rupiah' => -30000,
        ]);

        $this->assertDatabaseHas('product_inventory', [
            'product_id' => 'product-1',
            'qty_on_hand' => 4,
        ]);

        $this->assertDatabaseHas('product_inventory_costing', [
            'product_id' => 'product-1',
            'avg_cost_rupiah' => 10000,
            'inventory_value_rupiah' => 40000,
        ]);
    }

    public function test_issue_inventory_handler_rejects_when_stock_is_insufficient(): void
    {
        DB::table('product_inventory')->insert([
            'product_id' => 'product-1',
            'qty_on_hand' => 2,
        ]);

        DB::table('product_inventory_costing')->insert([
            'product_id' => 'product-1',
            'avg_cost_rupiah' => 10000,
            'inventory_value_rupiah' => 20000,
        ]);

        $handler = app(IssueInventoryHandler::class);

        $result = $handler->handle(
            'product-1',
            3,
            '2026-03-15',
            'stock_adjustment',
            'adjustment-1',
        );

        $this->assertInstanceOf(Result::class, $result);
        $this->assertTrue($result->isFailure());

        $this->assertDatabaseCount('inventory_movements', 0);

        $this->assertDatabaseHas('product_inventory', [
            'product_id' => 'product-1',
            'qty_on_hand' => 2,
        ]);

        $this->assertDatabaseHas('product_inventory_costing', [
            'product_id' => 'product-1',
            'avg_cost_rupiah' => 10000,
            'inventory_value_rupiah' => 20000,
        ]);
    }
}
