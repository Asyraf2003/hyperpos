<?php

declare(strict_types=1);

namespace App\Ports\Out\EmployeeFinance;

use App\Core\EmployeeFinance\EmployeeDebt\EmployeeDebt;

interface EmployeeDebtReaderPort
{
    public function findById(string $id): ?EmployeeDebt;
}
