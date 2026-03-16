<?php

declare(strict_types=1);

namespace App\Core\EmployeeFinance\EmployeeDebt;

enum DebtStatus: string
{
    case UNPAID = 'unpaid';
    case PAID = 'paid';
}
