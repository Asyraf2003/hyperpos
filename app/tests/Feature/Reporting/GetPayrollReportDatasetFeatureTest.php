<?php

declare(strict_types=1);

namespace Tests\Feature\Reporting;

use App\Application\Reporting\UseCases\GetPayrollReportDatasetHandler;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

final class GetPayrollReportDatasetFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_payroll_report_dataset_excludes_reversed_rows_and_builds_summary(): void
    {
        $this->seedEmployee('employee-1', 'Montir A');
        $this->seedEmployee('employee-2', 'Montir B');
        $this->seedPayroll('payroll-1', 'employee-1', 50000, '2030-01-06 08:00:00', 'daily', 'Harian A');
        $this->seedPayroll('payroll-2', 'employee-2', 40000, '2030-01-07 09:00:00', 'weekly', 'Mingguan B');
        $this->seedPayroll('payroll-3', 'employee-1', 10000, '2030-01-07 10:00:00', 'daily', 'Harian A2');
        $this->seedPayroll('payroll-4', 'employee-2', 90000, '2030-01-31 11:00:00', 'monthly', 'Direversal');

        DB::table('payroll_disbursement_reversals')->insert([
            'id' => 'reversal-1',
            'payroll_disbursement_id' => 'payroll-4',
            'reason' => 'Koreksi payout',
            'performed_by_actor_id' => 'actor-admin-1',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $result = app(GetPayrollReportDatasetHandler::class)->handle('2030-01-01', '2030-01-31');
        $this->assertTrue($result->isSuccess());

        $data = $result->data();
        $this->assertSame([
            'total_rows' => 3,
            'total_amount_rupiah' => 100000,
            'latest_disbursement_date' => '2030-01-07',
            'top_mode_label' => 'Harian',
            'top_mode_amount_rupiah' => 60000,
            'average_daily_rupiah' => 3225,
        ], $data['summary']);

        $this->assertSame([
            ['period_label' => '2030-01-06', 'total_rows' => 1, 'total_amount_rupiah' => 50000],
            ['period_label' => '2030-01-07', 'total_rows' => 2, 'total_amount_rupiah' => 50000],
        ], $data['period_rows']);

        $this->assertSame([
            ['mode_value' => 'daily', 'mode_label' => 'Harian', 'total_rows' => 2, 'total_amount_rupiah' => 60000],
            ['mode_value' => 'weekly', 'mode_label' => 'Mingguan', 'total_rows' => 1, 'total_amount_rupiah' => 40000],
        ], $data['mode_rows']);

        $this->assertCount(3, $data['rows']);
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
