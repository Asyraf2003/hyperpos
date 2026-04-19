<?php

declare(strict_types=1);

namespace App\Adapters\Out\Procurement\Concerns;

use App\Application\Procurement\DTO\SupplierTableQuery;
use Illuminate\Database\Query\Builder;

trait SupplierTableOrdering
{
    private function applyTableSorting(Builder $query, SupplierTableQuery $filters): Builder
    {
        if ($filters->sortBy() === 'last_shipment_date') {
            return $query
                ->orderByRaw('CASE WHEN last_shipment_date IS NULL THEN 1 ELSE 0 END ASC')
                ->orderBy('last_shipment_date', $filters->sortDir())
                ->orderBy('suppliers.id');
        }

        $sortColumn = match ($filters->sortBy()) {
            'invoice_count' => 'invoice_count',
            'outstanding_rupiah' => 'outstanding_rupiah',
            'invoice_unpaid_count' => 'invoice_unpaid_count',
            default => 'suppliers.nama_pt_pengirim',
        };

        return $query
            ->orderBy($sortColumn, $filters->sortDir())
            ->orderBy('suppliers.id');
    }
}
