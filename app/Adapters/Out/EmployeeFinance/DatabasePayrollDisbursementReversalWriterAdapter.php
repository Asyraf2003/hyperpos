<?php

declare(strict_types=1);

namespace App\Adapters\Out\EmployeeFinance;

use App\Ports\Out\EmployeeFinance\PayrollDisbursementReversalWriterPort;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

final class DatabasePayrollDisbursementReversalWriterAdapter implements PayrollDisbursementReversalWriterPort
{
    public function record(array $record): void
    {
        $now = Carbon::now();

        DB::table('payroll_disbursement_reversals')->insert(array_merge($record, [
            'created_at' => $now,
            'updated_at' => $now,
        ]));
    }
}
