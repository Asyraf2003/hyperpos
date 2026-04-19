<?php

declare(strict_types=1);

namespace App\Adapters\Out\Procurement\Concerns;

use App\Application\Procurement\DTO\SupplierTableQuery;
use Illuminate\Database\Query\Builder;

trait SupplierTableFilters
{
    private function applyTableFilters(Builder $query, SupplierTableQuery $filters): Builder
    {
        if ($filters->q() !== null) {
            $keyword = $filters->q();

            $query->where('suppliers.nama_pt_pengirim', 'like', '%' . $keyword . '%');
        }

        return $query;
    }
}
