<?php

declare(strict_types=1);

namespace App\Ports\Out\Procurement;

interface SupplierInvoiceDuplicateNumberCheckerPort
{
    public function existsActiveByNormalizedNumber(
        string $normalizedNomorFaktur,
        ?string $excludeSupplierInvoiceId = null,
    ): bool;
}
