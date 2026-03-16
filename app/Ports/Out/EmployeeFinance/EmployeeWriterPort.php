<?php

declare(strict_types=1);

namespace App\Ports\Out\EmployeeFinance;

use App\Core\EmployeeFinance\Employee\Employee;

interface EmployeeWriterPort
{
    public function save(Employee $employee): void;
}
