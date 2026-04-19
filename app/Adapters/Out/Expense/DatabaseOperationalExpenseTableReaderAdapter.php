<?php

declare(strict_types=1);

namespace App\Adapters\Out\Expense;

use App\Adapters\Out\Expense\Concerns\OperationalExpenseTableBaseQuery;
use App\Adapters\Out\Expense\Concerns\OperationalExpenseTableFilters;
use App\Adapters\Out\Expense\Concerns\OperationalExpenseTableOrdering;
use App\Adapters\Out\Expense\Concerns\OperationalExpenseTablePayload;
use App\Application\Expense\DTO\ExpenseTableQuery;
use App\Ports\Out\Expense\OperationalExpenseTableReaderPort;

final class DatabaseOperationalExpenseTableReaderAdapter implements OperationalExpenseTableReaderPort
{
    use OperationalExpenseTableBaseQuery;
    use OperationalExpenseTableFilters;
    use OperationalExpenseTableOrdering;
    use OperationalExpenseTablePayload;

    public function search(ExpenseTableQuery $query): array
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
