<?php

declare(strict_types=1);

namespace App\Ports\Out\Procurement;

interface SupplierPaymentReversalWriterPort
{
    /**
     * @return array{
     *   payment_id:string,
     *   supplier_invoice_id:string,
     *   amount_rupiah:int,
     *   paid_at:string,
     *   proof_status:string
     * }|null
     */
    public function findPaymentSnapshotForReversal(string $paymentId): ?array;

    public function paymentAlreadyReversed(string $paymentId): bool;

    /**
     * @param array{
     *   id:string,
     *   supplier_payment_id:string,
     *   reason:string,
     *   performed_by_actor_id:string
     * } $record
     */
    public function record(array $record): void;
}
