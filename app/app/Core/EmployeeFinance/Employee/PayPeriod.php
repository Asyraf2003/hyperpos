<?php

declare(strict_types=1);

namespace App\Core\EmployeeFinance\Employee;

enum PayPeriod: string
{
    case DAILY = 'daily';
    case WEEKLY = 'weekly';
    case MONTHLY = 'monthly';
    case MANUAL = 'manual';
}
