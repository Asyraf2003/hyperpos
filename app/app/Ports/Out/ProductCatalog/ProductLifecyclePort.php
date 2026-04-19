<?php

declare(strict_types=1);

namespace App\Ports\Out\ProductCatalog;

interface ProductLifecyclePort
{
    public function softDelete(string $productId, ?string $actorId): bool;
    public function restore(string $productId, ?string $actorId): bool;
}
