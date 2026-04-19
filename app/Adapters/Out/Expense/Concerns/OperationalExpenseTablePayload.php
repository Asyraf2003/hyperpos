<?php

declare(strict_types=1);

namespace App\Adapters\Out\Expense\Concerns;

use App\Application\Expense\DTO\ExpenseTableQuery;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

trait OperationalExpenseTablePayload
{
    private function toTablePayload(LengthAwarePaginator $paginator, ExpenseTableQuery $query): array
    {
        $rows = array_map(fn (object $row): array => [
            'id' => (string) $row->id,
            'expense_date' => (string) $row->expense_date,
            'category_name' => (string) $row->category_name_snapshot,
            'category_code' => (string) $row->category_code_snapshot,
            'description' => (string) $row->description,
            'amount_rupiah' => (int) $row->amount_rupiah,
            'payment_method' => (string) $row->payment_method,
        ], $paginator->items());

        return [
            'rows' => $rows,
            'meta' => [
                'page' => $paginator->currentPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
                'last_page' => $paginator->lastPage(),
                'sort_by' => $query->sortBy(),
                'sort_dir' => $query->sortDir(),
                'filters' => [
                    'q' => $query->q(),
                    'category_id' => $query->categoryId(),
                    'date_from' => $query->dateFrom(),
                    'date_to' => $query->dateTo(),
                ],
            ],
        ];
    }
}
