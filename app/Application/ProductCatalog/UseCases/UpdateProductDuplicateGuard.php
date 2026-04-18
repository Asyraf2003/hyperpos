<?php

declare(strict_types=1);

namespace App\Application\ProductCatalog\UseCases;

use App\Application\Shared\DTO\Result;
use App\Ports\Out\ProductCatalog\ProductDuplicateCheckerPort;

final class UpdateProductDuplicateGuard
{
    public function __construct(
        private readonly ProductDuplicateCheckerPort $duplicates,
    ) {
    }

    public function ensureNoConflict(
        string $productId,
        ?string $kodeBarang,
        string $namaBarang,
        string $merek,
        ?int $ukuran,
    ): ?Result {
        if (! $this->duplicates->hasConflictForUpdate(
            $productId,
            $kodeBarang,
            $namaBarang,
            $merek,
            $ukuran,
        )) {
            return null;
        }

        return Result::failure(
            'Product dengan kombinasi data ini sudah ada.',
            ['product' => ['PRODUCT_DUPLICATE']]
        );
    }
}
