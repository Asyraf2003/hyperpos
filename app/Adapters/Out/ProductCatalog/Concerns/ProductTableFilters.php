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
            $rawKeyword = $filters->q();
            $normalizedKeyword = $this->normalizeForSearch($rawKeyword);

            $query->where(function (Builder $builder) use ($rawKeyword, $normalizedKeyword): void {
                $builder
                    ->where('products.kode_barang', 'like', '%' . $rawKeyword . '%')
                    ->orWhere('products.nama_barang_normalized', 'like', '%' . $normalizedKeyword . '%')
                    ->orWhere('products.merek_normalized', 'like', '%' . $normalizedKeyword . '%');
            });
        }

        if ($filters->merek() !== null) {
            $query->where(
                'products.merek_normalized',
                $this->normalizeForSearch($filters->merek())
            );
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

    private function normalizeForSearch(string $value): string
    {
        $normalized = preg_replace('/\s+/', ' ', trim($value)) ?? trim($value);

        return mb_strtolower($normalized);
    }
}
