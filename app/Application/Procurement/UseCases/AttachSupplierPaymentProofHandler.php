<?php

declare(strict_types=1);

namespace App\Application\Procurement\UseCases;

use App\Application\Procurement\Services\AttachSupplierPaymentProofTransaction;
use App\Application\Procurement\Services\SupplierPaymentProofAttachmentFactory;
use App\Application\Shared\DTO\Result;
use App\Core\Shared\Exceptions\DomainException;
use App\Ports\Out\Procurement\SupplierPaymentProofFileStoragePort;
use App\Ports\Out\Procurement\SupplierPaymentReaderPort;
use Throwable;

final class AttachSupplierPaymentProofHandler
{
    public function __construct(
        private readonly SupplierPaymentReaderPort $payments,
        private readonly SupplierPaymentProofFileStoragePort $files,
        private readonly SupplierPaymentProofAttachmentFactory $attachmentFactory,
        private readonly AttachSupplierPaymentProofTransaction $transaction,
    ) {
    }

    /**
     * @param list<array{
     * source_path:string,
     * original_filename:string,
     * mime_type:string,
     * file_size_bytes:int
     * }> $proofFiles
     */
    public function handle(string $supplierPaymentId, array $proofFiles, string $performedByActorId): Result
    {
        $storedPaths = [];

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

            $storedProofFiles = $this->files->storeMany($payment->id(), $proofFiles);

            if ($storedProofFiles === []) {
                return $this->fail('Bukti pembayaran supplier gagal diunggah.', 'SUPPLIER_PAYMENT_PROOF_UPLOAD_FAILED');
            }

            [$attachmentRecords, $storedPaths] = $this->attachmentFactory->makeMany($payment->id(), $storedProofFiles, $actorId);
            $result = $this->transaction->run($payment, $attachmentRecords, $storedPaths, $actorId);

            if ($result->isFailure()) {
                $this->files->deleteMany($storedPaths);
            }

            return $result;
        } catch (DomainException $e) {
            $this->files->deleteMany($storedPaths);

            return $this->fail($e->getMessage(), 'INVALID_SUPPLIER_PAYMENT_PROOF');
        } catch (Throwable $e) {
            $this->files->deleteMany($storedPaths);

            throw $e;
        }
    }

    private function fail(string $message, string $code): Result
    {
        return Result::failure($message, ['supplier_payment_proof' => [$code]]);
    }
}
