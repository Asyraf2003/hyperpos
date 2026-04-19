<?php

declare(strict_types=1);

namespace App\Adapters\Out\Expense\Concerns;

use App\Application\Expense\DTO\ExpenseCategoryTableQuery;
use Illuminate\Database\Query\Builder;

trait ExpenseCategoryTableFilters
{
    private function applyTableFilters(Builder $builder, ExpenseCategoryTableQuery $query): Builder
    {
        if ($query->q() !== null) {
            $keyword = $query->q();

            $builder->where(function (Builder $q) use ($keyword): void {
                $q->where('code', 'like', '%' . $keyword . '%')
                    ->orWhere('name', 'like', '%' . $keyword . '%')
                    ->orWhere('description', 'like', '%' . $keyword . '%');
            });
        }

        if ($query->isActive() !== null) {
            $builder->where('is_active', $query->isActive());
        }

        return $builder;
    }
}
