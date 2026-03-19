<?php

declare(strict_types=1);

namespace Tests\Feature\Procurement;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

final class SupplierTableDataAccessFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login_when_accessing_supplier_table_data(): void
    {
        $this->get(route('admin.suppliers.table'))->assertRedirect(route('login'));
    }

    public function test_kasir_is_redirected_back_to_cashier_dashboard_when_accessing_supplier_table_data(): void
    {
        $response = $this->actingAs($this->user('kasir'))->get(route('admin.suppliers.table'));

        $response->assertRedirect(route('cashier.dashboard'));
        $response->assertSessionHas('error', 'Halaman admin hanya untuk role admin.');
    }

    public function test_admin_can_get_supplier_table_json(): void
    {
        DB::table('suppliers')->insert([
            'id' => 'supplier-1',
            'nama_pt_pengirim' => 'PT Federal Abadi',
            'nama_pt_pengirim_normalized' => 'pt federal abadi',
        ]);

        $response = $this->actingAs($this->user('admin'))->get(route('admin.suppliers.table'));

        $response->assertOk();
        $response->assertJsonPath('success', true);
        $response->assertJsonPath('data.rows.0.nama_pt_pengirim', 'PT Federal Abadi');
    }

    private function user(string $role): User
    {
        $user = User::query()->create([
            'name' => 'Test',
            'email' => $role . '@example.test',
            'password' => 'password123',
        ]);

        DB::table('actor_accesses')->insert([
            'actor_id' => (string) $user->getAuthIdentifier(),
            'role' => $role,
        ]);

        return $user;
    }
}
