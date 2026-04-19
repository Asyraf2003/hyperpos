<?php

declare(strict_types=1);

namespace App\Adapters\Out\EmployeeFinance;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

final class DatabaseEmployeeDebtRecordListByEmployeeQuery
{
    public function findByEmployeeId(string $employeeId): array
    {
        return DB::table('employee_debts')
            ->select(['id', 'total_debt', 'remaining_balance', 'status', 'notes', 'created_at'])
            ->where('employee_id', $employeeId)
            ->orderByDesc('created_at')
            ->get()
            ->map(function (object $row): array {
                $totalDebt = (int) $row->total_debt;
                $remainingBalance = (int) $row->remaining_balance;
                $statusValue = (string) $row->status;

                return [
                    'id' => (string) $row->id,
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

    private function statusLabel(string $value): string
    {
        return match ($value) {
            'unpaid' => 'Belum Lunas',
            'paid' => 'Lunas',
            default => ucfirst($value),
        };
    }
}
