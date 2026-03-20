<?php

declare(strict_types=1);

namespace App\Application\Procurement\UseCases;

use App\Application\Shared\DTO\Result;
use App\Core\Shared\Exceptions\DomainException;
use App\Ports\Out\AuditLogPort;
use App\Ports\Out\Procurement\SupplierPaymentReaderPort;
use App\Ports\Out\Procurement\SupplierPaymentWriterPort;
use App\Ports\Out\TransactionManagerPort;
use Throwable;

final class AttachSupplierPaymentProofHandler
{
    public function __construct(
        private readonly SupplierPaymentReaderPort $payments,
        private readonly SupplierPaymentWriterPort $writer,
        private readonly TransactionManagerPort $transactions,
        private readonly AuditLogPort $audit,
    ) {
    }

    public function handle(string $supplierPaymentId, string $proofPath, string $performedByActorId): Result
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

            $normalizedPath = trim($proofPath);

            if ($normalizedPath === '') {
                return $this->fail('Bukti pembayaran wajib diunggah.', 'SUPPLIER_PAYMENT_PROOF_REQUIRED');
            }

            $this->transactions->begin();
            $started = true;

            $payment->attachProof($normalizedPath);
            $this->writer->update($payment);

            $this->audit->record('supplier_payment_proof_attached', [
                'supplier_payment_id' => $payment->id(),
                'supplier_invoice_id' => $payment->supplierInvoiceId(),
                'proof_status' => $payment->proofStatus(),
                'proof_storage_path' => $payment->proofStoragePath(),
                'performed_by_actor_id' => $actorId,
            ]);

            $this->transactions->commit();

            return Result::success([
                'supplier_payment_id' => $payment->id(),
                'proof_status' => $payment->proofStatus(),
                'proof_storage_path' => $payment->proofStoragePath(),
            ], 'Bukti pembayaran supplier berhasil diunggah.');
        } catch (DomainException $e) {
            if ($started) {
                $this->transactions->rollBack();
            }

            return $this->fail($e->getMessage(), 'INVALID_SUPPLIER_PAYMENT_PROOF');
        } catch (Throwable $e) {
            if ($started) {
                $this->transactions->rollBack();
            }

            throw $e;
        }
    }

    private function fail(string $message, string $code): Result
    {
        return Result::failure($message, ['supplier_payment_proof' => [$code]]);
    }
}
