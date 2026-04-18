<?php

declare(strict_types=1);

namespace Tests\Feature\Admin\Product;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

final class ProductMasterValidationFeedbackTest extends TestCase
{
    use RefreshDatabase;

    public function test_create_product_duplicate_kode_barang_returns_session_error_instead_of_500(): void
    {
        $this->loginAsAuthorizedAdmin();

        DB::table('products')->insert([
            'id' => 'product-existing',
            'kode_barang' => 'KB-001',
            'nama_barang' => 'Ban Luar',
            'nama_barang_normalized' => 'ban luar',
            'merek' => 'Federal',
            'merek_normalized' => 'federal',
            'ukuran' => 80,
            'harga_jual' => 15000,
        ]);

        $response = $this
            ->from('/admin/products/create')
            ->post('/admin/products', [
                'kode_barang' => 'KB-001',
                'nama_barang' => 'Ban Dalam',
                'merek' => 'Federal',
                'ukuran' => 90,
                'harga_jual' => 17000,
            ]);

        $response->assertStatus(302);
        $response->assertRedirect('/admin/products/create');
        $response->assertSessionHasErrors([
            'kode_barang' => 'Kode barang sudah dipakai product lain.',
        ]);
    }

    public function test_update_product_duplicate_kode_barang_returns_session_error_instead_of_500(): void
    {
        $this->loginAsAuthorizedAdmin();

        DB::table('products')->insert([
            'id' => 'product-1',
            'kode_barang' => 'KB-001',
            'nama_barang' => 'Ban Luar',
            'nama_barang_normalized' => 'ban luar',
            'merek' => 'Federal',
            'merek_normalized' => 'federal',
            'ukuran' => 80,
            'harga_jual' => 15000,
        ]);

        DB::table('products')->insert([
            'id' => 'product-2',
            'kode_barang' => 'KB-002',
            'nama_barang' => 'Ban Dalam',
            'nama_barang_normalized' => 'ban dalam',
            'merek' => 'Federal',
            'merek_normalized' => 'federal',
            'ukuran' => 90,
            'harga_jual' => 17000,
        ]);

        $response = $this
            ->from('/admin/products/product-1/edit')
            ->put('/admin/products/product-1', [
                'kode_barang' => 'KB-002',
                'nama_barang' => 'Ban Luar',
                'merek' => 'Federal',
                'ukuran' => 80,
                'harga_jual' => 16000,
            ]);

        $response->assertStatus(302);
        $response->assertRedirect('/admin/products/product-1/edit');
        $response->assertSessionHasErrors([
            'kode_barang' => 'Kode barang sudah dipakai product lain.',
        ]);
    }
}
