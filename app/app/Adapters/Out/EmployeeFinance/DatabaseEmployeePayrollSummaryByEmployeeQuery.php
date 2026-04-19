<?php

declare(strict_types=1);

namespace App\Adapters\Out\EmployeeFinance;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

final class DatabaseEmployeePayrollSummaryByEmployeeQuery
{
    public function findByEmployeeId(string $employeeId): array
    {
        $row = DB::table('payroll_disbursements')
            ->leftJoin('payroll_disbursement_reversals', 'payroll_disbursements.id', '=', 'payroll_disbursement_reversals.payroll_disbursement_id')
            ->selectRaw('COUNT(CASE WHEN payroll_disbursement_reversals.id IS NULL THEN 1 END) as total_payroll_records')
            ->selectRaw('COALESCE(SUM(CASE WHEN payroll_disbursement_reversals.id IS NULL THEN payroll_disbursements.amount ELSE 0 END), 0) as total_disbursed_amount')
            ->selectRaw('MAX(CASE WHEN payroll_disbursement_reversals.id IS NULL THEN payroll_disbursements.disbursement_date END) as latest_disbursement_date')
            ->where('payroll_disbursements.employee_id', $employeeId)
            ->first();

        $totalPayrollRecords = (int) ($row->total_payroll_records ?? 0);
        $totalDisbursedAmount = (int) ($row->total_disbursed_amount ?? 0);
        $latestDisbursementDate = $row->latest_disbursement_date !== null
            ? Carbon::parse((string) $row->latest_disbursement_date)->format('Y-m-d')
            : null;

        return [
            'total_payroll_records' => $totalPayrollRecords,
            'total_disbursed_amount' => $totalDisbursedAmount,
            'total_disbursed_amount_formatted' => number_format($totalDisbursedAmount, 0, ',', '.'),
            'latest_disbursement_date' => $latestDisbursementDate,
        ];
    }
}
