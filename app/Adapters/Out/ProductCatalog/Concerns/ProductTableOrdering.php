<?php

declare(strict_types=1);

namespace App\Adapters\Out\ProductCatalog\Concerns;

use App\Application\ProductCatalog\DTO\ProductTableQuery;
use Illuminate\Database\Query\Builder;

trait ProductTableOrdering
{
    private function applyTableSorting(Builder $query, ProductTableQuery $filters): Builder
    {
        $sortColumn = match ($filters->sortBy()) {
            'merek' => 'products.merek',
            'ukuran' => 'products.ukuran',
            'harga_jual' => 'products.harga_jual',
            'stok_saat_ini' => 'stok_saat_ini',
            default => 'products.nama_barang',
        };

        return $query
            ->orderBy($sortColumn, $filters->sortDir())
            ->orderBy('products.id');
    }
}
