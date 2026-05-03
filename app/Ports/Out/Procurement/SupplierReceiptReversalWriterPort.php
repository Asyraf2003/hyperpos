<?php

declare(strict_types=1);

namespace App\Ports\Out\Procurement;

interface SupplierReceiptReversalWriterPort
{
    /**
     * @return array{
     *   supplier_receipt_id:string,
     *   supplier_invoice_id:string
     * }|null
     */
    public function findReceiptSnapshotForReversal(string $supplierReceiptId): ?array;

    public function receiptAlreadyReversed(string $supplierReceiptId): bool;

    /**
     * @param array{
     *   id:string,
     *   supplier_receipt_id:string,
     *   reason:string,
     *   performed_by_actor_id:string
     * } $record
     */
    public function record(array $record): void;
}
