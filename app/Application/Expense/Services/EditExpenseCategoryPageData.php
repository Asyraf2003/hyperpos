<?php

declare(strict_types=1);

namespace App\Application\Expense\Services;

use App\Core\Expense\ExpenseCategory\ExpenseCategory;
use App\Ports\Out\Expense\ExpenseCategoryReaderPort;

final class EditExpenseCategoryPageData
{
    public function __construct(
        private readonly ExpenseCategoryReaderPort $categories,
    ) {
    }

    public function category(string $categoryId): ?ExpenseCategory
    {
        return $this->categories->findById($categoryId);
    }
}
