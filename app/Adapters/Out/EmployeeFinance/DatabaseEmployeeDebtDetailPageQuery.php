<?php

declare(strict_types=1);

namespace App\Adapters\Out\EmployeeFinance;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

final class DatabaseEmployeeDebtDetailPageQuery
{
    /**
     * @return array{
     *     summary: array{
     *         id: string,
     *         employee_id: string,
     *         employee_name: string,
     *         total_debt: int,
     *         total_debt_formatted: string,
     *         remaining_balance: int,
     *         remaining_balance_formatted: string,
     *         total_paid_amount: int,
     *         total_paid_amount_formatted: string,
     *         status_value: string,
     *         status_label: string,
     *         notes: ?string,
     *         recorded_at: string
     *     },
     *     payments: list<array{
     *         id: string,
     *         amount: int,
     *         amount_formatted: string,
     *         payment_date: string,
     *         notes: ?string
     *     }>
     * }|null
     */
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

        $paymentRows = DB::table('employee_debt_payments')
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

        $totalDebt = (int) $row->total_debt;
        $remainingBalance = (int) $row->remaining_balance;
        $totalPaidAmount = $totalDebt - $remainingBalance;
        $statusValue = (string) $row->status;

        return [
            'summary' => [
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
            ],
            'payments' => $paymentRows,
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
