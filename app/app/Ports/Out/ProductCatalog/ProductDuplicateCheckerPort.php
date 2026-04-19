<?php

declare(strict_types=1);

namespace App\Ports\Out\ProductCatalog;

interface ProductDuplicateCheckerPort
{
    public function hasConflictForCreate(
        ?string $kodeBarang,
        string $namaBarang,
        string $merek,
        ?int $ukuran,
    ): bool;

    public function hasConflictForUpdate(
        string $productId,
        ?string $kodeBarang,
        string $namaBarang,
        string $merek,
        ?int $ukuran,
    ): bool;
}
