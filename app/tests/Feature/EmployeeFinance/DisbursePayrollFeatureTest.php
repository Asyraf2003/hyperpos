<?php

declare(strict_types=1);

namespace Tests\Feature\EmployeeFinance;

use App\Application\EmployeeFinance\UseCases\DisbursePayrollHandler;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use InvalidArgumentException;
use Tests\TestCase;

final class DisbursePayrollFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_disburse_payroll_handler_stores_payroll_disbursement(): void
    {
        $employeeId = (string) Str::uuid();

        DB::table('employees')->insert([
            'id' => $employeeId,
            'employee_name' => 'Asyraf Payroll',
            'phone' => '081111111111',
            'default_salary_amount' => 5000000,
            'salary_basis_type' => 'monthly',
            'employment_status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $handler = app(DisbursePayrollHandler::class);

        $payrollId = $handler->handle(
            $employeeId,
            5000000,
            '2026-03-25',
            'monthly',
            'Gaji Maret 2026'
        );

        $this->assertIsString($payrollId);

        $this->assertDatabaseHas('payroll_disbursements', [
            'id' => $payrollId,
            'employee_id' => $employeeId,
            'amount' => 5000000,
            'mode' => 'monthly',
            'notes' => 'Gaji Maret 2026',
        ]);
    }

    public function test_disburse_payroll_handler_rejects_when_employee_not_found(): void
    {
        $handler = app(DisbursePayrollHandler::class);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Karyawan tidak ditemukan.');

        $handler->handle(
            (string) Str::uuid(),
            5000000,
            '2026-03-25',
            'monthly',
            'Gaji Maret 2026'
        );
    }
}
