<?php

declare(strict_types=1);

namespace App\Application\EmployeeFinance\UseCases;

use App\Application\EmployeeFinance\DTO\PayrollTableQuery;
use App\Application\Shared\DTO\Result;
use App\Ports\Out\EmployeeFinance\PayrollTableReaderPort;

final class GetPayrollTableHandler
{
    public function __construct(private readonly PayrollTableReaderPort $payrolls)
    {
    }

    public function handle(PayrollTableQuery $query): Result
    {
        return Result::success($this->payrolls->search($query));
    }
}
