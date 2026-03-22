<?php

declare(strict_types=1);

namespace App\Adapters\Out\EmployeeFinance;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

final class DatabaseEmployeeDetailPageQuery
{
    public function findById(string $employeeId): ?array
    {
        $row = DB::table('employees')
            ->select([
                'id',
                'name',
                'phone',
                'base_salary',
                'pay_period',
                'status',
            ])
            ->where('id', $employeeId)
            ->first();

        if ($row === null) {
            return null;
        }

        $baseSalary = (int) $row->base_salary;
        $payPeriodValue = (string) $row->pay_period;
        $statusValue = (string) $row->status;

        return [
            'summary' => [
                'id' => (string) $row->id,
                'name' => (string) $row->name,
                'phone' => $row->phone !== null ? (string) $row->phone : null,
                'base_salary_amount' => $baseSalary,
                'base_salary_formatted' => number_format($baseSalary, 0, ',', '.'),
                'pay_period_value' => $payPeriodValue,
                'pay_period_label' => $this->payPeriodLabel($payPeriodValue),
                'status_value' => $statusValue,
                'status_label' => $this->statusLabel($statusValue),
            ],
            'debt' => [
                'summary' => $this->debtSummary($employeeId),
                'records' => $this->debtRecords($employeeId),
                'payments' => $this->debtPayments($employeeId),
            ],
        ];
    }

    private function debtSummary(string $employeeId): array
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

    private function debtRecords(string $employeeId): array
    {
        return DB::table('employee_debts')
            ->select([
                'id',
                'total_debt',
                'remaining_balance',
                'status',
                'notes',
                'created_at',
            ])
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
                    'status_label' => $this->debtStatusLabel($statusValue),
                    'notes' => $row->notes !== null ? (string) $row->notes : null,
                    'recorded_at' => Carbon::parse((string) $row->created_at)->format('Y-m-d'),
                ];
            })
            ->values()
            ->all();
    }

    private function debtPayments(string $employeeId): array
    {
        return DB::table('employee_debt_payments')
            ->join('employee_debts', 'employee_debts.id', '=', 'employee_debt_payments.employee_debt_id')
            ->select([
                'employee_debt_payments.id',
                'employee_debt_payments.employee_debt_id',
                'employee_debt_payments.amount',
                'employee_debt_payments.payment_date',
                'employee_debt_payments.notes',
            ])
            ->where('employee_debts.employee_id', $employeeId)
            ->orderByDesc('employee_debt_payments.payment_date')
            ->orderByDesc('employee_debt_payments.created_at')
            ->get()
            ->map(function (object $row): array {
                $amount = (int) $row->amount;

                return [
                    'id' => (string) $row->id,
                    'employee_debt_id' => (string) $row->employee_debt_id,
                    'amount' => $amount,
                    'amount_formatted' => number_format($amount, 0, ',', '.'),
                    'payment_date' => Carbon::parse((string) $row->payment_date)->format('Y-m-d H:i'),
                    'notes' => $row->notes !== null ? (string) $row->notes : null,
                ];
            })
            ->values()
            ->all();
    }

    private function payPeriodLabel(string $value): string
    {
        return match ($value) {
            'daily' => 'Harian',
            'weekly' => 'Mingguan',
            'monthly' => 'Bulanan',
            default => ucfirst($value),
        };
    }

    private function statusLabel(string $value): string
    {
        return match ($value) {
            'active' => 'Aktif',
            'inactive' => 'Nonaktif',
            default => ucfirst($value),
        };
    }

    private function debtStatusLabel(string $value): string
    {
        return match ($value) {
            'unpaid' => 'Belum Lunas',
            'paid' => 'Lunas',
            default => ucfirst($value),
        };
    }
}
