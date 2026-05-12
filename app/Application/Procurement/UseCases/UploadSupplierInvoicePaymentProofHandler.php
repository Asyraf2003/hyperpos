<?php

declare(strict_types=1);

namespace App\Application\Procurement\UseCases;

use App\Application\Procurement\Services\SupplierInvoiceListProjectionService;
use App\Application\Procurement\Services\SupplierInvoiceVoidStatus;
use App\Application\Procurement\Services\SupplierPaymentProofAttachmentFactory;
use App\Application\Shared\DTO\Result;
use App\Core\Procurement\SupplierPayment\SupplierPayment;
use App\Core\Shared\Exceptions\DomainException;
use App\Core\Shared\ValueObjects\Money;
use App\Ports\Out\AuditLogPort;
use App\Ports\Out\Procurement\SupplierInvoiceReaderPort;
use App\Ports\Out\Procurement\SupplierPaymentProofAttachmentWriterPort;
use App\Ports\Out\Procurement\SupplierPaymentProofFileStoragePort;
use App\Ports\Out\Procurement\SupplierPaymentReaderPort;
use App\Ports\Out\Procurement\SupplierPaymentWriterPort;
use App\Ports\Out\TransactionManagerPort;
use App\Ports\Out\UuidPort;
use DateTimeImmutable;
use Throwable;

final class UploadSupplierInvoicePaymentProofHandler
{
    public function __construct(
        private readonly SupplierInvoiceReaderPort $invoices,
        private readonly SupplierPaymentReaderPort $payments,
        private readonly SupplierPaymentWriterPort $paymentWriter,
        private readonly SupplierPaymentProofAttachmentWriterPort $attachmentWriter,
        private readonly SupplierPaymentProofFileStoragePort $files,
        private readonly SupplierPaymentProofAttachmentFactory $attachmentFactory,
        private readonly SupplierInvoiceListProjectionService $projection,
        private readonly SupplierInvoiceVoidStatus $voidStatus,
        private readonly TransactionManagerPort $transactions,
        private readonly AuditLogPort $audit,
        private readonly UuidPort $uuid,
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
    public function handle(string $supplierInvoiceId, array $proofFiles, string $performedByActorId): Result
    {
        $storedPaths = [];

        try {
            $actorId = trim($performedByActorId);

            if ($actorId === '') {
                throw new DomainException('Actor bukti pembayaran supplier wajib ada.');
            }

            if ($proofFiles === []) {
                return $this->fail('Bukti pembayaran wajib diunggah.', 'SUPPLIER_PAYMENT_PROOF_REQUIRED');
            }

            $this->transactions->begin();

            $invoice = $this->invoices->getByIdForUpdate(trim($supplierInvoiceId));

            if ($invoice === null) {
                $this->transactions->rollBack();

                return Result::failure('Nota supplier tidak ditemukan.', [
                    'supplier_invoice' => ['SUPPLIER_INVOICE_NOT_FOUND'],
                ]);
            }

            if ($this->voidStatus->isVoided($invoice->id())) {
                $this->transactions->rollBack();

                return Result::failure('Nota supplier yang sudah dibatalkan tidak bisa dimutasi lagi.', [
                    'supplier_invoice' => ['SUPPLIER_INVOICE_VOIDED'],
                ]);
            }

            $outstanding = $invoice->grandTotalRupiah()->amount()
                - $this->payments->getTotalPaidBySupplierInvoiceId($invoice->id())->amount();

            if ($outstanding < 1) {
                $this->transactions->rollBack();

                return Result::failure('Invoice supplier ini sudah lunas.', [
                    'supplier_payment' => ['SUPPLIER_INVOICE_ALREADY_PAID'],
                ]);
            }

            $paymentId = $this->uuid->generate();
            $payment = SupplierPayment::create(
                $paymentId,
                $invoice->id(),
                Money::fromInt($outstanding),
                new DateTimeImmutable('now'),
                SupplierPayment::PROOF_STATUS_PENDING,
                null
            );

            $storedProofFiles = $this->files->storeMany($paymentId, $proofFiles);

            if ($storedProofFiles === []) {
                $this->transactions->rollBack();

                return $this->fail('Bukti pembayaran supplier gagal diunggah.', 'SUPPLIER_PAYMENT_PROOF_UPLOAD_FAILED');
            }

            [$attachmentRecords, $storedPaths] = $this->attachmentFactory->makeMany(
                $paymentId,
                $storedProofFiles,
                $actorId
            );

            $this->paymentWriter->create($payment);
            $this->attachmentWriter->createMany($attachmentRecords);

            $payment->markProofUploaded();
            $this->paymentWriter->update($payment);

            $this->audit->record('supplier_invoice_mobile_payment_proof_uploaded', [
                'supplier_invoice_id' => $invoice->id(),
                'supplier_payment_id' => $paymentId,
                'amount_rupiah' => $outstanding,
                'proof_status' => $payment->proofStatus(),
                'attachment_count' => count($attachmentRecords),
                'attachment_storage_paths' => $storedPaths,
                'performed_by_actor_id' => $actorId,
            ]);

            $this->projection->syncInvoice($invoice->id());
            $this->transactions->commit();

            return Result::success([
                'supplier_invoice_id' => $invoice->id(),
                'supplier_payment_id' => $paymentId,
                'amount_rupiah' => $outstanding,
                'outstanding_rupiah' => 0,
                'proof_status' => $payment->proofStatus(),
                'attachment_count' => count($attachmentRecords),
            ], 'Bukti pembayaran supplier berhasil diunggah.');
        } catch (DomainException $e) {
            $this->rollBackIfNeeded();
            $this->files->deleteMany($storedPaths);

            return $this->fail($e->getMessage(), 'INVALID_SUPPLIER_PAYMENT_PROOF');
        } catch (Throwable $e) {
            $this->rollBackIfNeeded();
            $this->files->deleteMany($storedPaths);

            throw $e;
        }
    }

    private function fail(string $message, string $code): Result
    {
        return Result::failure($message, ['supplier_payment_proof' => [$code]]);
    }

    private function rollBackIfNeeded(): void
    {
        try {
            $this->transactions->rollBack();
        } catch (Throwable) {
        }
    }
}
