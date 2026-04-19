<?php

declare(strict_types=1);

namespace App\Application\EmployeeFinance\UseCases;

use App\Adapters\Out\EmployeeFinance\DatabaseEmployeeDebtListPageQuery;
use App\Application\EmployeeFinance\DTO\EmployeeDebtTableQuery;
use App\Application\Shared\DTO\Result;

final class GetEmployeeDebtTableHandler
{
    public function __construct(private readonly DatabaseEmployeeDebtListPageQuery $debts)
    {
    }

    public function handle(EmployeeDebtTableQuery $query): Result
    {
        return Result::success($this->debts->search($query));
    }
}
