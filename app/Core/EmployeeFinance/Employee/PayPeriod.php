<?php

declare(strict_types=1);

namespace App\Core\EmployeeFinance\Employee;

enum PayPeriod: string
{
    case WEEKLY = 'weekly';
    case MONTHLY = 'monthly';
}
