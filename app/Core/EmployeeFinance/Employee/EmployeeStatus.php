<?php

declare(strict_types=1);

namespace App\Core\EmployeeFinance\Employee;

enum EmployeeStatus: string
{
    case ACTIVE = 'active';
    case INACTIVE = 'inactive';
}
