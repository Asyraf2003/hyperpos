<?php

declare(strict_types=1);

namespace App\Adapters\Out\EmployeeFinance;

use App\Ports\Out\EmployeeFinance\EmployeeDebtAdjustmentWriterPort;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

final class DatabaseEmployeeDebtAdjustmentWriterAdapter implements EmployeeDebtAdjustmentWriterPort
{
    public function record(array $record): void
    {
        $now = Carbon::now();

        DB::table('employee_debt_adjustments')->insert(array_merge($record, [
            'created_at' => $now,
            'updated_at' => $now,
        ]));
    }
}
