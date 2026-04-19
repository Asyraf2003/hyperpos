<?php

declare(strict_types=1);

namespace Tests\Feature\ProductCatalog;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

final class ProductCreatePageFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login_when_accessing_create_product_page(): void
    {
        $response = $this->get(route('admin.products.create'));

        $response->assertRedirect(route('login'));
    }

    public function test_kasir_is_redirected_back_to_cashier_dashboard_when_accessing_create_product_page(): void
    {
        $user = $this->createUserWithRole('kasir-product-create@example.test', 'kasir');

        $response = $this
            ->actingAs($user)
            ->get(route('admin.products.create'));

        $response->assertRedirect(route('cashier.dashboard'));
        $response->assertSessionHas('error', 'Halaman admin hanya untuk role admin.');
    }

    public function test_admin_can_access_create_product_page(): void
    {
        $user = $this->createUserWithRole('admin-product-create@example.test', 'admin');

        $response = $this
            ->actingAs($user)
            ->get(route('admin.products.create'));

        $response->assertOk();
        $response->assertSee('Tambah Produk');
        $response->assertSee('Nama Barang');
        $response->assertSee('Harga Jual');
        $response->assertSee('Simpan Produk');
    }

    public function test_admin_can_store_product_from_create_page(): void
    {
        $user = $this->createUserWithRole('admin-product-store@example.test', 'admin');

        $response = $this
            ->actingAs($user)
            ->post(route('admin.products.store'), [
                'kode_barang' => 'KB-101',
                'nama_barang' => 'Ban Tubeless',
                'merek' => 'FDR',
                'ukuran' => 90,
                'harga_jual' => 55000,
            ]);

        $response->assertRedirect(route('admin.products.index'));
        $response->assertSessionHas('success', 'Product master berhasil dibuat.');

        $this->assertDatabaseHas('products', [
            'kode_barang' => 'KB-101',
            'nama_barang' => 'Ban Tubeless',
            'merek' => 'FDR',
            'ukuran' => 90,
            'harga_jual' => 55000,
        ]);
    }

    public function test_admin_store_product_returns_back_with_error_when_duplicate_conflict_happens(): void
    {
        $user = $this->createUserWithRole('admin-product-duplicate@example.test', 'admin');

        DB::table('products')->insert([
            'id' => 'product-1',
            'kode_barang' => null,
            'nama_barang' => 'Supra',
            'merek' => 'Federal',
            'ukuran' => null,
            'harga_jual' => 15000,
        ]);

        $response = $this
            ->from(route('admin.products.create'))
            ->actingAs($user)
            ->post(route('admin.products.store'), [
                'kode_barang' => null,
                'nama_barang' => 'Supra',
                'merek' => 'Federal',
                'ukuran' => null,
                'harga_jual' => 17000,
            ]);

        $response->assertRedirect(route('admin.products.create'));
        $response->assertSessionHasErrors([
            'product' => 'Product dengan kombinasi data ini sudah ada.',
        ]);

        $this->assertDatabaseCount('products', 1);
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
