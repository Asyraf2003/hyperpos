<?php

declare(strict_types=1);

namespace Tests\Feature\EmployeeFinance;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

final class EmployeeIndexPageFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login_when_accessing_employee_index_page(): void
    {
        $this->get(route('admin.employees.index'))->assertRedirect(route('login'));
    }

    public function test_kasir_is_redirected_back_to_cashier_dashboard_when_accessing_employee_index_page(): void
    {
        $response = $this->actingAs($this->createUserWithRole('kasir-employee-index@example.test', 'kasir'))
            ->get(route('admin.employees.index'));

        $response->assertRedirect(route('cashier.dashboard'));
        $response->assertSessionHas('error', 'Halaman admin hanya untuk role admin.');
    }

    public function test_admin_can_access_employee_index_page_and_see_existing_employee_data(): void
    {
        DB::table('employees')->insert([
            'id' => 'employee-1',
            'name' => 'Budi Santoso',
            'phone' => '081234567890',
            'base_salary' => 5000000,
            'pay_period' => 'monthly',
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->actingAs($this->createUserWithRole('admin-employee-index@example.test', 'admin'))
            ->get(route('admin.employees.index'));

        $response->assertOk();
        $response->assertSee('Master data karyawan untuk admin.');
        $response->assertSee('Tambah Data Karyawan');
        $response->assertSee('Budi Santoso');
        $response->assertSee('081234567890');
        $response->assertSee('Rp5.000.000');
        $response->assertSee('Bulanan');
        $response->assertSee('Aktif');
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
