<?php

declare(strict_types=1);

namespace App\Ports\Out\EmployeeFinance;

use App\Core\EmployeeFinance\Payroll\PayrollDisbursement;

interface PayrollDisbursementWriterPort
{
    public function save(PayrollDisbursement $payroll): void;
}
