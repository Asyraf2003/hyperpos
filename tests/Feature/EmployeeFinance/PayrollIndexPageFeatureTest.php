<?php

declare(strict_types=1);

namespace Tests\Feature\EmployeeFinance;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

final class PayrollIndexPageFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login_when_accessing_payroll_index_page(): void
    {
        $this->get(route('admin.payrolls.index'))->assertRedirect(route('login'));
    }

    public function test_kasir_is_redirected_back_to_cashier_dashboard_when_accessing_payroll_index_page(): void
    {
        $response = $this->actingAs($this->createUserWithRole('kasir-payroll-index@example.test', 'kasir'))
            ->get(route('admin.payrolls.index'));

        $response->assertRedirect(route('cashier.dashboard'));
        $response->assertSessionHas('error', 'Halaman admin hanya untuk role admin.');
    }

    public function test_admin_can_access_payroll_index_page_and_see_existing_rows(): void
    {
        DB::table('employees')->insert([
            'id' => '11111111-1111-1111-1111-111111111111',
            'name' => 'Asyraf Payroll',
            'phone' => '081111111111',
            'base_salary' => 5000000,
            'pay_period' => 'monthly',
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('payroll_disbursements')->insert([
            'id' => '22222222-2222-2222-2222-222222222222',
            'employee_id' => '11111111-1111-1111-1111-111111111111',
            'amount' => 5000000,
            'disbursement_date' => '2026-03-25 00:00:00',
            'mode' => 'monthly',
            'notes' => 'Gaji Maret 2026',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->actingAs($this->createUserWithRole('admin-payroll-index@example.test', 'admin'))
            ->get(route('admin.payrolls.index'));

        $response->assertOk();
        $response->assertSee('Riwayat Pencairan Gaji');
        $response->assertSee('Asyraf Payroll');
        $response->assertSee('Rp5.000.000');
        $response->assertSee('Bulanan');
        $response->assertSee('Gaji Maret 2026');
        $response->assertSee('2026-03-25');
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
