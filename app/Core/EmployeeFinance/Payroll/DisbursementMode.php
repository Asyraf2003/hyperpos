<?php

declare(strict_types=1);

namespace App\Core\EmployeeFinance\Payroll;

enum DisbursementMode: string
{
    case DAILY = 'daily';
    case WEEKLY = 'weekly';
    case MONTHLY = 'monthly';
    case MANUAL = 'manual';
}
