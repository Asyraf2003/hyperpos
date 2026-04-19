<?php

declare(strict_types=1);

namespace App\Adapters\Out\ProductCatalog;

use App\Adapters\Out\ProductCatalog\Concerns\ProductDuplicateLookupQuery;
use App\Ports\Out\ProductCatalog\ProductDuplicateCheckerPort;

final class DatabaseProductDuplicateCheckerAdapter implements ProductDuplicateCheckerPort
{
    use ProductDuplicateLookupQuery;

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

    private function isAllowedByKodeBarangException(
        ?string $candidateKodeBarang,
        ?string $existingKodeBarang,
    ): bool {
        if ($candidateKodeBarang === null || $existingKodeBarang === null) {
            return false;
        }

        return $candidateKodeBarang !== $existingKodeBarang;
    }
}
