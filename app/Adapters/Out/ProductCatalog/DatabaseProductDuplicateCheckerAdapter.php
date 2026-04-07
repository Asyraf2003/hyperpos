<?php

declare(strict_types=1);

namespace App\Adapters\Out\ProductCatalog;

use App\Ports\Out\ProductCatalog\ProductDuplicateCheckerPort;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

final class DatabaseProductDuplicateCheckerAdapter implements ProductDuplicateCheckerPort
{
    public function hasConflictForCreate(
        ?string $kodeBarang,
        string $namaBarang,
        string $merek,
        ?int $ukuran,
    ): bool {
        $rows = $this->baseQuery($namaBarang, $merek, $ukuran)->get(['id', 'kode_barang']);

        foreach ($rows as $row) {
            if ($this->isAllowedByKodeBarangException(
                $kodeBarang,
                $row->kode_barang !== null ? (string) $row->kode_barang : null,
            )) {
                continue;
            }

            return true;
        }

        return false;
    }

    public function hasConflictForUpdate(
        string $productId,
        ?string $kodeBarang,
        string $namaBarang,
        string $merek,
        ?int $ukuran,
    ): bool {
        $rows = $this->baseQuery($namaBarang, $merek, $ukuran)
            ->where('id', '!=', $productId)
            ->get(['id', 'kode_barang']);

        foreach ($rows as $row) {
            if ($this->isAllowedByKodeBarangException(
                $kodeBarang,
                $row->kode_barang !== null ? (string) $row->kode_barang : null,
            )) {
                continue;
            }

            return true;
        }

        return false;
    }

    private function baseQuery(string $namaBarang, string $merek, ?int $ukuran): Builder
    {
        $query = DB::table('products')
            ->whereNull('deleted_at')
            ->where('nama_barang_normalized', $this->normalizeForSearch($namaBarang))
            ->where('merek_normalized', $this->normalizeForSearch($merek));

        if ($ukuran === null) {
            $query->whereNull('ukuran');

            return $query;
        }

        return $query->where('ukuran', $ukuran);
    }

    private function isAllowedByKodeBarangException(
        ?string $candidateKodeBarang,
        ?string $existingKodeBarang,
    ): bool {
        if ($candidateKodeBarang === null || $existingKodeBarang === null) {
            return false;
        }

        return $candidateKodeBarang !== $existingKodeBarang;
    }

    private function normalizeForSearch(string $value): string
    {
        $normalized = preg_replace('/\s+/', ' ', trim($value)) ?? trim($value);

        return mb_strtolower($normalized);
    }
}
