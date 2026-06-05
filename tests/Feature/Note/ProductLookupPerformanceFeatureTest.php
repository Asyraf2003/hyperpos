<?php

declare(strict_types=1);

namespace Tests\Feature\Note;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

final class ProductLookupPerformanceFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_cashier_product_lookup_caps_available_stock_rows(): void
    {
        $this->loginAsKasir();

        for ($index = 1; $index <= 25; $index++) {
            $this->seedProduct($index, qtyOnHand: 5);
        }

        $response = $this->getJson(route('cashier.notes.products.lookup', ['q' => 'Ban']));

        $response->assertOk();
        $response->assertJsonPath('success', true);
        $response->assertJsonCount(20, 'data.rows');
        $response->assertJsonPath('data.rows.0.id', 'product-001');
        $response->assertJsonPath('data.rows.19.id', 'product-020');
    }

    public function test_cashier_product_lookup_uses_bounded_query_count_for_large_catalog(): void
    {
        $this->loginAsKasir();

        for ($index = 1; $index <= 30; $index++) {
            $this->seedProduct($index, qtyOnHand: 5);
        }

        DB::flushQueryLog();
        DB::enableQueryLog();

        $response = $this->getJson(route('cashier.notes.products.lookup', ['q' => 'Ban']));

        $queryCount = count(DB::getQueryLog());
        DB::disableQueryLog();

        $response->assertOk();
        $response->assertJsonCount(20, 'data.rows');
        $this->assertLessThanOrEqual(8, $queryCount);
    }

    private function seedProduct(int $index, int $qtyOnHand): void
    {
        $id = sprintf('product-%03d', $index);
        $name = sprintf('Ban Lookup %03d', $index);

        DB::table('products')->insert([
            'id' => $id,
            'kode_barang' => sprintf('KB-%03d', $index),
            'nama_barang' => $name,
            'nama_barang_normalized' => mb_strtolower($name),
            'merek' => 'Federal',
            'merek_normalized' => 'federal',
            'ukuran' => 80,
            'harga_jual' => 15000,
        ]);

        DB::table('product_inventory')->insert([
            'product_id' => $id,
            'qty_on_hand' => $qtyOnHand,
        ]);
    }
}
