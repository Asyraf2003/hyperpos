<?php

declare(strict_types=1);

namespace App\Application\ProductCatalog\UseCases;

use App\Application\Shared\DTO\Result;
use App\Ports\Out\ProductCatalog\ProductLifecyclePort;

final class SoftDeleteProductHandler
{
    public function __construct(
        private readonly ProductLifecyclePort $products,
    ) {
    }

    public function handle(string $productId, ?string $actorId): Result
    {
        $deleted = $this->products->softDelete(trim($productId), $actorId);

        if (! $deleted) {
            return Result::failure(
                'Product tidak ditemukan atau sudah dihapus.',
                ['product' => ['PRODUCT_NOT_FOUND_OR_DELETED']]
            );
        }

        return Result::success(
            ['id' => trim($productId)],
            'Product berhasil dihapus.'
        );
    }
}
