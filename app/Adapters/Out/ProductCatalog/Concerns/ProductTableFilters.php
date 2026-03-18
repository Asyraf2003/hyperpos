<?php

declare(strict_types=1);

namespace App\Adapters\Out\ProductCatalog\Concerns;

use App\Application\ProductCatalog\DTO\ProductTableQuery;
use Illuminate\Database\Query\Builder;

trait ProductTableFilters
{
    private function applyTableFilters(Builder $query, ProductTableQuery $filters): Builder
    {
        if ($filters->q() !== null) {
            $keyword = $filters->q();

            $query->where(function (Builder $builder) use ($keyword): void {
                $builder
                    ->where('products.kode_barang', 'like', '%' . $keyword . '%')
                    ->orWhere('products.nama_barang', 'like', '%' . $keyword . '%')
                    ->orWhere('products.merek', 'like', '%' . $keyword . '%');
            });
        }

        if ($filters->merek() !== null) {
            $query->where('products.merek', $filters->merek());
        }

        if ($filters->ukuranMin() !== null) {
            $query->where('products.ukuran', '>=', $filters->ukuranMin());
        }

        if ($filters->ukuranMax() !== null) {
            $query->where('products.ukuran', '<=', $filters->ukuranMax());
        }

        if ($filters->hargaMin() !== null) {
            $query->where('products.harga_jual', '>=', $filters->hargaMin());
        }

        if ($filters->hargaMax() !== null) {
            $query->where('products.harga_jual', '<=', $filters->hargaMax());
        }

        return $query;
    }
}
