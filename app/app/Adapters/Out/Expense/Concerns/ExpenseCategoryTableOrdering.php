<?php

declare(strict_types=1);

namespace App\Adapters\Out\Expense\Concerns;

use App\Application\Expense\DTO\ExpenseCategoryTableQuery;
use Illuminate\Database\Query\Builder;

trait ExpenseCategoryTableOrdering
{
    private function applyTableSorting(Builder $builder, ExpenseCategoryTableQuery $query): Builder
    {
        $sortable = ['code', 'name', 'is_active'];
        $sortBy = in_array($query->sortBy(), $sortable, true) ? $query->sortBy() : 'name';
        $sortDir = $query->sortDir() === 'desc' ? 'desc' : 'asc';

        return $builder
            ->orderBy($sortBy, $sortDir)
            ->orderByDesc('updated_at')
            ->orderBy('id');
    }
}
