<?php

declare(strict_types=1);

namespace Tests\Feature\EmployeeFinance;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

final class PayrollTableDataQueryFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_search_payroll_table_with_multi_word_query(): void
    {
        $this->seedPayrollRow('Budi Santoso', '2026-03-25 00:00:00', 5000000, 'monthly', 'Gaji Maret 2026');
        $this->seedPayrollRow('Andi', '2026-03-20 00:00:00', 3000000, 'weekly', 'Kasbon payroll');

        $r = $this->actingAs($this->admin())->get(route('admin.payrolls.table', ['q' => 'Budi Maret']));
        $r->assertOk();
        $r->assertJsonCount(1, 'data.rows');
        $r->assertJsonPath('data.rows.0.employee_name', 'Budi Santoso');
        $r->assertJsonPath('data.meta.filters.q', 'Budi Maret');
    }

    public function test_admin_can_sort_payroll_table_by_amount_desc(): void
    {
        $this->seedPayrollRow('Budi', '2026-03-25 00:00:00', 3000000, 'weekly', 'Gaji A');
        $this->seedPayrollRow('Andi', '2026-03-24 00:00:00', 6000000, 'monthly', 'Gaji B');

        $r = $this->actingAs($this->admin())->get(route('admin.payrolls.table', ['sort_by' => 'amount', 'sort_dir' => 'desc']));
        $r->assertOk();
        $r->assertJsonPath('data.rows.0.employee_name', 'Andi');
        $r->assertJsonPath('data.rows.1.employee_name', 'Budi');
    }

    public function test_admin_can_access_second_page_of_payroll_table(): void
    {
        for ($i = 1; $i <= 11; $i++) {
            $this->seedPayrollRow('Employee '.str_pad((string) $i, 2, '0', STR_PAD_LEFT), '2026-03-25 00:00:00', 1000000 + $i, 'monthly', 'Payroll '.$i);
        }

        $r = $this->actingAs($this->admin())->get(route('admin.payrolls.table', ['page' => 2]));
        $r->assertOk();
        $r->assertJsonPath('data.meta.page', 2);
        $r->assertJsonPath('data.meta.last_page', 2);
        $r->assertJsonPath('data.rows.0.employee_name', 'Employee 11');
    }

    private function admin(): User
    {
        $user = User::query()->create(['name' => 'Admin', 'email' => 'admin@example.test', 'password' => 'password123']);
        DB::table('actor_accesses')->insert(['actor_id' => (string) $user->getAuthIdentifier(), 'role' => 'admin']);
        return $user;
    }

    private function seedPayrollRow(string $name, string $date, int $amount, string $mode, string $notes): void
    {
        $employeeId = (string) \Illuminate\Support\Str::uuid();
        $payrollId = (string) \Illuminate\Support\Str::uuid();

        DB::table('employees')->insert([
            'id' => $employeeId,
            'name' => $name,
            'phone' => '0812',
            'base_salary' => 5000000,
            'pay_period' => 'monthly',
            'status' => 'active',
        ]);

        DB::table('payroll_disbursements')->insert([
            'id' => $payrollId,
            'employee_id' => $employeeId,
            'amount' => $amount,
            'disbursement_date' => $date,
            'mode' => $mode,
            'notes' => $notes,
        ]);
    }
}
