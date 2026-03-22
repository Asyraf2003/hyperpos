<?php

declare(strict_types=1);

namespace Tests\Feature\EmployeeFinance;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

final class EmployeeDebtIndexPageFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login_when_accessing_employee_debt_index_page(): void
    {
        $this->get(route('admin.employee-debts.index'))->assertRedirect(route('login'));
    }

    public function test_kasir_is_redirected_back_to_cashier_dashboard_when_accessing_employee_debt_index_page(): void
    {
        $response = $this->actingAs($this->createUserWithRole('kasir-debt-index@example.test', 'kasir'))
            ->get(route('admin.employee-debts.index'));

        $response->assertRedirect(route('cashier.dashboard'));
        $response->assertSessionHas('error', 'Halaman admin hanya untuk role admin.');
    }

    public function test_admin_can_access_employee_debt_index_page_and_see_employee_summary_rows(): void
    {
        $employeeId = (string) Str::uuid();

        DB::table('employees')->insert([
            'id' => $employeeId,
            'name' => 'Asyraf Hutang',
            'phone' => '081111111111',
            'base_salary' => 5000000,
            'pay_period' => 'monthly',
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('employee_debts')->insert([
            [
                'id' => (string) Str::uuid(),
                'employee_id' => $employeeId,
                'total_debt' => 1000000,
                'remaining_balance' => 750000,
                'status' => 'unpaid',
                'notes' => 'Kasbon awal',
                'created_at' => '2026-03-22 10:00:00',
                'updated_at' => now(),
            ],
            [
                'id' => (string) Str::uuid(),
                'employee_id' => $employeeId,
                'total_debt' => 500000,
                'remaining_balance' => 0,
                'status' => 'paid',
                'notes' => 'Kasbon kedua',
                'created_at' => '2026-03-23 10:00:00',
                'updated_at' => now(),
            ],
        ]);

        $response = $this->actingAs($this->createUserWithRole('admin-debt-index@example.test', 'admin'))
            ->get(route('admin.employee-debts.index'));

        $response->assertOk();
        $response->assertSee('Ringkasan Hutang Karyawan');
        $response->assertSee('Asyraf Hutang');
        $response->assertSee('2026-03-23');
        $response->assertSee('2');
        $response->assertSee('Rp1.500.000');
        $response->assertSee('Rp750.000');
        $response->assertSee('1 aktif / 1 lunas');
        $response->assertSee('Buka Detail Karyawan');
        $response->assertDontSee('Kasbon awal');
        $response->assertDontSee('Kasbon kedua');
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
