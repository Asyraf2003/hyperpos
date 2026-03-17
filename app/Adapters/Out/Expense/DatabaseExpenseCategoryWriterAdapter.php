<?php

declare(strict_types=1);

namespace App\Adapters\Out\Expense;

use App\Core\Expense\ExpenseCategory\ExpenseCategory;
use App\Ports\Out\Expense\ExpenseCategoryWriterPort;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

final class DatabaseExpenseCategoryWriterAdapter implements ExpenseCategoryWriterPort
{
    public function create(ExpenseCategory $expenseCategory): void
    {
        $now = Carbon::now();

        DB::table('expense_categories')->insert([
            'id' => $expenseCategory->id(),
            'code' => $expenseCategory->code(),
            'name' => $expenseCategory->name(),
            'description' => $expenseCategory->description(),
            'is_active' => $expenseCategory->isActive(),
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }
}
