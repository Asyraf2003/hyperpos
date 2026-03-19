<?php

declare(strict_types=1);

namespace App\Adapters\Out\Procurement\Concerns;

use App\Application\Procurement\DTO\SupplierTableQuery;
use Illuminate\Database\Query\Builder;

trait SupplierTableOrdering
{
    private function applyTableSorting(Builder $query, SupplierTableQuery $filters): Builder
    {
        return $query
            ->orderBy('suppliers.nama_pt_pengirim', $filters->sortDir())
            ->orderBy('suppliers.id');
    }
}
