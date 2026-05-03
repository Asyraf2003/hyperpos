<?php

declare(strict_types=1);

namespace App\Ports\Out\Procurement;

interface SupplierInvoiceVoidStatusReaderPort
{
    /**
     * @return array{
     *   supplier_invoice_id:string,
     *   voided_at:?string
     * }|null
     */
    public function findVoidStatus(string $supplierInvoiceId): ?array;

    public function isVoided(string $supplierInvoiceId): bool;
}
