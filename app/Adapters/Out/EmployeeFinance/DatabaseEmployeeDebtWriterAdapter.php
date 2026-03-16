<?php

declare(strict_types=1);

namespace App\Adapters\Out\EmployeeFinance;

use App\Core\EmployeeFinance\EmployeeDebt\EmployeeDebt;
use App\Ports\Out\EmployeeFinance\EmployeeDebtWriterPort;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

final class DatabaseEmployeeDebtWriterAdapter implements EmployeeDebtWriterPort
{
    public function save(EmployeeDebt $debt): void
    {
        $now = Carbon::now();

        $record = [
            'id' => $debt->getId(),
            'employee_id' => $debt->getEmployeeId(),
            'total_debt' => $debt->getTotalDebt()->amount(),
            'remaining_balance' => $debt->getRemainingBalance()->amount(),
            'status' => $debt->getStatus()->value,
            'notes' => $debt->getNotes(),
        ];

        DB::table('employee_debts')->updateOrInsert(
            ['id' => $debt->getId()],
            array_merge($record, ['updated_at' => $now])
        );

        DB::table('employee_debts')
            ->where('id', $debt->getId())
            ->whereNull('created_at')
            ->update(['created_at' => $now]);

        // Simpan setiap child payment
        foreach ($debt->getPayments() as $payment) {
            $paymentRecord = [
                'id' => $payment->getId(),
                'employee_debt_id' => $debt->getId(),
                'amount' => $payment->getAmount()->amount(),
                'payment_date' => $payment->getPaymentDate()->format('Y-m-d H:i:s'),
                'notes' => $payment->getNotes(),
            ];

            DB::table('employee_debt_payments')->updateOrInsert(
                ['id' => $payment->getId()],
                array_merge($paymentRecord, ['updated_at' => $now])
            );

            DB::table('employee_debt_payments')
                ->where('id', $payment->getId())
                ->whereNull('created_at')
                ->update(['created_at' => $now]);
        }
    }
}
