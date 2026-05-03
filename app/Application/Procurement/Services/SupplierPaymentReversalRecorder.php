<?php

declare(strict_types=1);

namespace App\Application\Procurement\Services;

use App\Ports\Out\AuditLogPort;
use App\Ports\Out\Procurement\SupplierPaymentReversalWriterPort;
use App\Ports\Out\UuidPort;

final class SupplierPaymentReversalRecorder
{
    public function __construct(
        private readonly UuidPort $uuid,
        private readonly AuditLogPort $audit,
        private readonly SupplierPaymentReversalWriterPort $reversals,
    ) {
    }

    public function record(
        string $paymentId,
        string $supplierInvoiceId,
        int $amountRupiah,
        string $paidAt,
        string $proofStatus,
        string $reason,
        string $actorId,
    ): string {
        $reversalId = $this->uuid->generate();
        $paymentId = trim($paymentId);
        $supplierInvoiceId = trim($supplierInvoiceId);
        $reason = trim($reason);
        $actorId = trim($actorId);

        $this->reversals->record([
            'id' => $reversalId,
            'supplier_payment_id' => $paymentId,
            'reason' => $reason,
            'performed_by_actor_id' => $actorId,
        ]);

        $this->audit->record('supplier_payment_reversed', [
            'reversal_id' => $reversalId,
            'supplier_payment_id' => $paymentId,
            'supplier_invoice_id' => $supplierInvoiceId,
            'amount_rupiah' => $amountRupiah,
            'paid_at' => $paidAt,
            'proof_status' => $proofStatus,
            'reason' => $reason,
            'performed_by_actor_id' => $actorId,
        ]);

        return $reversalId;
    }
}
