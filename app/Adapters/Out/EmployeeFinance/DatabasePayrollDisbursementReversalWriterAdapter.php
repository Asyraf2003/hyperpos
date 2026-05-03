<?php

declare(strict_types=1);

namespace App\Adapters\Out\EmployeeFinance;

use App\Ports\Out\EmployeeFinance\PayrollDisbursementReversalWriterPort;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

final class DatabasePayrollDisbursementReversalWriterAdapter implements PayrollDisbursementReversalWriterPort
{
    public function findPayrollSnapshotForReversal(string $payrollId): ?array
    {
        $payroll = DB::table('payroll_disbursements')
            ->select(['id', 'employee_id', 'amount', 'disbursement_date', 'mode', 'notes'])
            ->where('id', $payrollId)
            ->first();

        if ($payroll === null) {
            return null;
        }

        return [
            'id' => (string) $payroll->id,
            'employee_id' => (string) $payroll->employee_id,
            'amount' => (int) $payroll->amount,
            'disbursement_date' => (string) $payroll->disbursement_date,
            'mode' => (string) $payroll->mode,
            'notes' => $payroll->notes !== null ? (string) $payroll->notes : null,
        ];
    }

    public function payrollAlreadyReversed(string $payrollId): bool
    {
        return DB::table('payroll_disbursement_reversals')
            ->where('payroll_disbursement_id', $payrollId)
            ->exists();
    }

    public function record(array $record): void
    {
        $now = Carbon::now();

        DB::table('payroll_disbursement_reversals')->insert(array_merge($record, [
            'created_at' => $now,
            'updated_at' => $now,
        ]));
    }
}
