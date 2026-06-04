<?php

declare(strict_types=1);

namespace App\Ports\Out\ServiceCatalog;

use App\Core\ServiceCatalog\ServiceCatalogItem;

interface ServiceCatalogWriterPort
{
    public function createIfMissing(string $name, int $defaultPriceRupiah): ServiceCatalogItem;
}
