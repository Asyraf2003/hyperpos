<?php

declare(strict_types=1);

namespace App\Ports\Out\EmployeeFinance;

use App\Core\EmployeeFinance\Employee\Employee;

interface EmployeeReaderPort
{
    public function findById(string $id): ?Employee;
}
