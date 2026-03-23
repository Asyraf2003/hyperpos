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
            'status' => (string) $row->status,
            'status_label' => $this->statusLabel((string) $row->status),
            'status_badge_class' => $this->statusBadgeClass((string) $row->status),
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

    private function statusLabel(string $status): string
    {
        return match ($status) {
            'posted' => 'Posted',
            'draft' => 'Draft',
            'cancelled' => 'Cancelled',
            default => ucfirst($status),
        };
    }

    private function statusBadgeClass(string $status): string
    {
        return match ($status) {
            'posted' => 'bg-light-success text-success',
            'draft' => 'bg-light-warning text-warning',
            'cancelled' => 'bg-light-danger text-danger',
            default => 'bg-light-secondary text-secondary',
        };
    }
}
