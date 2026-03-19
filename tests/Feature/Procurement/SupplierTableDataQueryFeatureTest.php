<?php

declare(strict_types=1);

namespace Tests\Feature\Procurement;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

final class SupplierTableDataQueryFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_search_supplier_table(): void
    {
        $this->seedSupplier('supplier-1', 'PT Federal Abadi');
        $this->seedSupplier('supplier-2', 'PT Astra Otoparts');
        $this->seedSupplier('supplier-3', 'CV Toko Lokal');

        $response = $this->actingAs($this->admin())->get(route('admin.suppliers.table', ['q' => 'PT']));

        $response->assertOk();
        $response->assertJsonCount(2, 'data.rows');
        $response->assertJsonPath('data.meta.filters.q', 'PT');
    }

    public function test_admin_can_sort_supplier_table_by_nama_pt_pengirim_desc(): void
    {
        $this->seedSupplier('supplier-1', 'PT Alpha Motor');
        $this->seedSupplier('supplier-2', 'PT Zebra Parts');

        $response = $this->actingAs($this->admin())->get(route('admin.suppliers.table', [
            'sort_by' => 'nama_pt_pengirim',
            'sort_dir' => 'desc',
        ]));

        $response->assertOk();
        $response->assertJsonPath('data.rows.0.nama_pt_pengirim', 'PT Zebra Parts');
        $response->assertJsonPath('data.rows.1.nama_pt_pengirim', 'PT Alpha Motor');
    }

    public function test_admin_can_access_second_page_of_supplier_table(): void
    {
        for ($i = 1; $i <= 11; $i++) {
            $this->seedSupplier(
                'supplier-' . $i,
                'Supplier ' . str_pad((string) $i, 2, '0', STR_PAD_LEFT),
            );
        }

        $response = $this->actingAs($this->admin())->get(route('admin.suppliers.table', ['page' => 2]));

        $response->assertOk();
        $response->assertJsonPath('data.meta.page', 2);
        $response->assertJsonPath('data.meta.last_page', 2);
        $response->assertJsonPath('data.rows.0.nama_pt_pengirim', 'Supplier 11');
    }

    private function admin(): User
    {
        $user = User::query()->create([
            'name' => 'Admin',
            'email' => 'admin@example.test',
            'password' => 'password123',
        ]);

        DB::table('actor_accesses')->insert([
            'actor_id' => (string) $user->getAuthIdentifier(),
            'role' => 'admin',
        ]);

        return $user;
    }

    private function seedSupplier(string $id, string $namaPtPengirim): void
    {
        DB::table('suppliers')->insert([
            'id' => $id,
            'nama_pt_pengirim' => $namaPtPengirim,
            'nama_pt_pengirim_normalized' => mb_strtolower($namaPtPengirim),
        ]);
    }
}
