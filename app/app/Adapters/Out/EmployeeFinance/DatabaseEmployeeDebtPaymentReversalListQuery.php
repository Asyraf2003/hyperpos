<?php

declare(strict_types=1);

namespace App\Adapters\Out\EmployeeFinance;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

final class DatabaseEmployeeDebtPaymentReversalListQuery
{
    public function findByDebtId(string $debtId): array
    {
        return DB::table('employee_debt_payment_reversals')
            ->join(
                'employee_debt_payments',
                'employee_debt_payments.id',
                '=',
                'employee_debt_payment_reversals.employee_debt_payment_id'
            )
            ->select([
                'employee_debt_payment_reversals.id',
                'employee_debt_payment_reversals.reason',
                'employee_debt_payment_reversals.performed_by_actor_id',
                'employee_debt_payment_reversals.created_at',
                'employee_debt_payments.id as payment_id',
                'employee_debt_payments.amount',
                'employee_debt_payments.payment_date',
                'employee_debt_payments.notes',
            ])
            ->where('employee_debt_payments.employee_debt_id', $debtId)
            ->orderByDesc('employee_debt_payment_reversals.created_at')
            ->get()
            ->map(function (object $row): array {
                $amount = (int) $row->amount;

                return [
                    'id' => (string) $row->id,
                    'payment_id' => (string) $row->payment_id,
                    'amount' => $amount,
                    'amount_formatted' => number_format($amount, 0, ',', '.'),
                    'payment_date' => Carbon::parse((string) $row->payment_date)->format('Y-m-d H:i'),
                    'payment_notes' => $row->notes !== null ? (string) $row->notes : null,
                    'reason' => (string) $row->reason,
                    'performed_by_actor_id' => (string) $row->performed_by_actor_id,
                    'recorded_at' => Carbon::parse((string) $row->created_at)->format('Y-m-d H:i'),
                ];
            })
            ->values()
            ->all();
    }
}
