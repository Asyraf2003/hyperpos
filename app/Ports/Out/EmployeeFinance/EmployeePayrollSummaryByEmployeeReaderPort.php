<?php

declare(strict_types=1);

namespace App\Ports\Out\EmployeeFinance;

interface EmployeePayrollSummaryByEmployeeReaderPort
{
    /**
     * @return array<string, mixed>
     */
    public function findByEmployeeId(string $employeeId): array;
}
