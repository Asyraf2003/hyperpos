<?php

declare(strict_types=1);

namespace App\Adapters\Out\EmployeeFinance;

use App\Ports\Out\EmployeeFinance\EmployeeDebtPaymentReversalWriterPort;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

final class DatabaseEmployeeDebtPaymentReversalWriterAdapter implements EmployeeDebtPaymentReversalWriterPort
{
    public function record(array $record): void
    {
        $now = Carbon::now();

        DB::table('employee_debt_payment_reversals')->insert(array_merge($record, [
            'created_at' => $now,
            'updated_at' => $now,
        ]));
    }
}
