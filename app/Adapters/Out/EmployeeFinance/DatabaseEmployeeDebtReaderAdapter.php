<?php

declare(strict_types=1);

namespace App\Adapters\Out\EmployeeFinance;

use App\Core\EmployeeFinance\EmployeeDebt\DebtPayment;
use App\Core\EmployeeFinance\EmployeeDebt\DebtStatus;
use App\Core\EmployeeFinance\EmployeeDebt\EmployeeDebt;
use App\Core\Shared\ValueObjects\Money;
use App\Ports\Out\EmployeeFinance\EmployeeDebtReaderPort;
use DateTimeImmutable;
use Illuminate\Support\Facades\DB;

final class DatabaseEmployeeDebtReaderAdapter implements EmployeeDebtReaderPort
{
    public function findById(string $id): ?EmployeeDebt
    {
        $row = DB::table('employee_debts')
            ->select(['id', 'employee_id', 'total_debt', 'remaining_balance', 'status', 'notes'])
            ->where('id', $id)
            ->first();

        if ($row === null) {
            return null;
        }

        $paymentRows = DB::table('employee_debt_payments')
            ->leftJoin(
                'employee_debt_payment_reversals',
                'employee_debt_payment_reversals.employee_debt_payment_id',
                '=',
                'employee_debt_payments.id'
            )
            ->select([
                'employee_debt_payments.id',
                'employee_debt_payments.amount',
                'employee_debt_payments.payment_date',
                'employee_debt_payments.notes',
            ])
            ->where('employee_debt_payments.employee_debt_id', $id)
            ->whereNull('employee_debt_payment_reversals.id')
            ->get();

        $payments = [];
        foreach ($paymentRows as $pRow) {
            $payments[(string) $pRow->id] = new DebtPayment(
                (string) $pRow->id,
                Money::fromInt((int) $pRow->amount),
                new DateTimeImmutable((string) $pRow->payment_date),
                $pRow->notes !== null ? (string) $pRow->notes : null
            );
        }

        return EmployeeDebt::rehydrate(
            (string) $row->id,
            (string) $row->employee_id,
            Money::fromInt((int) $row->total_debt),
            Money::fromInt((int) $row->remaining_balance),
            DebtStatus::from((string) $row->status),
            $row->notes !== null ? (string) $row->notes : null,
            $payments
        );
    }
}
