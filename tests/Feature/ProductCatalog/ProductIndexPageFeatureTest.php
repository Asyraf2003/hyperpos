<?php

declare(strict_types=1);

namespace Tests\Feature\ProductCatalog;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

final class ProductIndexPageFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login_when_accessing_product_index_page(): void
    {
        $response = $this->get(route('admin.products.index'));

        $response->assertRedirect(route('login'));
    }

    public function test_kasir_is_redirected_back_to_cashier_dashboard_when_accessing_product_index_page(): void
    {
        $user = $this->createUserWithRole('kasir-product-index@example.test', 'kasir');

        $response = $this
            ->actingAs($user)
            ->get(route('admin.products.index'));

        $response->assertRedirect(route('cashier.dashboard'));
        $response->assertSessionHas('error', 'Halaman admin hanya untuk role admin.');
    }

    public function test_admin_can_access_product_index_page_and_see_empty_state(): void
    {
        $user = $this->createUserWithRole('admin-product-index@example.test', 'admin');

        $response = $this
            ->actingAs($user)
            ->get(route('admin.products.index'));

        $response->assertOk();
        $response->assertSee('Product');
        $response->assertSee('Master barang bengkel');
        $response->assertSee('Belum ada product master.');
    }

    public function test_admin_can_see_product_rows_sorted_by_nama_merek_ukuran_and_id(): void
    {
        $user = $this->createUserWithRole('admin-product-list@example.test', 'admin');

        DB::table('products')->insert([
            [
                'id' => 'product-3',
                'kode_barang' => 'KB-003',
                'nama_barang' => 'Ban Luar',
                'merek' => 'Federal',
                'ukuran' => 90,
                'harga_jual' => 35000,
            ],
            [
                'id' => 'product-1',
                'kode_barang' => null,
                'nama_barang' => 'Aki Kering',
                'merek' => 'GS Astra',
                'ukuran' => null,
                'harga_jual' => 120000,
            ],
            [
                'id' => 'product-2',
                'kode_barang' => 'KB-002',
                'nama_barang' => 'Ban Dalam',
                'merek' => 'FDR',
                'ukuran' => 80,
                'harga_jual' => 18000,
            ],
        ]);

        $response = $this
            ->actingAs($user)
            ->get(route('admin.products.index'));

        $response->assertOk();
        $response->assertSeeInOrder([
            'Aki Kering',
            'Ban Dalam',
            'Ban Luar',
        ]);
        $response->assertSee('Rp 120.000');
        $response->assertSee('Rp 18.000');
        $response->assertSee('Rp 35.000');
    }

    public function test_admin_can_search_product_by_nama_barang(): void
    {
        $user = $this->createUserWithRole('admin-product-search-name@example.test', 'admin');

        DB::table('products')->insert([
            [
                'id' => 'product-1',
                'kode_barang' => 'KB-001',
                'nama_barang' => 'Ban Luar',
                'merek' => 'Federal',
                'ukuran' => 90,
                'harga_jual' => 35000,
            ],
            [
                'id' => 'product-2',
                'kode_barang' => 'KB-002',
                'nama_barang' => 'Aki Kering',
                'merek' => 'GS Astra',
                'ukuran' => null,
                'harga_jual' => 120000,
            ],
        ]);

        $response = $this
            ->actingAs($user)
            ->get(route('admin.products.index', ['q' => 'Ban']));

        $response->assertOk();
        $response->assertSee('value="Ban"', false);
        $response->assertSee('Ban Luar');
        $response->assertDontSee('Aki Kering');
    }

    public function test_admin_can_search_product_by_kode_barang_or_merek(): void
    {
        $user = $this->createUserWithRole('admin-product-search-code-brand@example.test', 'admin');

        DB::table('products')->insert([
            [
                'id' => 'product-1',
                'kode_barang' => 'KB-101',
                'nama_barang' => 'Ban Luar',
                'merek' => 'Federal',
                'ukuran' => 90,
                'harga_jual' => 35000,
            ],
            [
                'id' => 'product-2',
                'kode_barang' => 'KB-202',
                'nama_barang' => 'Aki Kering',
                'merek' => 'GS Astra',
                'ukuran' => null,
                'harga_jual' => 120000,
            ],
        ]);

        $responseByCode = $this
            ->actingAs($user)
            ->get(route('admin.products.index', ['q' => 'KB-202']));

        $responseByCode->assertOk();
        $responseByCode->assertSee('Aki Kering');
        $responseByCode->assertDontSee('Ban Luar');

        $responseByBrand = $this
            ->actingAs($user)
            ->get(route('admin.products.index', ['q' => 'Federal']));

        $responseByBrand->assertOk();
        $responseByBrand->assertSee('Ban Luar');
        $responseByBrand->assertDontSee('Aki Kering');
    }

    public function test_admin_sees_search_empty_state_when_query_has_no_match(): void
    {
        $user = $this->createUserWithRole('admin-product-search-empty@example.test', 'admin');

        DB::table('products')->insert([
            'id' => 'product-1',
            'kode_barang' => 'KB-001',
            'nama_barang' => 'Ban Luar',
            'merek' => 'Federal',
            'ukuran' => 90,
            'harga_jual' => 35000,
        ]);

        $response = $this
            ->actingAs($user)
            ->get(route('admin.products.index', ['q' => 'TidakAda']));

        $response->assertOk();
        $response->assertSee('Tidak ada product yang cocok dengan pencarian.');
        $response->assertDontSee('Ban Luar');
    }

    public function test_admin_can_see_paginated_product_rows_on_second_page(): void
    {
        $user = $this->createUserWithRole('admin-product-pagination@example.test', 'admin');

        $records = [];

        for ($i = 1; $i <= 12; $i++) {
            $records[] = [
                'id' => 'product-' . str_pad((string) $i, 2, '0', STR_PAD_LEFT),
                'kode_barang' => 'KB-' . str_pad((string) $i, 3, '0', STR_PAD_LEFT),
                'nama_barang' => 'Produk ' . str_pad((string) $i, 2, '0', STR_PAD_LEFT),
                'merek' => 'Merek A',
                'ukuran' => null,
                'harga_jual' => 10000 + $i,
            ];
        }

        DB::table('products')->insert($records);

        $response = $this
            ->actingAs($user)
            ->get(route('admin.products.index', ['page' => 2]));

        $response->assertOk();
        $response->assertSee('Produk 11');
        $response->assertSee('Produk 12');
        $response->assertDontSee('Produk 01');
    }

    public function test_admin_can_access_second_page_of_search_result(): void
    {
        $user = $this->createUserWithRole('admin-product-search-pagination@example.test', 'admin');

        $records = [];

        for ($i = 1; $i <= 11; $i++) {
            $records[] = [
                'id' => 'ban-' . str_pad((string) $i, 2, '0', STR_PAD_LEFT),
                'kode_barang' => 'BAN-' . str_pad((string) $i, 3, '0', STR_PAD_LEFT),
                'nama_barang' => 'Ban ' . str_pad((string) $i, 2, '0', STR_PAD_LEFT),
                'merek' => 'Federal',
                'ukuran' => null,
                'harga_jual' => 20000 + $i,
            ];
        }

        $records[] = [
            'id' => 'aki-01',
            'kode_barang' => 'AKI-001',
            'nama_barang' => 'Aki Kering',
            'merek' => 'GS Astra',
            'ukuran' => null,
            'harga_jual' => 120000,
        ];

        DB::table('products')->insert($records);

        $response = $this
            ->actingAs($user)
            ->get(route('admin.products.index', ['q' => 'Ban', 'page' => 2]));

        $response->assertOk();
        $response->assertSee('value="Ban"', false);
        $response->assertSee('Ban 11');
        $response->assertDontSee('Ban 01');
        $response->assertDontSee('Aki Kering');
    }

    private function createUserWithRole(string $email, string $role): User
    {
        $user = User::query()->create([
            'name' => 'Test User',
            'email' => $email,
            'password' => 'password123',
        ]);

        DB::table('actor_accesses')->insert([
            'actor_id' => (string) $user->getAuthIdentifier(),
            'role' => $role,
        ]);

        return $user;
    }
}
