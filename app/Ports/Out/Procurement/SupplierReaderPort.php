<?php

declare(strict_types=1);

namespace App\Ports\Out\Procurement;

use App\Core\Procurement\Supplier\Supplier;

interface SupplierReaderPort
{
    public function getById(string $supplierId): ?Supplier;

    public function getByNormalizedNamaPtPengirim(string $namaPtPengirimNormalized): ?Supplier;
}
