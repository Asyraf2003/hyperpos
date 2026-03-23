<?php

declare(strict_types=1);

namespace App\Adapters\Out\Expense\Concerns;

use App\Application\Expense\DTO\ExpenseCategoryTableQuery;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

trait ExpenseCategoryTablePayload
{
    private function toTablePayload(LengthAwarePaginator $paginator, ExpenseCategoryTableQuery $query): array
    {
        $rows = array_map(fn (object $row): array => [
            'id' => (string) $row->id,
            'code' => (string) $row->code,
            'name' => (string) $row->name,
            'description' => $row->description !== null ? (string) $row->description : null,
            'is_active' => (bool) $row->is_active,
            'status_label' => (bool) $row->is_active ? 'Aktif' : 'Nonaktif',
            'status_badge_class' => (bool) $row->is_active ? 'bg-light-success text-success' : 'bg-light-secondary text-secondary',
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
                    'is_active' => $query->isActive(),
                ],
            ],
        ];
    }
}
