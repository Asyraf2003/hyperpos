<?php

declare(strict_types=1);

namespace App\Application\Procurement\Services;

use App\Core\Procurement\SupplierPayment\SupplierPayment;
use App\Ports\Out\AuditLogPort;
use DateTimeImmutable;

final class RecordSupplierPaymentAuditLog
{
    public function __construct(
        private readonly AuditLogPort $audit,
    ) {
    }

    public function record(
        string $paymentId,
        string $invoiceId,
        int $amountRupiah,
        int $outstandingBeforeRupiah,
        DateTimeImmutable $paidAt,
        string $actorId,
    ): void {
        $this->audit->record('supplier_payment_recorded', [
            'supplier_payment_id' => $paymentId,
            'supplier_invoice_id' => $invoiceId,
            'amount_rupiah' => $amountRupiah,
            'outstanding_before_rupiah' => $outstandingBeforeRupiah,
            'outstanding_after_rupiah' => $outstandingBeforeRupiah - $amountRupiah,
            'paid_at' => $paidAt->format('Y-m-d'),
            'performed_by_actor_id' => $actorId,
            'proof_status' => SupplierPayment::PROOF_STATUS_PENDING,
        ]);
    }
}
