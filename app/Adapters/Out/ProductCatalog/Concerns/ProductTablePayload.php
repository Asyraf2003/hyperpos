<?php

declare(strict_types=1);

namespace App\Adapters\Out\ProductCatalog\Concerns;

use App\Application\ProductCatalog\DTO\ProductTableQuery;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

trait ProductTablePayload
{
    /**
     * @return array{
     *   rows:list<array<string, int|string|null>>,
     *   meta:array<string, mixed>
     * }
     */
    private function toTablePayload(LengthAwarePaginator $paginator, ProductTableQuery $query): array
    {
        $rows = array_map(static fn (object $row): array => [
            'id' => (string) $row->id,
            'kode_barang' => $row->kode_barang !== null ? (string) $row->kode_barang : null,
            'nama_barang' => (string) $row->nama_barang,
            'merek' => (string) $row->merek,
            'ukuran' => $row->ukuran !== null ? (int) $row->ukuran : null,
            'harga_jual' => (int) $row->harga_jual,
            'stok_saat_ini' => (int) $row->stok_saat_ini,
            'deleted_at' => $row->deleted_at !== null ? (string) $row->deleted_at : null,
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
                    'status' => $query->status(),
                    'merek' => $query->merek(),
                    'ukuran_min' => $query->ukuranMin(),
                    'ukuran_max' => $query->ukuranMax(),
                    'harga_min' => $query->hargaMin(),
                    'harga_max' => $query->hargaMax(),
                ],
            ],
        ];
    }
}
