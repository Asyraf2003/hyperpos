<?php

declare(strict_types=1);

namespace App\Adapters\Out\EmployeeFinance;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

final class DatabaseEmployeeDebtListPageQuery
{
    public function latest(): array
    {
        return DB::table('employee_debts')
            ->join('employees', 'employees.id', '=', 'employee_debts.employee_id')
            ->select(['employee_debts.employee_id', 'employees.name as employee_name'])
            ->selectRaw('COUNT(*) as total_debt_records')
            ->selectRaw('SUM(employee_debts.total_debt) as total_debt_amount')
            ->selectRaw('SUM(employee_debts.remaining_balance) as total_remaining_balance')
            ->selectRaw("SUM(CASE WHEN employee_debts.status = 'unpaid' THEN 1 ELSE 0 END) as active_debt_count")
            ->selectRaw("SUM(CASE WHEN employee_debts.status = 'paid' THEN 1 ELSE 0 END) as paid_debt_count")
            ->selectRaw('MAX(employee_debts.created_at) as latest_recorded_at')
            ->groupBy('employee_debts.employee_id', 'employees.name')
            ->orderByDesc('latest_recorded_at')
            ->get()
            ->map(function (object $row): array {
                $totalDebtAmount = (int) $row->total_debt_amount;
                $totalRemainingBalance = (int) $row->total_remaining_balance;

                return [
                    'employee_id' => (string) $row->employee_id,
                    'employee_name' => (string) $row->employee_name,
                    'total_debt_records' => (int) $row->total_debt_records,
                    'total_debt_amount_formatted' => number_format($totalDebtAmount, 0, ',', '.'),
                    'total_remaining_balance_formatted' => number_format($totalRemainingBalance, 0, ',', '.'),
                    'active_debt_count' => (int) $row->active_debt_count,
                    'paid_debt_count' => (int) $row->paid_debt_count,
                    'latest_recorded_at' => Carbon::parse((string) $row->latest_recorded_at)->format('Y-m-d'),
                ];
            })
            ->values()
            ->all();
    }
}
