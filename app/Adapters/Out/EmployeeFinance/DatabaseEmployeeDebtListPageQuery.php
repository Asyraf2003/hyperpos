<?php

declare(strict_types=1);

namespace App\Adapters\Out\EmployeeFinance;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

final class DatabaseEmployeeDebtListPageQuery
{
    /**
     * @return list<array{
     *     id: string,
     *     employee_id: string,
     *     employee_name: string,
     *     total_debt: int,
     *     total_debt_formatted: string,
     *     remaining_balance: int,
     *     remaining_balance_formatted: string,
     *     status_value: string,
     *     status_label: string,
     *     notes: ?string,
     *     recorded_at: string
     * }>
     */
    public function latest(): array
    {
        return DB::table('employee_debts')
            ->join('employees', 'employees.id', '=', 'employee_debts.employee_id')
            ->select([
                'employee_debts.id',
                'employee_debts.employee_id',
                'employees.name as employee_name',
                'employee_debts.total_debt',
                'employee_debts.remaining_balance',
                'employee_debts.status',
                'employee_debts.notes',
                'employee_debts.created_at',
            ])
            ->orderByDesc('employee_debts.created_at')
            ->limit(50)
            ->get()
            ->map(function (object $row): array {
                $totalDebt = (int) $row->total_debt;
                $remainingBalance = (int) $row->remaining_balance;
                $statusValue = (string) $row->status;

                return [
                    'id' => (string) $row->id,
                    'employee_id' => (string) $row->employee_id,
                    'employee_name' => (string) $row->employee_name,
                    'total_debt' => $totalDebt,
                    'total_debt_formatted' => number_format($totalDebt, 0, ',', '.'),
                    'remaining_balance' => $remainingBalance,
                    'remaining_balance_formatted' => number_format($remainingBalance, 0, ',', '.'),
                    'status_value' => $statusValue,
                    'status_label' => $this->statusLabel($statusValue),
                    'notes' => $row->notes !== null ? (string) $row->notes : null,
                    'recorded_at' => Carbon::parse((string) $row->created_at)->format('Y-m-d'),
                ];
            })
            ->values()
            ->all();
    }

    private function statusLabel(string $statusValue): string
    {
        return match ($statusValue) {
            'unpaid' => 'Belum Lunas',
            'paid' => 'Lunas',
            default => ucfirst($statusValue),
        };
    }
}
