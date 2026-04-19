<?php

declare(strict_types=1);

namespace App\Application\ProductCatalog\UseCases;

use App\Application\ProductCatalog\DTO\ProductTableQuery;
use App\Application\Shared\DTO\Result;
use App\Ports\Out\ProductCatalog\ProductTableReaderPort;

final class GetProductTableHandler
{
    public function __construct(
        private readonly ProductTableReaderPort $products,
    ) {
    }

    public function handle(ProductTableQuery $query): Result
    {
        return Result::success($this->products->search($query));
    }
}
