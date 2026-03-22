<?php

declare(strict_types=1);

namespace App\Adapters\Out\EmployeeFinance;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

final class DatabaseEmployeeDebtDetailPageQuery
{
    public function findById(string $debtId): ?array
    {
        $row = DB::table('employee_debts')
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
            ->where('employee_debts.id', $debtId)
            ->first();

        if ($row === null) {
            return null;
        }

        return [
            'summary' => $this->summary($row),
            'payments' => $this->payments($debtId),
        ];
    }

    private function payments(string $debtId): array
    {
        return DB::table('employee_debt_payments')
            ->select(['id', 'amount', 'payment_date', 'notes'])
            ->where('employee_debt_id', $debtId)
            ->orderByDesc('payment_date')
            ->orderByDesc('created_at')
            ->get()
            ->map(function (object $payment): array {
                $amount = (int) $payment->amount;

                return [
                    'id' => (string) $payment->id,
                    'amount' => $amount,
                    'amount_formatted' => number_format($amount, 0, ',', '.'),
                    'payment_date' => Carbon::parse((string) $payment->payment_date)->format('Y-m-d H:i'),
                    'notes' => $payment->notes !== null ? (string) $payment->notes : null,
                ];
            })
            ->values()
            ->all();
    }

    private function summary(object $row): array
    {
        $totalDebt = (int) $row->total_debt;
        $remainingBalance = (int) $row->remaining_balance;
        $totalPaidAmount = $totalDebt - $remainingBalance;
        $statusValue = (string) $row->status;

        return [
            'id' => (string) $row->id,
            'employee_id' => (string) $row->employee_id,
            'employee_name' => (string) $row->employee_name,
            'total_debt' => $totalDebt,
            'total_debt_formatted' => number_format($totalDebt, 0, ',', '.'),
            'remaining_balance' => $remainingBalance,
            'remaining_balance_formatted' => number_format($remainingBalance, 0, ',', '.'),
            'total_paid_amount' => $totalPaidAmount,
            'total_paid_amount_formatted' => number_format($totalPaidAmount, 0, ',', '.'),
            'status_value' => $statusValue,
            'status_label' => $this->statusLabel($statusValue),
            'notes' => $row->notes !== null ? (string) $row->notes : null,
            'recorded_at' => Carbon::parse((string) $row->created_at)->format('Y-m-d'),
        ];
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
