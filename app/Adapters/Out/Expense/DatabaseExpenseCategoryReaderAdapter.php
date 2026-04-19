<?php

declare(strict_types=1);

namespace App\Adapters\Out\Expense;

use App\Core\Expense\ExpenseCategory\ExpenseCategory;
use App\Ports\Out\Expense\ExpenseCategoryReaderPort;
use Illuminate\Support\Facades\DB;

final class DatabaseExpenseCategoryReaderAdapter implements ExpenseCategoryReaderPort
{
    public function existsByCode(string $code): bool
    {
        return DB::table('expense_categories')->where('code', trim($code))->exists();
    }

    public function findById(string $id): ?ExpenseCategory
    {
        return $this->mapRow(DB::table('expense_categories')->where('id', trim($id))->first());
    }

    public function findByCode(string $code): ?ExpenseCategory
    {
        return $this->mapRow(DB::table('expense_categories')->where('code', trim($code))->first());
    }

    private function mapRow(?object $row): ?ExpenseCategory
    {
        if ($row === null) return null;

        return ExpenseCategory::rehydrate(
            $row->id,
            $row->code,
            $row->name,
            $row->description,
            (bool) $row->is_active,
        );
    }
}
