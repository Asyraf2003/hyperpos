<?php

declare(strict_types=1);

namespace Tests\Feature\EmployeeFinance;

use App\Adapters\Out\EmployeeFinance\DatabaseEmployeeDebtDetailPageQuery;
use App\Adapters\Out\EmployeeFinance\DatabaseEmployeeDebtPaymentListByEmployeeQuery;
use App\Adapters\Out\Reporting\DatabaseEmployeeDebtReportingSourceReaderAdapter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

final class EmployeeDebtPaymentReversalReadModelFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_debt_detail_query_excludes_reversed_payments(): void
    {
        [$employeeId, $debtId, $activePaymentId, $reversedPaymentId] = $this->seedDebtWithActiveAndReversedPayments();

        $detail = app(DatabaseEmployeeDebtDetailPageQuery::class)->findById($debtId);

        $this->assertNotNull($detail);
        $this->assertSame($employeeId, $detail['summary']['employee_id']);
        $this->assertSame(100000, $detail['summary']['total_paid_amount']);
        $this->assertCount(1, $detail['payments']);
        $this->assertSame($activePaymentId, $detail['payments'][0]['id']);
        $this->assertSame('Pembayaran aktif', $detail['payments'][0]['notes']);
        $this->assertNotSame($reversedPaymentId, $detail['payments'][0]['id']);
    }

    public function test_payment_list_by_employee_query_excludes_reversed_payments(): void
    {
        [$employeeId, , $activePaymentId, $reversedPaymentId] = $this->seedDebtWithActiveAndReversedPayments();

        $payments = app(DatabaseEmployeeDebtPaymentListByEmployeeQuery::class)->findByEmployeeId($employeeId);

        $this->assertCount(1, $payments);
        $this->assertSame($activePaymentId, $payments[0]['id']);
        $this->assertSame('Pembayaran aktif', $payments[0]['notes']);
        $this->assertNotSame($reversedPaymentId, $payments[0]['id']);
    }

    public function test_reporting_source_excludes_reversed_payments_from_totals(): void
    {
        [, $debtId] = $this->seedDebtWithActiveAndReversedPayments();

        $source = app(DatabaseEmployeeDebtReportingSourceReaderAdapter::class);

        $rows = $source->getEmployeeDebtSummaryRows('2026-04-01', '2026-04-30');
        $this->assertCount(1, $rows);
        $this->assertSame($debtId, $rows[0]['debt_id']);
        $this->assertSame(1000000, $rows[0]['total_debt']);
        $this->assertSame(100000, $rows[0]['total_paid_amount']);
        $this->assertSame(900000, $rows[0]['remaining_balance']);

        $reconciliation = $source->getEmployeeDebtSummaryReconciliation('2026-04-01', '2026-04-30');
        $this->assertSame(1, $reconciliation['total_rows']);
        $this->assertSame(1000000, $reconciliation['total_debt']);
        $this->assertSame(100000, $reconciliation['total_paid_amount']);
        $this->assertSame(900000, $reconciliation['total_remaining_balance']);
    }

    private function seedDebtWithActiveAndReversedPayments(): array
    {
        $employeeId = (string) Str::uuid();
        $debtId = (string) Str::uuid();
        $activePaymentId = (string) Str::uuid();
        $reversedPaymentId = (string) Str::uuid();

        DB::table('employees')->insert([
            'id' => $employeeId,
            'employee_name' => 'Budi Hutang',
            'phone' => '0812',
            'default_salary_amount' => 5000000,
            'salary_basis_type' => 'monthly',
            'employment_status' => 'active',
            'created_at' => '2026-04-01 08:00:00',
            'updated_at' => '2026-04-01 08:00:00',
        ]);

        DB::table('employee_debts')->insert([
            'id' => $debtId,
            'employee_id' => $employeeId,
            'total_debt' => 1000000,
            'remaining_balance' => 900000,
            'status' => 'unpaid',
            'notes' => 'Kasbon awal',
            'created_at' => '2026-04-11 09:00:00',
            'updated_at' => '2026-04-11 09:00:00',
        ]);

        DB::table('employee_debt_payments')->insert([
            [
                'id' => $activePaymentId,
                'employee_debt_id' => $debtId,
                'amount' => 100000,
                'payment_date' => '2026-04-11 10:00:00',
                'notes' => 'Pembayaran aktif',
                'created_at' => '2026-04-11 10:00:00',
                'updated_at' => '2026-04-11 10:00:00',
            ],
            [
                'id' => $reversedPaymentId,
                'employee_debt_id' => $debtId,
                'amount' => 100000,
                'payment_date' => '2026-04-11 11:00:00',
                'notes' => 'Pembayaran direversal',
                'created_at' => '2026-04-11 11:00:00',
                'updated_at' => '2026-04-11 11:00:00',
            ],
        ]);

        DB::table('employee_debt_payment_reversals')->insert([
            'id' => (string) Str::uuid(),
            'employee_debt_payment_id' => $reversedPaymentId,
            'reason' => 'Salah input pembayaran',
            'performed_by_actor_id' => '1',
            'created_at' => '2026-04-11 12:00:00',
            'updated_at' => '2026-04-11 12:00:00',
        ]);

        return [$employeeId, $debtId, $activePaymentId, $reversedPaymentId];
    }
}
