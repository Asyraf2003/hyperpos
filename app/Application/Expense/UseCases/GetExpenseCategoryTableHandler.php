<?php

declare(strict_types=1);

namespace App\Application\Expense\UseCases;

use App\Application\Expense\DTO\ExpenseCategoryTableQuery;
use App\Application\Shared\DTO\Result;
use App\Ports\Out\Expense\ExpenseCategoryTableReaderPort;

final class GetExpenseCategoryTableHandler
{
    public function __construct(
        private readonly ExpenseCategoryTableReaderPort $categories,
    ) {
    }

    public function handle(ExpenseCategoryTableQuery $query): Result
    {
        return Result::success($this->categories->search($query));
    }
}
