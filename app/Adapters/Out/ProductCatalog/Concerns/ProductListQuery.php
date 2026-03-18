<?php

declare(strict_types=1);

namespace App\Adapters\Out\ProductCatalog\Concerns;

use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

trait ProductListQuery
{
    private function baseSelect(): Builder
    {
        return DB::table('products')
            ->select(['id', 'kode_barang', 'nama_barang', 'merek', 'ukuran', 'harga_jual']);
    }

    private function applySearch(Builder $query, string $keyword): Builder
    {
        return $query->where(function (Builder $builder) use ($keyword): void {
            $builder
                ->where('kode_barang', 'like', '%' . $keyword . '%')
                ->orWhere('nama_barang', 'like', '%' . $keyword . '%')
                ->orWhere('merek', 'like', '%' . $keyword . '%');
        });
    }

    private function applyOrdering(Builder $query): Builder
    {
        return $query
            ->orderBy('nama_barang')
            ->orderBy('merek')
            ->orderBy('ukuran')
            ->orderBy('id');
    }
}
