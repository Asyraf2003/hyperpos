<?php

declare(strict_types=1);

namespace Tests\Feature\ProductCatalog;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

final class UpdateProductThresholdFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_update_product_endpoint_rejects_when_only_critical_threshold_is_filled(): void
    {
        $this->loginAsKasir();

        DB::table('products')->insert([
            'id' => 'product-1',
            'kode_barang' => 'KB-020',
            'nama_barang' => 'Supra',
            'merek' => 'Federal',
            'ukuran' => 100,
            'harga_jual' => 15000,
        ]);

        $response = $this->postJson('/product-catalog/products/product-1/update', [
            'kode_barang' => 'KB-020',
            'nama_barang' => 'Supra',
            'merek' => 'Federal',
            'ukuran' => 100,
            'harga_jual' => 15000,
            'reorder_point_qty' => null,
            'critical_threshold_qty' => 3,
        ]);

        $response->assertStatus(422);

        $this->assertDatabaseHas('products', [
            'id' => 'product-1',
            'reorder_point_qty' => null,
            'critical_threshold_qty' => null,
        ]);
    }

    public function test_update_product_endpoint_rejects_when_critical_threshold_exceeds_reorder_point(): void
    {
        $this->loginAsKasir();

        DB::table('products')->insert([
            'id' => 'product-1',
            'kode_barang' => 'KB-021',
            'nama_barang' => 'Vario',
            'merek' => 'Federal',
            'ukuran' => 110,
            'harga_jual' => 17000,
            'reorder_point_qty' => 8,
            'critical_threshold_qty' => 3,
        ]);

        $response = $this->postJson('/product-catalog/products/product-1/update', [
            'kode_barang' => 'KB-021',
            'nama_barang' => 'Vario',
            'merek' => 'Federal',
            'ukuran' => 110,
            'harga_jual' => 17000,
            'reorder_point_qty' => 4,
            'critical_threshold_qty' => 6,
        ]);

        $response->assertStatus(422);

        $this->assertDatabaseHas('products', [
            'id' => 'product-1',
            'reorder_point_qty' => 8,
            'critical_threshold_qty' => 3,
        ]);
    }
}
