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
