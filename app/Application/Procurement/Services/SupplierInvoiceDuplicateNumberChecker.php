<?php

declare(strict_types=1);

namespace App\Application\Procurement\Services;

use App\Ports\Out\Procurement\SupplierInvoiceDuplicateNumberCheckerPort;

final class SupplierInvoiceDuplicateNumberChecker
{
    public function __construct(
        private readonly SupplierInvoiceDuplicateNumberCheckerPort $duplicates,
    ) {
    }

    public function exists(string $nomorFaktur, ?string $excludeSupplierInvoiceId = null): bool
    {
        $normalized = mb_strtolower(trim($nomorFaktur), 'UTF-8');

        if ($normalized === '') {
            return false;
        }

        return $this->duplicates->existsActiveByNormalizedNumber($normalized, $excludeSupplierInvoiceId);
    }
}
