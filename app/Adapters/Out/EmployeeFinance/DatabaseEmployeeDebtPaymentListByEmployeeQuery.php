<?php

declare(strict_types=1);

namespace App\Adapters\Out\EmployeeFinance;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

final class DatabaseEmployeeDebtPaymentListByEmployeeQuery
{
    public function findByEmployeeId(string $employeeId): array
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
}
