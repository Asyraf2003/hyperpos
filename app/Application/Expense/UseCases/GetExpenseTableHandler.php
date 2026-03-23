<?php

declare(strict_types=1);

namespace App\Application\Expense\UseCases;

use App\Application\Expense\DTO\ExpenseTableQuery;
use App\Application\Shared\DTO\Result;
use App\Ports\Out\Expense\OperationalExpenseTableReaderPort;

final class GetExpenseTableHandler
{
    public function __construct(
        private readonly OperationalExpenseTableReaderPort $expenses,
    ) {
    }

    public function handle(ExpenseTableQuery $query): Result
    {
        return Result::success($this->expenses->search($query));
    }
}
