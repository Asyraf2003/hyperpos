<?php

declare(strict_types=1);

namespace Tests\Feature\EmployeeFinance;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

final class EmployeeDetailPageFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login_when_accessing_employee_detail_page(): void
    {
        $this->get(route('admin.employees.show', ['employeeId' => 'employee-1']))
            ->assertRedirect(route('login'));
    }

    public function test_kasir_is_redirected_back_to_cashier_dashboard_when_accessing_employee_detail_page(): void
    {
        $response = $this->actingAs($this->createUserWithRole('kasir-employee-detail@example.test', 'kasir'))
            ->get(route('admin.employees.show', ['employeeId' => 'employee-1']));

        $response->assertRedirect(route('cashier.dashboard'));
        $response->assertSessionHas('error', 'Halaman admin hanya untuk role admin.');
    }

    public function test_admin_can_access_employee_detail_page(): void
    {
        $employeeId = $this->seedEmployee();

        $response = $this->actingAs($this->createUserWithRole('admin-employee-detail@example.test', 'admin'))
            ->get(route('admin.employees.show', ['employeeId' => $employeeId]));

        $response->assertOk();
        $response->assertSee('Detail Karyawan');
        $response->assertSee('Ringkasan Karyawan');
        $response->assertSee('Budi Santoso');
        $response->assertSee('Mingguan');
        $response->assertSee('Aktif');
        $response->assertSee('Section Hutang Karyawan');
        $response->assertSee('Section Riwayat Gaji');
        $response->assertSee('Edit Karyawan');
    }

    public function test_admin_is_redirected_to_index_when_employee_detail_is_missing(): void
    {
        $response = $this->actingAs($this->createUserWithRole('admin-employee-detail-missing@example.test', 'admin'))
            ->get(route('admin.employees.show', ['employeeId' => 'missing-employee']));

        $response->assertRedirect(route('admin.employees.index'));
        $response->assertSessionHas('error', 'Data karyawan tidak ditemukan.');
    }

    private function seedEmployee(): string
    {
        $employeeId = (string) Str::uuid();

        DB::table('employees')->insert([
            'id' => $employeeId,
            'name' => 'Budi Santoso',
            'phone' => '081211111111',
            'base_salary' => 5000000,
            'pay_period' => 'weekly',
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return $employeeId;
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
