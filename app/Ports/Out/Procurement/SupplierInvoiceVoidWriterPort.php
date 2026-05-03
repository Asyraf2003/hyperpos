<?php

declare(strict_types=1);

namespace App\Ports\Out\Procurement;

interface SupplierInvoiceVoidWriterPort
{
    /**
     * @return array{
     *   supplier_invoice_id:string,
     *   voided_at:?string
     * }|null
     */
    public function findVoidSnapshotForUpdate(string $supplierInvoiceId): ?array;

    public function receiptExists(string $supplierInvoiceId): bool;

    public function paymentExists(string $supplierInvoiceId): bool;

    public function voidInvoice(string $supplierInvoiceId, string $voidReason): void;

    public function recordVoidAuditIfAvailable(
        string $supplierInvoiceId,
        string $voidReason,
        ?string $performedByActorId
    ): void;
}
