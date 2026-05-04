<?php

declare(strict_types=1);

namespace App\Application\EmployeeFinance\Services;

use App\Core\EmployeeFinance\Employee\Employee;
use App\Ports\Out\EmployeeFinance\EmployeeReaderPort;

final class EditEmployeePageData
{
    public function __construct(
        private readonly EmployeeReaderPort $employees,
    ) {
    }

    public function findById(string $employeeId): ?Employee
    {
        return $this->employees->findById($employeeId);
    }
}
