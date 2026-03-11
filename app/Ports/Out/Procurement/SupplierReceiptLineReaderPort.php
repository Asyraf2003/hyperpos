<?php

declare(strict_types=1);

namespace App\Ports\Out\Procurement;

interface SupplierReceiptLineReaderPort
{
    public function getReceivedQtyBySupplierInvoiceLineId(string $supplierInvoiceLineId): int;
}
