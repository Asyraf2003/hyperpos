<?php

declare(strict_types=1);

namespace App\Application\Procurement\Services;

use App\Application\Procurement\UseCases\AttachSupplierPaymentProofResultBuilder;
use App\Application\Shared\DTO\Result;
use App\Ports\Out\AuditLogPort;
use App\Ports\Out\Procurement\SupplierPaymentProofAttachmentWriterPort;
use App\Ports\Out\Procurement\SupplierPaymentWriterPort;
use App\Ports\Out\TransactionManagerPort;

final class AttachSupplierPaymentProofTransaction
{
    public function __construct(
        private readonly SupplierPaymentProofAttachmentWriterPort $attachments,
        private readonly SupplierPaymentWriterPort $payments,
        private readonly TransactionManagerPort $transactions,
        private readonly AuditLogPort $audit,
        private readonly SupplierInvoiceListProjectionService $projection,
        private readonly AttachSupplierPaymentProofResultBuilder $results,
    ) {
    }

    /**
     * @param list<object> $attachmentRecords
     * @param list<string> $storedPaths
     */
    public function run(
        object $payment,
        array $attachmentRecords,
        array $storedPaths,
        string $actorId,
    ): Result {
        $this->transactions->begin();

        try {
            $this->attachments->createMany($attachmentRecords);
            $payment->markProofUploaded();
            $this->payments->update($payment);

            $this->audit->record('supplier_payment_proof_attached', [
                'supplier_payment_id' => $payment->id(),
                'supplier_invoice_id' => $payment->supplierInvoiceId(),
                'proof_status' => $payment->proofStatus(),
                'attachment_count' => count($attachmentRecords),
                'attachment_storage_paths' => $storedPaths,
                'performed_by_actor_id' => $actorId,
            ]);

            $this->projection->syncInvoice($payment->supplierInvoiceId());
            $this->transactions->commit();

            return $this->results->success($payment, $attachmentRecords, $storedPaths);
        } catch (\Throwable $e) {
            $this->transactions->rollBack();
            throw $e;
        }
    }
}
