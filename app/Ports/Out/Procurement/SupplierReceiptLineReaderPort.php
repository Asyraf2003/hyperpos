<?php

declare(strict_types=1);

namespace App\Ports\Out\Procurement;

interface SupplierReceiptLineReaderPort
{
    /**
     * @return list<string>
     */
    public function getIdsBySupplierReceiptId(string $supplierReceiptId): array;

    public function getReceivedQtyBySupplierInvoiceLineId(string $supplierInvoiceLineId): int;
}
