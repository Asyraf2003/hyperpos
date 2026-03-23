<?php

declare(strict_types=1);

namespace App\Adapters\Out\Expense\Concerns;

use App\Application\Expense\DTO\ExpenseTableQuery;
use Illuminate\Database\Query\Builder;

trait OperationalExpenseTableOrdering
{
    private function applyTableSorting(Builder $builder, ExpenseTableQuery $query): Builder
    {
        $sortable = ['expense_date', 'amount_rupiah', 'status'];
        $sortBy = in_array($query->sortBy(), $sortable, true) ? $query->sortBy() : 'expense_date';
        $sortDir = $query->sortDir() === 'asc' ? 'asc' : 'desc';

        return $builder
            ->orderBy($sortBy, $sortDir)
            ->orderByDesc('created_at')
            ->orderByDesc('id');
    }
}
