<?php

declare(strict_types=1);

namespace Tests\Feature\Reporting;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

final class PayrollReportPageFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login_when_accessing_payroll_report_page(): void
    {
        $this->get(route('admin.reports.payroll.index'))->assertRedirect(route('login'));
    }

    public function test_kasir_is_redirected_back_to_cashier_dashboard_when_accessing_payroll_report_page(): void
    {
        $response = $this->actingAs($this->user('kasir'))->get(route('admin.reports.payroll.index'));
        $response->assertRedirect(route('cashier.dashboard'));
        $response->assertSessionHas('error', 'Halaman admin hanya untuk role admin.');
    }

    public function test_admin_can_access_payroll_report_page_and_see_report_data(): void
    {
        $this->seedEmployee('employee-1', 'Montir A');
        $this->seedEmployee('employee-2', 'Montir B');
        $this->seedPayroll('payroll-1', 'employee-1', 50000, '2030-01-06 08:00:00', 'daily', 'Harian A');
        $this->seedPayroll('payroll-2', 'employee-2', 40000, '2030-01-07 09:00:00', 'weekly', 'Mingguan B');
        $this->seedPayroll('payroll-3', 'employee-1', 10000, '2030-01-07 10:00:00', 'daily', 'Harian A2');

        $response = $this->actingAs($this->user('admin'))->get(route('admin.reports.payroll.index', [
            'period_mode' => 'custom',
            'date_from' => '2030-01-01',
            'date_to' => '2030-01-31',
        ]));

        $response->assertOk();
        $response->assertSee('Laporan Gaji');
        $response->assertSee('payroll-report-filter-form', false);
        $response->assertSee('2030-01-01 s/d 2030-01-31');
        $response->assertSee('Montir A');
        $response->assertSee('Montir B');
        $response->assertSee('Harian');
        $response->assertSee('Mingguan');
        $response->assertSee('Rp 100.000');
        $response->assertSee('2030-01-07');
    }

    private function user(string $role): User
    {
        $user = User::query()->create([
            'name' => 'Test',
            'email' => $role . '-payroll-report@example.test',
            'password' => 'password123',
        ]);

        DB::table('actor_accesses')->insert([
            'actor_id' => (string) $user->getAuthIdentifier(),
            'role' => $role,
        ]);

        return $user;
    }

    private function seedEmployee(string $id, string $name): void
    {
        DB::table('employees')->insert([
            'id' => $id,
            'employee_name' => $name,
            'phone' => null,
            'default_salary_amount' => 5000000,
            'salary_basis_type' => 'monthly',
            'employment_status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function seedPayroll(string $id, string $employeeId, int $amount, string $date, string $mode, ?string $notes): void
    {
        DB::table('payroll_disbursements')->insert([
            'id' => $id,
            'employee_id' => $employeeId,
            'amount' => $amount,
            'disbursement_date' => $date,
            'mode' => $mode,
            'notes' => $notes,
            'created_at' => $date,
            'updated_at' => $date,
        ]);
    }
}
