<?php

declare(strict_types=1);

namespace App\Application\Procurement\UseCases;

use App\Application\Procurement\Services\AttachSupplierPaymentProofTransaction;
use App\Application\Procurement\Services\SupplierPaymentProofAttachmentFactory;
use App\Application\Shared\DTO\Result;
use App\Core\Shared\Exceptions\DomainException;
use App\Ports\Out\Procurement\SupplierPaymentReaderPort;
use Throwable;

final class AttachSupplierPaymentProofHandler
{
    public function __construct(
        private readonly SupplierPaymentReaderPort $payments,
        private readonly SupplierPaymentProofAttachmentFactory $attachmentFactory,
        private readonly AttachSupplierPaymentProofTransaction $transaction,
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

            return $this->transaction->run($payment, $attachmentRecords, $storedPaths, $actorId);
        } catch (DomainException $e) {
            return $this->fail($e->getMessage(), 'INVALID_SUPPLIER_PAYMENT_PROOF');
        } catch (Throwable $e) {
            throw $e;
        }
    }

    private function fail(string $message, string $code): Result
    {
        return Result::failure($message, ['supplier_payment_proof' => [$code]]);
    }
}
