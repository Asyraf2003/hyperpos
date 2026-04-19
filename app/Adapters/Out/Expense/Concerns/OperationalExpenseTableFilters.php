<?php

declare(strict_types=1);

namespace App\Adapters\Out\Expense\Concerns;

use App\Application\Expense\DTO\ExpenseTableQuery;
use Illuminate\Database\Query\Builder;

trait OperationalExpenseTableFilters
{
    private function applyTableFilters(Builder $builder, ExpenseTableQuery $query): Builder
    {
        if ($query->q() !== null) {
            $keyword = $query->q();

            $builder->where(function (Builder $q) use ($keyword): void {
                $q->where('category_name_snapshot', 'like', '%' . $keyword . '%')
                    ->orWhere('category_code_snapshot', 'like', '%' . $keyword . '%')
                    ->orWhere('description', 'like', '%' . $keyword . '%')
                    ->orWhere('payment_method', 'like', '%' . $keyword . '%');
            });
        }

        if ($query->categoryId() !== null) {
            $builder->where('category_id', $query->categoryId());
        }

        if ($query->dateFrom() !== null) {
            $builder->whereDate('expense_date', '>=', $query->dateFrom());
        }

        if ($query->dateTo() !== null) {
            $builder->whereDate('expense_date', '<=', $query->dateTo());
        }

        return $builder;
    }
}
