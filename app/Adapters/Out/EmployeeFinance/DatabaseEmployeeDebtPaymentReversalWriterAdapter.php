<?php

declare(strict_types=1);

namespace App\Adapters\Out\EmployeeFinance;

use App\Ports\Out\EmployeeFinance\EmployeeDebtPaymentReversalWriterPort;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

final class DatabaseEmployeeDebtPaymentReversalWriterAdapter implements EmployeeDebtPaymentReversalWriterPort
{
    public function findPaymentSnapshotForReversal(string $paymentId): ?array
    {
        $payment = DB::table('employee_debt_payments')
            ->join('employee_debts', 'employee_debts.id', '=', 'employee_debt_payments.employee_debt_id')
            ->select([
                'employee_debt_payments.id',
                'employee_debt_payments.employee_debt_id',
                'employee_debt_payments.amount',
                'employee_debt_payments.payment_date',
                'employee_debt_payments.notes',
                'employee_debts.employee_id',
                'employee_debts.total_debt',
                'employee_debts.remaining_balance',
                'employee_debts.status',
            ])
            ->where('employee_debt_payments.id', $paymentId)
            ->first();

        if ($payment === null) {
            return null;
        }

        return [
            'employee_debt_payment_id' => (string) $payment->id,
            'employee_debt_id' => (string) $payment->employee_debt_id,
            'employee_id' => (string) $payment->employee_id,
            'amount' => (int) $payment->amount,
            'payment_date' => (string) $payment->payment_date,
            'notes' => $payment->notes !== null ? (string) $payment->notes : null,
            'total_debt' => (int) $payment->total_debt,
            'remaining_balance' => (int) $payment->remaining_balance,
            'status' => (string) $payment->status,
        ];
    }

    public function paymentAlreadyReversed(string $paymentId): bool
    {
        return DB::table('employee_debt_payment_reversals')
            ->where('employee_debt_payment_id', $paymentId)
            ->exists();
    }

    public function updateDebtAfterPaymentReversal(
        string $employeeDebtId,
        int $remainingBalance,
        string $status
    ): void {
        DB::table('employee_debts')
            ->where('id', $employeeDebtId)
            ->update([
                'remaining_balance' => $remainingBalance,
                'status' => $status,
                'updated_at' => Carbon::now(),
            ]);
    }

    public function record(array $record): void
    {
        $now = Carbon::now();

        DB::table('employee_debt_payment_reversals')->insert(array_merge($record, [
            'created_at' => $now,
            'updated_at' => $now,
        ]));
    }
}
