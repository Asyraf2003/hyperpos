<?php

declare(strict_types=1);

namespace Tests\Feature\EmployeeFinance;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

final class CreatePayrollPageFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login_when_accessing_create_payroll_page(): void
    {
        $this->get(route('admin.payrolls.create'))->assertRedirect(route('login'));
    }

    public function test_kasir_is_redirected_back_to_cashier_dashboard_when_accessing_create_payroll_page(): void
    {
        $response = $this->actingAs($this->createUserWithRole('kasir-payroll-create@example.test', 'kasir'))
            ->get(route('admin.payrolls.create'));

        $response->assertRedirect(route('cashier.dashboard'));
        $response->assertSessionHas('error', 'Halaman admin hanya untuk role admin.');
    }

    public function test_admin_can_access_create_payroll_page(): void
    {
        DB::table('employees')->insert([
            'id' => (string) Str::uuid(),
            'name' => 'Budi Payroll',
            'phone' => '081222222222',
            'base_salary' => 4500000,
            'pay_period' => 'monthly',
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->actingAs($this->createUserWithRole('admin-payroll-create@example.test', 'admin'))
            ->get(route('admin.payrolls.create'));

        $response->assertOk();
        $response->assertSee('Pilih Karyawan');
        $response->assertSee('Detail Batch Pencairan');
        $response->assertSee('Mode Batch Default');
        $response->assertSee('Karyawan Dipilih');
        $response->assertSee('admin-payroll-create.js');
    }

    public function test_admin_can_store_batch_payroll_from_create_page(): void
    {
        $employeeId = (string) Str::uuid();

        DB::table('employees')->insert([
            'id' => $employeeId,
            'name' => 'Asyraf Mubarak',
            'phone' => '08111222333',
            'base_salary' => 5000000,
            'pay_period' => 'monthly',
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->actingAs($this->createUserWithRole('admin-payroll-store@example.test', 'admin'))
            ->post(route('admin.payrolls.batch.store'), [
                'disbursement_date_string' => '2026-03-25',
                'mode_value' => 'monthly',
                'notes' => 'Batch Maret 2026',
                'rows' => [
                    [
                        'employee_id' => $employeeId,
                        'amount' => 5000000,
                    ],
                ],
            ]);

        $response->assertRedirect(route('admin.payrolls.index'));
        $response->assertSessionHas('success', 'Batch payroll berhasil dicatat.');

        $this->assertDatabaseHas('payroll_disbursements', [
            'employee_id' => $employeeId,
            'amount' => 5000000,
            'mode' => 'monthly',
            'notes' => 'Batch Maret 2026',
        ]);
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
