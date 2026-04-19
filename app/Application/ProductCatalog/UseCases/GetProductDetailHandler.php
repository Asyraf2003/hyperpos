<?php

declare(strict_types=1);

namespace App\Application\ProductCatalog\UseCases;

use App\Application\Shared\DTO\Result;
use App\Ports\Out\ProductCatalog\ProductDetailReaderPort;

final class GetProductDetailHandler
{
    public function __construct(
        private readonly ProductDetailReaderPort $products,
    ) {
    }

    public function handle(string $productId): Result
    {
        $productId = trim($productId);

        $detail = $this->products->getDetail($productId);

        if ($detail === null) {
            return Result::failure(
                'Product tidak ditemukan.',
                ['product' => ['PRODUCT_NOT_FOUND']]
            );
        }

        return Result::success([
            'detail' => $detail,
            'timeline' => $this->products->getVersionTimeline($productId),
        ]);
    }
}
