<?php

declare(strict_types=1);

namespace App\Adapters\Out\Expense;

use App\Adapters\Out\Expense\Concerns\ExpenseCategoryTableBaseQuery;
use App\Adapters\Out\Expense\Concerns\ExpenseCategoryTableFilters;
use App\Adapters\Out\Expense\Concerns\ExpenseCategoryTableOrdering;
use App\Adapters\Out\Expense\Concerns\ExpenseCategoryTablePayload;
use App\Application\Expense\DTO\ExpenseCategoryTableQuery;
use App\Ports\Out\Expense\ExpenseCategoryTableReaderPort;

final class DatabaseExpenseCategoryTableReaderAdapter implements ExpenseCategoryTableReaderPort
{
    use ExpenseCategoryTableBaseQuery;
    use ExpenseCategoryTableFilters;
    use ExpenseCategoryTableOrdering;
    use ExpenseCategoryTablePayload;

    public function search(ExpenseCategoryTableQuery $query): array
    {
        $builder = $this->baseTableQuery();
        $builder = $this->applyTableFilters($builder, $query);
        $builder = $this->applyTableSorting($builder, $query);

        return $this->toTablePayload(
            $builder->paginate($query->perPage(), ['*'], 'page', $query->page()),
            $query,
        );
    }
}
