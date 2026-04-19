<?php

declare(strict_types=1);

namespace App\Application\Procurement\Services;

use App\Ports\Out\AuditLogPort;
use App\Ports\Out\UuidPort;
use Illuminate\Support\Facades\DB;

final class SupplierPaymentReversalRecorder
{
    public function __construct(
        private readonly UuidPort $uuid,
        private readonly AuditLogPort $audit,
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

        DB::table('supplier_payment_reversals')->insert([
            'id' => $reversalId,
            'supplier_payment_id' => trim($paymentId),
            'reason' => trim($reason),
            'performed_by_actor_id' => trim($actorId),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->audit->record('supplier_payment_reversed', [
            'reversal_id' => $reversalId,
            'supplier_payment_id' => trim($paymentId),
            'supplier_invoice_id' => trim($supplierInvoiceId),
            'amount_rupiah' => $amountRupiah,
            'paid_at' => $paidAt,
            'proof_status' => $proofStatus,
            'reason' => trim($reason),
            'performed_by_actor_id' => trim($actorId),
        ]);

        return $reversalId;
    }
}
