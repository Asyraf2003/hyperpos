<?php

declare(strict_types=1);

namespace App\Adapters\Out\EmployeeFinance;

use App\Core\EmployeeFinance\Payroll\PayrollDisbursement;
use App\Ports\Out\EmployeeFinance\PayrollDisbursementWriterPort;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

final class DatabasePayrollDisbursementWriterAdapter implements PayrollDisbursementWriterPort
{
    public function save(PayrollDisbursement $payroll): void
    {
        $now = Carbon::now();

        $record = [
            'id' => $payroll->getId(),
            'employee_id' => $payroll->getEmployeeId(),
            'amount' => $payroll->getAmount()->amount(),
            'disbursement_date' => $payroll->getDisbursementDate()->format('Y-m-d H:i:s'),
            'mode' => $payroll->getMode()->value,
            'notes' => $payroll->getNotes(),
        ];

        DB::table('payroll_disbursements')->updateOrInsert(
            ['id' => $payroll->getId()],
            array_merge($record, ['updated_at' => $now])
        );

        DB::table('payroll_disbursements')
            ->where('id', $payroll->getId())
            ->whereNull('created_at')
            ->update(['created_at' => $now]);
    }
}
