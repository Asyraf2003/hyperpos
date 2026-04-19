<?php

declare(strict_types=1);

namespace App\Application\EmployeeFinance\UseCases;

use App\Application\EmployeeFinance\DTO\EmployeeTableQuery;
use App\Application\Shared\DTO\Result;
use App\Ports\Out\EmployeeFinance\EmployeeTableReaderPort;

final class GetEmployeeTableHandler
{
    public function __construct(private readonly EmployeeTableReaderPort $employees)
    {
    }

    public function handle(EmployeeTableQuery $query): Result
    {
        return Result::success($this->employees->search($query));
    }
}
