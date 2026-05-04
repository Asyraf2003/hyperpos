<?php

declare(strict_types=1);

namespace App\Application\Procurement\Services;

use App\Core\Procurement\Supplier\Supplier;
use App\Ports\Out\Procurement\SupplierReaderPort;

final class EditSupplierPageData
{
    public function __construct(
        private readonly SupplierReaderPort $suppliers,
    ) {
    }

    public function getById(string $supplierId): ?Supplier
    {
        return $this->suppliers->getById($supplierId);
    }
}
