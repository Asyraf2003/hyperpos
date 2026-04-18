<?php

declare(strict_types=1);

namespace App\Application\ProductCatalog\UseCases;

use App\Application\Shared\DTO\Result;
use App\Ports\Out\ProductCatalog\ProductLifecyclePort;

final class RestoreProductHandler
{
    public function __construct(
        private readonly ProductLifecyclePort $products,
    ) {
    }

    public function handle(string $productId, ?string $actorId): Result
    {
        $restored = $this->products->restore(trim($productId), $actorId);

        if (! $restored) {
            return Result::failure(
                'Product tidak ditemukan atau belum dihapus.',
                ['product' => ['PRODUCT_NOT_FOUND_OR_NOT_DELETED']]
            );
        }

        return Result::success(
            ['id' => trim($productId)],
            'Product berhasil direstore.'
        );
    }
}
