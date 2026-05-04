<?php

declare(strict_types=1);

namespace App\Adapters\Out\EmployeeFinance;

use App\Ports\Out\EmployeeFinance\EmployeeDebtSummaryByEmployeeReaderPort;
use Illuminate\Support\Facades\DB;

final class DatabaseEmployeeDebtSummaryByEmployeeQuery implements EmployeeDebtSummaryByEmployeeReaderPort
{
    public function findByEmployeeId(string $employeeId): array
    {
        $row = DB::table('employee_debts')
            ->selectRaw('COUNT(*) as total_debt_records')
            ->selectRaw('SUM(total_debt) as total_debt_amount')
            ->selectRaw('SUM(remaining_balance) as total_remaining_balance')
            ->selectRaw("SUM(CASE WHEN status = 'unpaid' THEN 1 ELSE 0 END) as active_debt_count")
            ->selectRaw("SUM(CASE WHEN status = 'paid' THEN 1 ELSE 0 END) as paid_debt_count")
            ->where('employee_id', $employeeId)
            ->first();

        $totalDebtRecords = (int) ($row->total_debt_records ?? 0);
        $totalDebtAmount = (int) ($row->total_debt_amount ?? 0);
        $totalRemainingBalance = (int) ($row->total_remaining_balance ?? 0);
        $activeDebtCount = (int) ($row->active_debt_count ?? 0);
        $paidDebtCount = (int) ($row->paid_debt_count ?? 0);

        return [
            'total_debt_records' => $totalDebtRecords,
            'total_debt_amount' => $totalDebtAmount,
            'total_debt_amount_formatted' => number_format($totalDebtAmount, 0, ',', '.'),
            'total_remaining_balance' => $totalRemainingBalance,
            'total_remaining_balance_formatted' => number_format($totalRemainingBalance, 0, ',', '.'),
            'active_debt_count' => $activeDebtCount,
            'paid_debt_count' => $paidDebtCount,
        ];
    }
}
