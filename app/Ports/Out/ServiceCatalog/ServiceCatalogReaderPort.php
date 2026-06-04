<?php

declare(strict_types=1);

namespace App\Ports\Out\ServiceCatalog;

use App\Core\ServiceCatalog\ServiceCatalogItem;

interface ServiceCatalogReaderPort
{
    public function findByNormalizedName(string $normalizedName): ?ServiceCatalogItem;

    /**
     * @return list<ServiceCatalogItem>
     */
    public function search(string $query, int $limit = 10): array;
}
