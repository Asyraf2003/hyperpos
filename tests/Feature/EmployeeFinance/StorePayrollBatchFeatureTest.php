<?php

declare(strict_types=1);

namespace Tests\Feature\EmployeeFinance;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

final class StorePayrollBatchFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login_when_storing_payroll_batch(): void
    {
        $this->post(route('admin.payrolls.batch.store'))->assertRedirect(route('login'));
    }

    public function test_kasir_is_redirected_back_to_cashier_dashboard_when_storing_payroll_batch(): void
    {
        $response = $this->actingAs($this->user('kasir'))->post(route('admin.payrolls.batch.store'));
        $response->assertRedirect(route('cashier.dashboard'));
        $response->assertSessionHas('error', 'Halaman admin hanya untuk role admin.');
    }

    public function test_admin_can_store_payroll_batch(): void
    {
        $this->seedEmployee('11111111-1111-1111-1111-111111111111');
        $this->seedEmployee('22222222-2222-2222-2222-222222222222');

        $response = $this->actingAs($this->user('admin'))->post(route('admin.payrolls.batch.store'), [
            'disbursement_date_string' => '2026-03-25',
            'mode_value' => 'monthly',
            'notes' => 'Batch Maret',
            'rows' => [
                ['employee_id' => '11111111-1111-1111-1111-111111111111', 'amount' => 5000000],
                ['employee_id' => '22222222-2222-2222-2222-222222222222', 'amount' => 4500000],
            ],
        ]);

        $response->assertRedirect(route('admin.payrolls.index'));
        $response->assertSessionHas('success', 'Batch payroll berhasil dicatat.');
        $this->assertDatabaseCount('payroll_disbursements', 2);
        $this->assertDatabaseHas('audit_logs', ['event' => 'payroll_batch_disbursement_recorded']);
    }

    private function user(string $role): User
    {
        $user = User::query()->create(['name' => 'Test', 'email' => $role.'@example.test', 'password' => 'password123']);
        DB::table('actor_accesses')->insert(['actor_id' => (string) $user->getAuthIdentifier(), 'role' => $role]);
        return $user;
    }

    private function seedEmployee(string $id): void
    {
        DB::table('employees')->insert([
            'id' => $id, 'employee_name' => 'Employee '.$id[0], 'phone' => '0812', 'default_salary_amount' => 5000000,
            'salary_basis_type' => 'monthly', 'employment_status' => 'active', 'created_at' => now(), 'updated_at' => now(),
        ]);
    }
}
