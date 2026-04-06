<?php

declare(strict_types=1);

namespace Tests\Feature\Inventory;

use App\Application\Inventory\Services\ReverseNoteStoreStockInventoryOperation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\Support\SeedsMinimalInventoryProductFixture;
use Tests\TestCase;

final class ReverseNoteStoreStockInventoryOperationFeatureTest extends TestCase
{
    use RefreshDatabase;
    use SeedsMinimalInventoryProductFixture;

    public function test_it_reverses_all_store_stock_movements_for_a_note(): void
    {
        $this->seedInventoryProduct('product-1', 'KB-001', 'Ban Luar', 'Federal', 100, 12000);

        DB::table('notes')->insert([
            'id' => 'note-1',
            'customer_name' => 'Budi',
            'customer_phone' => '08123',
            'transaction_date' => '2026-03-15',
            'total_rupiah' => 40000,
        ]);

        DB::table('work_items')->insert([
            'id' => 'work-item-1',
            'note_id' => 'note-1',
            'line_no' => 1,
            'transaction_type' => 'store_stock_sale_only',
            'status' => 'open',
            'subtotal_rupiah' => 40000,
        ]);

        DB::table('work_item_store_stock_lines')->insert([
            'id' => 'ssl-1',
            'work_item_id' => 'work-item-1',
            'product_id' => 'product-1',
            'qty' => 2,
            'line_total_rupiah' => 40000,
        ]);

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
            'source_id' => 'ssl-1',
            'tanggal_mutasi' => '2026-03-15',
            'qty_delta' => -2,
            'unit_cost_rupiah' => 10000,
            'total_cost_rupiah' => -20000,
        ]);

        $service = app(ReverseNoteStoreStockInventoryOperation::class);
        $service->execute('note-1', new \DateTimeImmutable('2026-03-16'));

        $this->assertDatabaseHas('product_inventory', [
            'product_id' => 'product-1',
            'qty_on_hand' => 5,
        ]);

        $this->assertDatabaseHas('product_inventory_costing', [
            'product_id' => 'product-1',
            'avg_cost_rupiah' => 10000,
            'inventory_value_rupiah' => 50000,
        ]);

        $this->assertDatabaseHas('inventory_movements', [
            'product_id' => 'product-1',
            'movement_type' => 'stock_in',
            'source_type' => 'work_item_store_stock_line_reversal',
            'source_id' => 'ssl-1',
            'qty_delta' => 2,
            'unit_cost_rupiah' => 10000,
            'total_cost_rupiah' => 20000,
        ]);
    }
}
