<?php

declare(strict_types=1);

namespace App\Ports\Out\ProductCatalog;

use App\Core\ProductCatalog\Product\Product;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface ProductReaderPort
{
    public function getById(string $productId): ?Product;

    /**
     * @return array<int, Product>
     */
    public function findAll(): array;

    /**
     * @return array<int, Product>
     */
    public function search(string $query): array;

    public function findPaginated(int $perPage = 10): LengthAwarePaginator;

    public function searchPaginated(string $query, int $perPage = 10): LengthAwarePaginator;
}
