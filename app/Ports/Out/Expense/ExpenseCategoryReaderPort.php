<?php

declare(strict_types=1);

namespace App\Ports\Out\Expense;

use App\Core\Expense\ExpenseCategory\ExpenseCategory;

interface ExpenseCategoryReaderPort
{
    public function existsByCode(string $code): bool;

    public function findById(string $id): ?ExpenseCategory;
}
