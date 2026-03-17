<?php

declare(strict_types=1);

namespace App\Ports\Out\Expense;

use App\Core\Expense\ExpenseCategory\ExpenseCategory;

interface ExpenseCategoryWriterPort
{
    public function create(ExpenseCategory $expenseCategory): void;
}
