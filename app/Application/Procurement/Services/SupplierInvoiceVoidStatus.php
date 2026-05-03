<?php

declare(strict_types=1);

namespace App\Application\Procurement\Services;

use App\Ports\Out\Procurement\SupplierInvoiceVoidStatusReaderPort;

final class SupplierInvoiceVoidStatus
{
    public function __construct(
        private readonly SupplierInvoiceVoidStatusReaderPort $reader,
    ) {
    }

    public function isVoided(string $supplierInvoiceId): bool
    {
        return $this->reader->isVoided($supplierInvoiceId);
    }
}
