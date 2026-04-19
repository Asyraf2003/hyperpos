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
            ->whereNull('deleted_at')
            ->select([
                'id',
                'kode_barang',
                'nama_barang',
                'merek',
                'ukuran',
                'harga_jual',
                'reorder_point_qty',
                'critical_threshold_qty',
            ]);
    }

    private function applySearch(Builder $query, string $keyword): Builder
    {
        $rawKeyword = $keyword;
        $normalizedKeyword = $this->normalizeForSearch($keyword);

        return $query->where(function (Builder $builder) use ($rawKeyword, $normalizedKeyword): void {
            $builder
                ->where('kode_barang', 'like', '%' . $rawKeyword . '%')
                ->orWhere('nama_barang', 'like', '%' . $rawKeyword . '%')
                ->orWhere('merek', 'like', '%' . $rawKeyword . '%')
                ->orWhere('nama_barang_normalized', 'like', '%' . $normalizedKeyword . '%')
                ->orWhere('merek_normalized', 'like', '%' . $normalizedKeyword . '%');
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

    private function normalizeForSearch(string $value): string
    {
        $normalized = preg_replace('/\s+/', ' ', trim($value)) ?? trim($value);

        return mb_strtolower($normalized);
    }
}
