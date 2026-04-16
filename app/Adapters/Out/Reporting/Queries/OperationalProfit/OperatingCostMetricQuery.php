<?php

declare(strict_types=1);

namespace App\Adapters\Out\Reporting\Queries\OperationalProfit;

use Illuminate\Support\Facades\DB;

final class OperatingCostMetricQuery
{
    public function operationalExpense(string $fromDate, string $toDate): int
    {
        return (int) (DB::table('operational_expenses')
            ->whereNull('deleted_at')
            ->whereBetween('expense_date', [$fromDate, $toDate])
            ->sum('amount_rupiah') ?? 0);
    }

    public function payrollDisbursement(string $fromDate, string $toDate): int
    {
        return (int) (DB::table('payroll_disbursements')
            ->leftJoin(
                'payroll_disbursement_reversals',
                'payroll_disbursements.id',
                '=',
                'payroll_disbursement_reversals.payroll_disbursement_id'
            )
            ->whereNull('payroll_disbursement_reversals.id')
            ->whereBetween(DB::raw('DATE(payroll_disbursements.disbursement_date)'), [$fromDate, $toDate])
            ->sum('payroll_disbursements.amount') ?? 0);
    }

    public function employeeDebtCashOut(string $fromDate, string $toDate): int
    {
        return (int) (DB::table('employee_debts')
            ->whereBetween(DB::raw('DATE(employee_debts.created_at)'), [$fromDate, $toDate])
            ->sum('employee_debts.total_debt') ?? 0);
    }
}
