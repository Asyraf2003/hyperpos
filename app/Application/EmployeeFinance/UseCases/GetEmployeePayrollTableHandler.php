<?php

declare(strict_types=1);

namespace App\Application\EmployeeFinance\UseCases;

use App\Application\EmployeeFinance\DTO\EmployeePayrollTableQuery;
use App\Application\Shared\DTO\Result;
use App\Ports\Out\EmployeeFinance\EmployeePayrollTableReaderPort;

final class GetEmployeePayrollTableHandler
{
    public function __construct(
        private readonly EmployeePayrollTableReaderPort $payrolls,
    ) {
    }

    public function handle(string $employeeId, EmployeePayrollTableQuery $query): Result
    {
        return Result::success($this->payrolls->search($employeeId, $query));
    }
}
