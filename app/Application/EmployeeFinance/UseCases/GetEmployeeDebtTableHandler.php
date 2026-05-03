<?php

declare(strict_types=1);

namespace App\Application\EmployeeFinance\UseCases;

use App\Ports\Out\EmployeeFinance\EmployeeDebtTableReaderPort;
use App\Application\EmployeeFinance\DTO\EmployeeDebtTableQuery;
use App\Application\Shared\DTO\Result;

final class GetEmployeeDebtTableHandler
{
    public function __construct(private readonly EmployeeDebtTableReaderPort $debts)
    {
    }

    public function handle(EmployeeDebtTableQuery $query): Result
    {
        return Result::success($this->debts->search($query));
    }
}
