<?php

declare(strict_types=1);

namespace App\Ports\Out\EmployeeFinance;

interface EmployeeDebtSummaryByEmployeeReaderPort
{
    /**
     * @return array<string, mixed>
     */
    public function findByEmployeeId(string $employeeId): array;
}
