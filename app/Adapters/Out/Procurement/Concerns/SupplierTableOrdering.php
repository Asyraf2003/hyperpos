<?php

declare(strict_types=1);

namespace App\Adapters\Out\Procurement\Concerns;

use App\Application\Procurement\DTO\SupplierTableQuery;
use Illuminate\Database\Query\Builder;

trait SupplierTableOrdering
{
    private function applyTableSorting(Builder $query, SupplierTableQuery $filters): Builder
    {
        $sortColumn = match ($filters->sortBy()) {
            'invoice_count' => 'invoice_count',
            'outstanding_rupiah' => 'outstanding_rupiah',
            'invoice_unpaid_count' => 'invoice_unpaid_count',
            'last_shipment_date' => 'last_shipment_date',
            default => 'suppliers.nama_pt_pengirim',
        };

        return $query
            ->orderBy($sortColumn, $filters->sortDir())
            ->orderBy('suppliers.id');
    }
}