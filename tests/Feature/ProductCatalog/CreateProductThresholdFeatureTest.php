<?php

declare(strict_types=1);

namespace Tests\Feature\ProductCatalog;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class CreateProductThresholdFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_create_product_endpoint_rejects_when_only_reorder_point_is_filled(): void
    {
        $this->loginAsKasir();

        $response = $this->postJson('/product-catalog/products/create', [
            'kode_barang' => 'KB-010',
            'nama_barang' => 'Beat',
            'merek' => 'Federal',
            'ukuran' => 90,
            'harga_jual' => 15000,
            'reorder_point_qty' => 10,
            'critical_threshold_qty' => null,
        ]);

        $response->assertStatus(422);

        $this->assertDatabaseMissing('products', [
            'kode_barang' => 'KB-010',
            'nama_barang' => 'Beat',
            'merek' => 'Federal',
        ]);
    }

    public function test_create_product_endpoint_rejects_when_critical_threshold_exceeds_reorder_point(): void
    {
        $this->loginAsKasir();

        $response = $this->postJson('/product-catalog/products/create', [
            'kode_barang' => 'KB-011',
            'nama_barang' => 'Scoopy',
            'merek' => 'Federal',
            'ukuran' => 90,
            'harga_jual' => 16000,
            'reorder_point_qty' => 5,
            'critical_threshold_qty' => 7,
        ]);

        $response->assertStatus(422);

        $this->assertDatabaseMissing('products', [
            'kode_barang' => 'KB-011',
            'nama_barang' => 'Scoopy',
            'merek' => 'Federal',
        ]);
    }
}
