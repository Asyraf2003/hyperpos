<?php

declare(strict_types=1);

namespace App\Application\Procurement\UseCases;

use App\Application\Procurement\Services\SupplierPaymentProofAttachmentFactory;
use App\Application\Shared\DTO\Result;
use App\Core\Shared\Exceptions\DomainException;
use App\Ports\Out\AuditLogPort;
use App\Ports\Out\Procurement\SupplierPaymentProofAttachmentWriterPort;
use App\Ports\Out\Procurement\SupplierPaymentReaderPort;
use App\Ports\Out\Procurement\SupplierPaymentWriterPort;
use App\Ports\Out\TransactionManagerPort;
use Throwable;

final class AttachSupplierPaymentProofHandler
{
    public function __construct(
        private readonly SupplierPaymentReaderPort $payments,
        private readonly SupplierPaymentWriterPort $writer,
        private readonly SupplierPaymentProofAttachmentWriterPort $attachments,
        private readonly SupplierPaymentProofAttachmentFactory $attachmentFactory,
        private readonly TransactionManagerPort $transactions,
        private readonly AuditLogPort $audit,
    ) {
    }

    /**
     * @param list<array{
     * storage_path:string,
     * original_filename:string,
     * mime_type:string,
     * file_size_bytes:int
     * }> $proofFiles
     */
    public function handle(string $supplierPaymentId, array $proofFiles, string $performedByActorId): Result
    {
        $started = false;

        try {
            $payment = $this->payments->getById(trim($supplierPaymentId));

            if ($payment === null) {
                return $this->fail('Pembayaran supplier tidak ditemukan.', 'SUPPLIER_PAYMENT_NOT_FOUND');
            }

            $actorId = trim($performedByActorId);

            if ($actorId === '') {
                throw new DomainException('Actor bukti pembayaran supplier wajib ada.');
            }

            if ($proofFiles === []) {
                return $this->fail('Bukti pembayaran wajib diunggah.', 'SUPPLIER_PAYMENT_PROOF_REQUIRED');
            }

            [$attachmentRecords, $storedPaths] = $this->attachmentFactory->makeMany($payment->id(), $proofFiles, $actorId);

            $this->transactions->begin();
            $started = true;

            $this->attachments->createMany($attachmentRecords);
            $payment->markProofUploaded();
            $this->writer->update($payment);

            $this->audit->record('supplier_payment_proof_attached', [
                'supplier_payment_id' => $payment->id(),
                'supplier_invoice_id' => $payment->supplierInvoiceId(),
                'proof_status' => $payment->proofStatus(),
                'attachment_count' => count($attachmentRecords),
                'attachment_storage_paths' => $storedPaths,
                'performed_by_actor_id' => $actorId,
            ]);

            $this->transactions->commit();

            return Result::success([
                'supplier_payment_id' => $payment->id(),
                'proof_status' => $payment->proofStatus(),
                'attachment_count' => count($attachmentRecords),
                'attachment_storage_paths' => $storedPaths,
            ], 'Bukti pembayaran supplier berhasil diunggah.');
        } catch (DomainException $e) {
            if ($started) $this->transactions->rollBack();
            return $this->fail($e->getMessage(), 'INVALID_SUPPLIER_PAYMENT_PROOF');
        } catch (Throwable $e) {
            if ($started) $this->transactions->rollBack();
            throw $e;
        }
    }

    private function fail(string $message, string $code): Result
    {
        return Result::failure($message, ['supplier_payment_proof' => [$code]]);
    }
}
